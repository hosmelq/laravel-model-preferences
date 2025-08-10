<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences\Drivers;

use function Safe\json_decode;
use function Safe\json_encode;

use HosmelQ\ModelPreferences\Contracts\HasPreferences;
use HosmelQ\ModelPreferences\Contracts\PresenceAwarePreferenceDriver;
use HosmelQ\ModelPreferences\Exceptions\PreferenceTableNotFound;
use HosmelQ\ModelPreferences\Support\Config as PreferencesConfig;
use HosmelQ\ModelPreferences\Support\PreferenceRead;
use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Query\Builder;
use Safe\Exceptions\JsonException;

class TableDriver implements PresenceAwarePreferenceDriver
{
    /**
     * Create a new table driver instance.
     */
    public function __construct(protected DatabaseManager $db)
    {
    }

    /**
     * Get all preferences for a model.
     *
     * @return array<string, mixed>
     */
    public function all(HasPreferences $model): array
    {
        /** @var array<string, mixed> */
        return $this->newQuery($model)
            ->where($this->ownerKey(), $model->getKey())
            ->pluck('value', 'key')
            ->map(function (mixed $value): mixed {
                if (is_null($value)) {
                    return null;
                }

                assert(is_string($value));

                return json_decode($value, true);
            })
            ->all();
    }

    /**
     * Delete all preferences for a model.
     */
    public function clear(HasPreferences $model): void
    {
        $this->newQuery($model)
            ->where($this->ownerKey(), $model->getKey())
            ->delete();
    }

    /**
     * Delete a preference for a model.
     */
    public function delete(HasPreferences $model, string $key): void
    {
        $this->newQuery($model)
            ->where($this->ownerKey(), $model->getKey())
            ->where('key', $key)
            ->delete();
    }

    /**
     * Delete multiple preferences for a model.
     *
     * @param array<int, string> $keys
     */
    public function deleteMultiple(HasPreferences $model, array $keys): void
    {
        $this->newQuery($model)
            ->where($this->ownerKey(), $model->getKey())
            ->whereIn('key', $keys)
            ->delete();
    }

    /**
     * Get a preference value for a model.
     *
     * @throws JsonException
     */
    public function get(HasPreferences $model, string $key): mixed
    {
        return $this->getWithPresence($model, $key)->value;
    }

    /**
     * Get multiple preference values for a model.
     *
     * @param array<int, string> $keys
     *
     * @return array<string, mixed>
     */
    public function getMultiple(HasPreferences $model, array $keys): array
    {
        /** @var array<string, mixed> */
        return $this->newQuery($model)
            ->where($this->ownerKey(), $model->getKey())
            ->whereIn('key', $keys)
            ->pluck('value', 'key')
            ->map(function (mixed $value): mixed {
                if (is_null($value)) {
                    return null;
                }

                assert(is_string($value));

                return json_decode($value, true);
            })
            ->all();
    }

    /**
     * Get a preference value for a model with presence information.
     *
     * @throws JsonException
     */
    public function getWithPresence(HasPreferences $model, string $key): PreferenceRead
    {
        $row = $this->newQuery($model)
            ->where($this->ownerKey(), $model->getKey())
            ->where('key', $key)
            ->first(['value']);

        if (is_null($row)) {
            return new PreferenceRead(false, null);
        }

        $value = $row->value;

        if (is_null($value)) {
            return new PreferenceRead(true, null);
        }

        assert(is_string($value));

        return new PreferenceRead(true, json_decode($value, true));
    }

    /**
     * Check if a preference exists for a model.
     *
     * @throws JsonException
     */
    public function has(HasPreferences $model, string $key): bool
    {
        return $this->newQuery($model)
            ->where($this->ownerKey(), $model->getKey())
            ->where('key', $key)
            ->exists();
    }

    /**
     * Determine if a preference is missing for a model.
     */
    public function missing(HasPreferences $model, string $key): bool
    {
        return ! $this->has($model, $key);
    }

    /**
     * Set a preference value for a model.
     *
     * @throws JsonException
     */
    public function set(HasPreferences $model, string $key, mixed $value): void
    {
        $encoded = json_encode($value);

        $this->newQuery($model)->updateOrInsert([
            $this->ownerKey() => $model->getKey(),
            'key' => $key,
        ], [
            'value' => $encoded,
        ]);
    }

    /**
     * Set multiple preference values for a model.
     *
     * @param array<string, mixed> $values
     *
     * @throws JsonException
     */
    public function setMultiple(HasPreferences $model, array $values): void
    {
        if ($values === []) {
            return;
        }

        $rows = [];

        foreach ($values as $key => $value) {
            $rows[] = [
                $this->ownerKey() => $model->getKey(),
                'key' => $key,
                'value' => json_encode($value),
            ];
        }

        $this->newQuery($model)->upsert(
            $rows,
            [$this->ownerKey(), 'key'],
            ['value']
        );
    }

    /**
     * Get the database connection for this driver.
     */
    protected function connection(): Connection
    {
        $connectionName = PreferencesConfig::storesTableConnection();

        return $this->db->connection($connectionName);
    }

    /**
     * Get a new query builder for the model's preferences table.
     */
    protected function newQuery(HasPreferences $model): Builder
    {
        return $this->connection()->table($this->resolveTableName($model));
    }

    /**
     * Get the owner key column name for table-based preferences.
     */
    protected function ownerKey(): string
    {
        return 'model_id';
    }

    /**
     * Resolve the preferences table name for the model.
     *
     * @throws PreferenceTableNotFound
     */
    protected function resolveTableName(HasPreferences $model): string
    {
        $tableName = $model->preferencesConfig()->getTable();

        if (! is_string($tableName) || $tableName === '') {
            throw PreferenceTableNotFound::invalid($model);
        }

        return $tableName;
    }
}
