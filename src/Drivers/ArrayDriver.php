<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences\Drivers;

use Closure;
use HosmelQ\ModelPreferences\Contracts\HasPreferences;
use HosmelQ\ModelPreferences\Contracts\PreferenceDriver;
use HosmelQ\ModelPreferences\Drivers\Concerns\BuildsPreferenceQueries;
use HosmelQ\ModelPreferences\Support\PreferencesConfig;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MissingAttributeException;
use Illuminate\Database\Eloquent\Model;
use Override;

class ArrayDriver implements PreferenceDriver
{
    use BuildsPreferenceQueries;

    /**
     * @var array<string, array<string, int|string>>
     */
    protected array $owners = [];

    /**
     * @var array<string, array<string, array<string, mixed>>>
     */
    protected array $preferences = [];

    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function all(HasPreferences&Model $model, PreferencesConfig $config): array
    {
        if ($this->storedKey($model) === null) {
            throw new MissingAttributeException($model, $model->getKeyName());
        }

        return $this->preferences[$this->table($model)][$this->key($model)] ?? [];
    }

    /**
     * @return list<string>
     */
    #[Override]
    public function clear(HasPreferences&Model $model, PreferencesConfig $config): array
    {
        $table = $this->table($model);
        $key = $this->key($model);
        $keys = array_map(strval(...), array_keys($this->preferences[$table][$key] ?? []));

        unset($this->preferences[$table][$key], $this->owners[$table][$key]);

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
        $this->put($model, array_diff_key($this->all($model, $config), array_flip($keys)));
    }

    /**
     * @param non-empty-list<HasPreferences&Model> $models
     */
    #[Override]
    public function load(array $models, PreferencesConfig $config): void {}

    /**
     * @param array<string, mixed> $values
     */
    #[Override]
    public function set(HasPreferences&Model $model, PreferencesConfig $config, array $values): void
    {
        $this->put($model, array_replace(
            $this->all($model, $config),
            array_map(
                fn (mixed $value): mixed
                    => json_decode(
                        json_encode($value, JSON_THROW_ON_ERROR),
                        true,
                        512,
                        JSON_THROW_ON_ERROR,
                    ),
                $values,
            ),
        ));
    }

    /**
     * @param Builder<*> $query
     */
    #[Override]
    public function whereMissing(Builder $query, PreferencesConfig $config, string $key): void
    {
        $this->whereKeys(
            $query->getQuery(),
            $this->qualified($query, $query->getModel()->getKeyName()),
            $query->getModel(),
            $this->keysWhere(
                $this->queriedModel($query),
                fn (array $preferences): bool => array_key_exists($key, $preferences),
            ),
            true,
        );
    }

    /**
     * @param Builder<*> $query
     */
    #[Override]
    public function whereStored(
        Builder $query,
        PreferencesConfig $config,
        string $key,
        mixed $value,
    ): void {
        $this->whereKeys(
            $query->getQuery(),
            $this->qualified($query, $query->getModel()->getKeyName()),
            $query->getModel(),
            $this->keysWhere(
                $this->queriedModel($query),
                fn (array $preferences): bool
                    => array_key_exists($key, $preferences) && $preferences[$key] === $value,
            ),
        );
    }

    protected function key(Model $model): string
    {
        return $this->normalizeKey($model, $this->storedKeys([$model])[0]);
    }

    /**
     * @param Closure(array<string, mixed>): bool $filter
     *
     * @return list<int|string>
     */
    protected function keysWhere(Model $model, Closure $filter): array
    {
        $table = $this->table($model);

        return array_values(
            array_intersect_key(
                $this->owners[$table] ?? [],
                array_filter($this->preferences[$table] ?? [], $filter),
            ),
        );
    }

    /**
     * @param array<string, mixed> $preferences
     */
    protected function put(Model $model, array $preferences): void
    {
        $table = $this->table($model);
        $key = $this->key($model);
        $this->preferences[$table][$key] = $preferences;
        $this->owners[$table][$key] = $this->storedKeys([$model])[0];
    }

    protected function table(Model $model): string
    {
        return $model->getConnection()->getName().'|'.$model->getTable();
    }
}
