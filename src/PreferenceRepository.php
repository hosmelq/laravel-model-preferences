<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences;

use BackedEnum;
use DateTimeInterface;
use HosmelQ\ModelPreferences\Contracts\HasPreferences;
use HosmelQ\ModelPreferences\Contracts\PreferenceDriver;
use HosmelQ\ModelPreferences\Events\PreferenceDeleted;
use HosmelQ\ModelPreferences\Events\PreferenceUpdated;
use HosmelQ\ModelPreferences\Exceptions\PreferenceValidationException;
use HosmelQ\ModelPreferences\Support\PreferencesConfig;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator as ValidatorInstance;
use InvalidArgumentException;
use JsonSerializable;
use LogicException;
use Override;
use UnitEnum;

/**
 * @implements Arrayable<string, mixed>
 */
class PreferenceRepository implements Arrayable, JsonSerializable
{
    protected const array PRIMITIVE_CASTS = [
        'bool',
        'boolean',
        'double',
        'float',
        'int',
        'integer',
        'real',
        'string',
    ];

    public function __construct(
        protected HasPreferences&Model $model,
        protected PreferencesConfig $config,
        protected PreferenceDriver $driver,
    ) {}

    public static function key(string|UnitEnum $key): string
    {
        return match (true) {
            $key instanceof BackedEnum => (string) $key->value,
            $key instanceof UnitEnum => $key->name,
            default => $key,
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return array_replace($this->config->getDefaults(), $this->castFromStorage($this->stored()));
    }

    public function clear(): void
    {
        $this->ensureModelExists();

        $keys = $this->driver->clear($this->model, $this->config);

        Event::dispatch(new PreferenceDeleted($this->model, $keys));
    }

    public function delete(string|UnitEnum $key): void
    {
        $this->deleteMultiple([$key]);
    }

    /**
     * @param list<string|UnitEnum> $keys
     */
    public function deleteMultiple(array $keys): void
    {
        $this->ensureModelExists();

        if ($keys === []) {
            return;
        }

        $keys = array_map(self::key(...), $keys);

        $this->driver->delete($this->model, $this->config, $keys);

        Event::dispatch(new PreferenceDeleted($this->model, $keys));
    }

    public function get(string|UnitEnum $key, mixed $default = null): mixed
    {
        $key = self::key($key);
        $stored = $this->stored();

        if (array_key_exists($key, $stored)) {
            return $this->castFromStorage([$key => $stored[$key]])[$key];
        }

        return func_num_args() > 1 ? value($default) : $this->config->getDefaults()[$key] ?? null;
    }

    /**
     * @param list<string|UnitEnum> $keys
     *
     * @return array<string, mixed>
     */
    public function getMultiple(array $keys): array
    {
        if ($keys === []) {
            return [];
        }

        $keys = array_map(self::key(...), $keys);
        $stored = array_intersect_key($this->stored(), array_flip($keys));
        $preferences = array_replace($this->config->getDefaults(), $this->castFromStorage($stored));

        return array_combine(
            $keys,
            array_map(fn (string $key): mixed => $preferences[$key] ?? null, $keys),
        );
    }

    public function has(string|UnitEnum $key): bool
    {
        return array_key_exists(self::key($key), $this->stored());
    }

    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function missing(string|UnitEnum $key): bool
    {
        return ! $this->has($key);
    }

    public function set(string|UnitEnum $key, mixed $value): void
    {
        $this->setMultiple([self::key($key) => $value]);
    }

    /**
     * @param array<string, mixed> $values
     */
    public function setMultiple(array $values): void
    {
        $this->ensureModelExists();

        if ($values === []) {
            return;
        }

        $this->validate($values);

        $this->driver->set($this->model, $this->config, $this->castForStorage($values));

        Event::dispatch(new PreferenceUpdated($this->model, $values));
    }

    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function toArray(): array
    {
        $stored = $this->stored();
        $casts = array_intersect_key($this->config->getCasts(), $stored);
        $serialized = $casts === []
            ? []
            : $this
                ->caster($casts)
                ->setRawAttributes(array_intersect_key($stored, $casts))
                ->toArray();
        $preferences = array_map($this->serializeValue(...), $this->config->getDefaults());

        foreach ($stored as $key => $value) {
            $preferences[$key] = array_key_exists($key, $serialized)
                ? $serialized[$key]
                : $this->serializeValue($value);
        }

        return $preferences;
    }

    /**
     * @param Builder<*> $query
     */
    public function whereEquals(Builder $query, string|UnitEnum $key, mixed $value): void
    {
        $key = self::key($key);
        $value = $this->storedForm($key, $value);
        $matchesDefault = $this->matchesDefault($key, $value);
        $model = $query->getModel();

        // Nesting on the base query preserves any table alias on the outer query.
        $scoped = fn (QueryBuilder $nested): Builder
            => $model->newQueryWithoutRelationships()->setQuery($nested);

        $query->getQuery()->where(
            function (QueryBuilder $group) use ($key, $matchesDefault, $scoped, $value): void {
                $this->driver->whereStored($scoped($group), $this->config, $key, $value);

                if ($matchesDefault) {
                    $group->orWhere(
                        fn (QueryBuilder $missing)
                            => $this->driver->whereMissing($scoped($missing), $this->config, $key),
                    );
                }
            },
        );
    }

    /**
     * @param array<string, string> $casts
     */
    protected function caster(array $casts): Model
    {
        return (new class () extends Model {})->mergeCasts($casts);
    }

    /**
     * @param array<string, mixed> $values
     *
     * @return array<string, mixed>
     */
    protected function castForStorage(array $values): array
    {
        $casts = array_intersect_key($this->config->getCasts(), $values);

        if ($casts === []) {
            return $values;
        }

        $caster = $this->caster($casts);

        foreach ($casts as $key => $cast) {
            if (
                in_array($cast, self::PRIMITIVE_CASTS, true)
                || str_starts_with($cast, 'decimal:')
            ) {
                $values[$key] = $caster->setRawAttributes([$key => $values[$key]])
                    ->getAttribute($key);
            } else {
                $caster->setAttribute($key, $values[$key]);

                if (in_array(
                    Str::before($cast, ':'),
                    ['date', 'immutable_date', 'timestamp'],
                    true,
                )) {
                    $caster->setAttribute($key, $caster->getAttribute($key));
                }

                $values[$key] = $caster->getAttributes()[$key];
            }
        }

        return $values;
    }

    /**
     * @param array<string, mixed> $values
     *
     * @return array<string, mixed>
     */
    protected function castFromStorage(array $values): array
    {
        $casts = array_intersect_key($this->config->getCasts(), $values);

        if ($casts === []) {
            return $values;
        }

        $caster = $this->caster($casts)->setRawAttributes($values);

        foreach (array_keys($casts) as $key) {
            $values[$key] = $caster->getAttribute($key);
        }

        return $values;
    }

    protected function ensureModelExists(): void
    {
        if (! $this->model->exists) {
            throw new LogicException(
                sprintf(
                    'Cannot change the preferences of an unsaved [%s] model.',
                    $this->model::class,
                ),
            );
        }

        if ($this->model->getKey() === null) {
            throw new LogicException(
                sprintf(
                    'Cannot change the preferences of a [%s] model without a key.',
                    $this->model::class,
                ),
            );
        }
    }

    protected function matchesDefault(string $key, mixed $stored): bool
    {
        $defaults = $this->config->getDefaults();

        if (! array_key_exists($key, $defaults)) {
            return false;
        }

        $default = $defaults[$key];

        if ($this->serializeValue(
            $this->castFromStorage([$key => $stored])[$key],
        ) === $this->serializeValue(
            $default,
        )) {
            return true;
        }

        $cast = $this->config->getCasts()[$key] ?? null;

        if ($cast !== null) {
            $class = Str::before($cast, ':');

            if (class_exists($class) && ! enum_exists($class)) {
                return false;
            }
        }

        return $this->storedForm($key, $default) === $stored;
    }

    protected function normalizeForValidation(mixed $value): mixed
    {
        return match (true) {
            is_array($value) => array_map($this->normalizeForValidation(...), $value),
            $value instanceof BackedEnum => $value->value,
            default => $value,
        };
    }

    /**
     * @return array<array-key, mixed>
     */
    protected function rulesFor(string $key): array
    {
        $depth = mb_substr_count($key, '.');
        $rules = [];

        foreach ($this->config->getRules() as $rule => $constraints) {
            $name = (string) $rule;

            if (
                mb_substr_count($name, '.') >= $depth
                && Str::is(implode('.', array_slice(explode('.', $name), 0, $depth + 1)), $key)
            ) {
                $rules[$rule] = $constraints;
            }
        }

        return $rules;
    }

    protected function serializeValue(mixed $value): mixed
    {
        return match (true) {
            is_array($value) => array_map($this->serializeValue(...), $value),
            $value instanceof BackedEnum => $value->value,
            $value instanceof UnitEnum => $value->name,
            $value instanceof DateTimeInterface => Carbon::instance($value)->toJSON(),
            $value instanceof Arrayable => $this->serializeValue($value->toArray()),
            $value instanceof JsonSerializable => $value->jsonSerialize(),
            default => $value,
        };
    }

    /**
     * @return array<string, mixed>
     */
    protected function stored(): array
    {
        return $this->model->exists ? $this->driver->all($this->model, $this->config) : [];
    }

    protected function storedForm(string $key, mixed $value): mixed
    {
        $value = $this->castForStorage([$key => $value])[$key];

        return json_decode(
            json_encode($value, JSON_THROW_ON_ERROR),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );
    }

    /**
     * @param array<array-key, mixed> $values
     */
    protected function validate(array $values): void
    {
        $keys = array_map(strval(...), array_keys($values));
        $keySet = array_flip($keys);
        $rules = [];
        $undeclared = [];

        foreach ($keys as $key) {
            $parent = $key;

            while (str_contains($parent, '.')) {
                $parent = Str::beforeLast($parent, '.');

                if (isset($keySet[$parent])) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'Cannot set overlapping preference keys [%s] and [%s] together.',
                            $parent,
                            $key,
                        ),
                    );
                }
            }

            $keyRules = $this->rulesFor($key);

            if (
                $keyRules === []
                && $this->config->isStrict()
                && ! array_key_exists($key, $this->config->getDefaults())
            ) {
                $undeclared[] = $key;

                continue;
            }

            $rules += $keyRules;
        }

        if ($rules === [] && $undeclared === []) {
            return;
        }

        // Put explicit rules before wildcard rules so Laravel applies both to the same attribute.
        $rules = array_filter(
            $rules,
            fn (int|string $rule): bool => ! str_contains((string) $rule, '*'),
            ARRAY_FILTER_USE_KEY,
        ) + $rules;
        $validator = Validator::make(
            array_map($this->normalizeForValidation(...), Arr::undot($values)),
            $rules,
        )->after(function (ValidatorInstance $validator) use ($undeclared): void {
            foreach ($undeclared as $key) {
                $validator->addFailure($key, 'Prohibited');
            }
        });

        if ($validator->fails()) {
            throw new PreferenceValidationException($validator);
        }
    }
}
