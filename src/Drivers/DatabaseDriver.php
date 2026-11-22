<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences\Drivers;

use HosmelQ\ModelPreferences\Contracts\HasPreferences;
use HosmelQ\ModelPreferences\Contracts\PreferenceDriver;
use HosmelQ\ModelPreferences\Drivers\Concerns\BuildsPreferenceQueries;
use HosmelQ\ModelPreferences\Support\JsonExpression;
use HosmelQ\ModelPreferences\Support\PreferencesConfig;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\MissingAttributeException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Override;
use stdClass;
use WeakMap;

abstract class DatabaseDriver implements PreferenceDriver
{
    use BuildsPreferenceQueries;

    /**
     * @var WeakMap<HasPreferences&Model, array<string, mixed>>
     */
    protected WeakMap $loaded;

    public function __construct()
    {
        $this->loaded = new WeakMap();
    }

    /**
     * @return non-empty-string
     */
    abstract protected function foreignKey(): string;

    abstract public function tableName(Model $model, PreferencesConfig $config): string;

    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function all(HasPreferences&Model $model, PreferencesConfig $config): array
    {
        if (! isset($this->loaded[$model])) {
            if ($this->storedKey($model) === null) {
                throw new MissingAttributeException($model, $model->getKeyName());
            }

            $this->load([$model], $config);
        }

        return $this->loaded[$model] ?? [];
    }

    /**
     * @return list<string>
     */
    #[Override]
    public function clear(HasPreferences&Model $model, PreferencesConfig $config): array
    {
        /** @var list<string> $keys */
        $keys = $this->connection($model)->transaction(function () use ($config, $model): array {
            /** @var list<string> $keys */
            $keys = $this->owned($model, $config)->pluck('key')->all();

            if ($keys !== []) {
                $this->owned($model, $config)->whereIn('key', $keys)->delete();
            }

            return $keys;
        });

        unset($this->loaded[$model]);

        return $keys;
    }

    /**
     * @param list<string> $keys
     */
    #[Override]
    public function delete(
        HasPreferences&Model $model,
        PreferencesConfig $config,
        array $keys,
    ): void {
        $this->owned($model, $config)->whereIn('key', $keys)->delete();

        unset($this->loaded[$model]);
    }

    /**
     * @param non-empty-list<HasPreferences&Model> $models
     */
    #[Override]
    public function load(array $models, PreferencesConfig $config): void
    {
        $rows = $this->rowsByKey(
            $this->query($models[0], $config),
            $this->foreignKey(),
            $models[0],
            $this->storedKeys($models),
            ['key', 'value'],
        );

        foreach ($models as $model) {
            $key = $this->normalizeKey($model, $this->storedKeys([$model])[0]);
            $preferences = [];

            /** @var object{key: string, value: null|string}&stdClass $row */
            foreach ($rows[$key] ?? [] as $row) {
                $preferences[$row->key] = $row->value === null
                    ? null
                    : json_decode($row->value, true, 512, JSON_THROW_ON_ERROR);
            }

            $this->loaded[$model] = $preferences;
        }
    }

    /**
     * @param array<array-key, mixed> $values
     */
    #[Override]
    public function set(HasPreferences&Model $model, PreferencesConfig $config, array $values): void
    {
        $owner = [...$this->constraints($model), $this->foreignKey() => $this->storedKey($model)];

        $this->query($model, $config)->upsert(
            array_map(
                fn (int|string $key, mixed $value): array
                    => [
                        ...$owner,
                        'key' => (string) $key,
                        'value' => json_encode($value, JSON_THROW_ON_ERROR),
                    ],
                array_keys($values),
                $values,
            ),
            [...array_keys($owner), 'key'],
            ['value'],
        );

        unset($this->loaded[$model]);
    }

    /**
     * @param EloquentBuilder<*> $query
     */
    #[Override]
    public function whereMissing(
        EloquentBuilder $query,
        PreferencesConfig $config,
        string $key,
    ): void {
        $this->whereOwns(
            $query,
            $this->query($this->queriedModel($query), $config, 'laravel_preferences')
                ->where('key', $key),
            true,
        );
    }

    /**
     * @param EloquentBuilder<*> $query
     */
    #[Override]
    public function whereStored(
        EloquentBuilder $query,
        PreferencesConfig $config,
        string $key,
        mixed $value,
    ): void {
        $preferences = $this->query($this->queriedModel($query), $config, 'laravel_preferences')
            ->where('key', $key);

        $this->whereJson(
            $preferences,
            JsonExpression::equals($preferences, 'laravel_preferences.value', null, $value),
        );

        $this->whereOwns($query, $preferences);
    }

    protected function connection(Model $model): ConnectionInterface
    {
        return $model->getConnection();
    }

    /**
     * @return array<non-empty-string, string>
     */
    protected function constraints(Model $model): array
    {
        return [];
    }

    protected function owned(Model $model, PreferencesConfig $config): Builder
    {
        return $this->query($model, $config)->where($this->foreignKey(), $this->storedKey($model));
    }

    protected function query(
        Model $model,
        PreferencesConfig $config,
        null|string $as = null,
    ): Builder {
        $table = $this->tableName($model, $config);

        return $this
            ->connection($model)
            ->table($as === null ? $table : $table.' as '.$as)
            ->where($this->constraints($model));
    }

    /**
     * @param EloquentBuilder<*> $query
     */
    protected function whereOwns(
        EloquentBuilder $query,
        Builder $preferences,
        bool $not = false,
    ): void {
        $model = $query->getModel();
        $key = $this->qualified($query, $model->getKeyName());

        if ($preferences->getConnection() === $query->getQuery()->getConnection()) {
            $query->getQuery()->addWhereExistsQuery(
                $preferences->whereColumn($this->foreignKey(), $key),
                'and',
                $not,
            );

            return;
        }

        $this->whereKeys(
            $query->getQuery(),
            $key,
            $model,
            $preferences->pluck($this->foreignKey())->all(),
            $not,
        );
    }
}
