<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences\Drivers;

use Closure;
use HosmelQ\ModelPreferences\Contracts\HasPreferences;
use HosmelQ\ModelPreferences\Contracts\PreferenceDriver;
use HosmelQ\ModelPreferences\Drivers\Concerns\BuildsPreferenceQueries;
use HosmelQ\ModelPreferences\Support\Config;
use HosmelQ\ModelPreferences\Support\JsonExpression;
use HosmelQ\ModelPreferences\Support\PreferencesConfig;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MissingAttributeException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use LogicException;
use Override;
use stdClass;
use WeakMap;

class ColumnDriver implements PreferenceDriver
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
     * @return array<string, mixed>
     */
    #[Override]
    public function all(HasPreferences&Model $model, PreferencesConfig $config): array
    {
        $column = $this->column($model, $config);

        if (array_key_exists($column, $model->getAttributes())) {
            return $this->decode($model, $column, $model->getAttributes()[$column]);
        }

        if (! isset($this->loaded[$model])) {
            if ($this->storedKey($model) === null) {
                throw new MissingAttributeException($model, $column);
            }

            $this->loaded[$model] = $this->decode(
                $model,
                $column,
                $this->row($model)->value($column),
            );
        }

        return $this->loaded[$model];
    }

    /**
     * @return list<string>
     */
    #[Override]
    public function clear(HasPreferences&Model $model, PreferencesConfig $config): array
    {
        /** @var list<string> $keys */
        $keys = [];

        $this->write($model, $config, function (array $preferences) use (&$keys): array {
            $keys = array_map(strval(...), array_keys($preferences));

            return [];
        });

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
        $this->write(
            $model,
            $config,
            fn (array $preferences): array => array_diff_key($preferences, array_flip($keys)),
        );
    }

    /**
     * @param non-empty-list<HasPreferences&Model> $models
     */
    #[Override]
    public function load(array $models, PreferencesConfig $config): void
    {
        $column = $this->column($models[0], $config);
        $models = array_values(array_filter(
            $models,
            fn (HasPreferences&Model $model): bool
                => ! array_key_exists($column, $model->getAttributes()),
        ));

        if ($models === []) {
            return;
        }

        $rows = $this->rowsByKey(
            $models[0]->getConnection()->table($models[0]->getTable()),
            $models[0]->getKeyName(),
            $models[0],
            $this->storedKeys($models),
            [$column],
        );

        foreach ($models as $model) {
            $key = $this->normalizeKey($model, $this->storedKeys([$model])[0]);
            $row = $rows[$key][0] ?? null;
            $this->loaded[$model] = $row instanceof stdClass
                ? $this->decode($model, $column, $row->{$column})
                : [];
        }
    }

    /**
     * @param array<string, mixed> $values
     */
    #[Override]
    public function set(HasPreferences&Model $model, PreferencesConfig $config, array $values): void
    {
        $this->write(
            $model,
            $config,
            fn (array $preferences): array => array_replace($preferences, $values),
        );
    }

    /**
     * @param Builder<*> $query
     */
    #[Override]
    public function whereMissing(Builder $query, PreferencesConfig $config, string $key): void
    {
        $column = $this->qualified($query, $this->column($query->getModel(), $config));

        $this->whereJson(
            $query->getQuery(),
            JsonExpression::lacksKey($query->getQuery(), $column, $key),
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
        $column = $this->qualified($query, $this->column($query->getModel(), $config));

        $this->whereJson(
            $query->getQuery(),
            JsonExpression::equals($query->getQuery(), $column, $key, $value),
        );
    }

    protected function column(Model $model, PreferencesConfig $config): string
    {
        $column = $config->getColumn() ?? Config::storesColumnName();

        if (! $model->hasCast($column)) {
            throw new LogicException(
                sprintf(
                    'The [%s] model must cast the [%s] attribute to an array.',
                    $model::class,
                    $column,
                ),
            );
        }

        return $column;
    }

    /**
     * @return array<string, mixed>
     */
    protected function decode(Model $model, string $column, mixed $value): array
    {
        $instance = (clone $model)->setRawAttributes([$column => $value] + $model->getAttributes());

        /** @var null|array<string, mixed>|Arrayable<string, mixed> $preferences */
        $preferences = $instance->getAttribute($column);

        return $preferences instanceof Arrayable
            ? new Collection($preferences)->all()
            : $preferences ?? [];
    }

    protected function hasUnsavedChanges(Model $model, string $column): bool
    {
        $attributes = $model->getAttributes();
        $original = $model->getRawOriginal();

        if (! array_key_exists($column, $attributes)) {
            return false;
        }

        if (! array_key_exists($column, $original)) {
            return true;
        }

        return $model->isDirty($column);
    }

    protected function row(Model $model): QueryBuilder
    {
        return $model->getConnection()
            ->table($model->getTable())
            ->where($model->getKeyName(), $this->storedKey($model));
    }

    /**
     * @param Closure(array<string, mixed>): array<string, mixed> $callback
     */
    protected function write(
        HasPreferences&Model $model,
        PreferencesConfig $config,
        Closure $callback,
    ): void {
        if (! $model->exists) {
            return;
        }

        $column = $this->column($model, $config);

        if ($this->hasUnsavedChanges($model, $column)) {
            throw new LogicException(
                sprintf(
                    'Save the changes to the [%s] attribute of the [%s] model before changing its preferences.',
                    $column,
                    $model::class,
                ),
            );
        }

        $model->getConnection()->transaction(function () use ($callback, $column, $model): void {
            $row = $this->row($model)->lockForUpdate()->first([$column])
                ?? throw new ModelNotFoundException()->setModel($model::class);
            $preferences = $callback($this->decode($model, $column, $row->{$column}));
            $copy = clone $model;

            $copy->setAttribute($column, $preferences);

            $encoded = $copy->getAttributes()[$column];
            $attributes = [$column => $encoded];
            $updatedAt = $model->usesTimestamps() ? $model->getUpdatedAtColumn() : null;

            if (
                $updatedAt !== null
                && array_key_exists($updatedAt, $model->getAttributes())
                && ! $model->originalIsEquivalent($updatedAt)
            ) {
                $updatedAt = null;
            }

            $timestamp = $model->freshTimestamp();

            if ($updatedAt !== null) {
                $attributes[$updatedAt] = $copy->setUpdatedAt($timestamp)
                    ->getAttributes()[$updatedAt];
            }

            $this->row($model)->update($attributes);

            if (array_key_exists($column, $model->getAttributes())) {
                $model->setAttribute($column, $preferences);
            } else {
                $this->loaded[$model] = $this->decode($model, $column, $encoded);
            }

            if ($updatedAt !== null) {
                $model->setUpdatedAt($timestamp);
            }

            $model->syncOriginalAttributes(
                array_keys(array_intersect_key($attributes, $model->getAttributes())),
            );
        });
    }
}
