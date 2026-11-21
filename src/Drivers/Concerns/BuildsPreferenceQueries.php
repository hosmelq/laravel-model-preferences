<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences\Drivers\Concerns;

use function Safe\preg_match;

use HosmelQ\ModelPreferences\Support\JsonExpression;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\MissingAttributeException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Str;
use stdClass;

trait BuildsPreferenceQueries
{
    protected function normalizeKey(Model $model, int|string $key): string
    {
        if (in_array($model->getKeyType(), ['int', 'integer'], true)) {
            return (string) (int) $key;
        }

        $key = (string) $key;

        return Str::isUuid($key) ? mb_strtolower($key) : $key;
    }

    /**
     * @param EloquentBuilder<*> $query
     */
    protected function qualified(EloquentBuilder $query, string $column): string
    {
        return $this->queriedTable($query)[1].'.'.$column;
    }

    /**
     * @param EloquentBuilder<*> $query
     */
    protected function queriedModel(EloquentBuilder $query): Model
    {
        return (clone $query->getModel())->setTable($this->queriedTable($query)[0]);
    }

    /**
     * @param EloquentBuilder<*> $query
     *
     * @return array{string, string}
     */
    protected function queriedTable(EloquentBuilder $query): array
    {
        $from = $query->getQuery()->from;

        if (! is_string($from)) {
            return [$query->getModel()->getTable(), $query->getModel()->getTable()];
        }

        if (
            preg_match('/^(.+?)\s+as\s+(.+)$/i', $from, $matches) === 1
            && isset($matches[1], $matches[2])
        ) {
            return [$matches[1], $matches[2]];
        }

        return [$from, $from];
    }

    /**
     * @param non-empty-list<int|string> $keys
     * @param list<string> $columns
     *
     * @return array<string, list<stdClass>>
     */
    protected function rowsByKey(
        Builder $query,
        string $column,
        Model $model,
        array $keys,
        array $columns,
    ): array {
        $integers = in_array($model->getKeyType(), ['int', 'integer'], true);
        $distinct = array_values(array_unique($integers ? array_map(intval(...), $keys) : $keys));
        $chunks = $integers ? [$distinct] : array_chunk($distinct, 1_000);
        $rows = [];

        foreach ($chunks as $chunk) {
            $batch = clone $query;

            $this->whereKeys($batch, $column, $model, $chunk);

            /** @var object{laravel_key: int|string}&stdClass $row */
            foreach ($batch->get([...$columns, $column.' as laravel_key']) as $row) {
                $rows[$this->normalizeKey($model, $row->laravel_key)][] = $row;
            }
        }

        return $rows;
    }

    protected function storedKey(Model $model): null|int|string
    {
        $key = $model->getRawOriginal($model->getKeyName(), $model->getKey());

        return is_int($key) || is_string($key) ? $key : null;
    }

    /**
     * @param non-empty-list<Model> $models
     *
     * @return non-empty-list<int|string>
     */
    protected function storedKeys(array $models): array
    {
        return array_map(function (Model $model): int|string {
            $key = $this->storedKey($model);

            if ($key === null) {
                throw new MissingAttributeException($model, $model->getKeyName());
            }

            return $key;
        }, $models);
    }

    protected function whereJson(Builder $query, JsonExpression $expression): void
    {
        $query->whereRaw($expression, $expression->bindings);
    }

    /**
     * @param array<array-key, mixed> $keys
     */
    protected function whereKeys(
        Builder $query,
        string $column,
        Model $model,
        array $keys,
        bool $not = false,
    ): void {
        if (in_array($model->getKeyType(), ['int', 'integer'], true)) {
            $query->whereIntegerInRaw($column, $keys, 'and', $not);
        } else {
            $query->whereIn($column, $keys, 'and', $not);
        }
    }
}
