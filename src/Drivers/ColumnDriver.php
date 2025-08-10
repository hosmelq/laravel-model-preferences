<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences\Drivers;

use HosmelQ\ModelPreferences\Contracts\HasPreferences;
use HosmelQ\ModelPreferences\Contracts\PreferenceDriver;
use HosmelQ\ModelPreferences\Exceptions\PreferenceColumnNotFound;

class ColumnDriver implements PreferenceDriver
{
    /**
     * Get all preferences for a model.
     *
     * @return array<string, mixed>
     */
    public function all(HasPreferences $model): array
    {
        return $this->getPreferences($model);
    }

    /**
     * Delete all preferences for a model.
     */
    public function clear(HasPreferences $model): void
    {
        $this->savePreferences($model, []);
    }

    /**
     * Delete a preference for a model.
     */
    public function delete(HasPreferences $model, string $key): void
    {
        $preferences = $this->getPreferences($model);

        unset($preferences[$key]);

        $this->savePreferences($model, $preferences);
    }

    /**
     * Delete multiple preferences for a model.
     *
     * @param array<int, string> $keys
     */
    public function deleteMultiple(HasPreferences $model, array $keys): void
    {
        $preferences = $this->getPreferences($model);

        foreach ($keys as $key) {
            unset($preferences[$key]);
        }

        $this->savePreferences($model, $preferences);
    }

    /**
     * Get a preference value for a model.
     */
    public function get(HasPreferences $model, string $key): mixed
    {
        $preferences = $this->getPreferences($model);

        return $preferences[$key] ?? null;
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
        $preferences = $this->getPreferences($model);
        $results = [];

        foreach ($keys as $key) {
            if (array_key_exists($key, $preferences)) {
                $results[$key] = $preferences[$key];
            }
        }

        return $results;
    }

    /**
     * Check if a preference exists for a model.
     */
    public function has(HasPreferences $model, string $key): bool
    {
        $preferences = $this->getPreferences($model);

        return array_key_exists($key, $preferences);
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
     */
    public function set(HasPreferences $model, string $key, mixed $value): void
    {
        $preferences = $this->getPreferences($model);

        $preferences[$key] = $value;

        $this->savePreferences($model, $preferences);
    }

    /**
     * Set multiple preference values for a model.
     *
     * @param array<string, mixed> $values
     */
    public function setMultiple(HasPreferences $model, array $values): void
    {
        $preferences = $this->getPreferences($model);

        foreach ($values as $key => $value) {
            $preferences[$key] = $value;
        }

        $this->savePreferences($model, $preferences);
    }

    /**
     * Get all preferences from the model's JSON column.
     *
     * @return array<string, mixed>
     */
    protected function getPreferences(HasPreferences $model): array
    {
        $columnName = $this->resolveColumnName($model);

        if (! array_key_exists($columnName, $model->getAttributes())) {
            return [];
        }

        $value = $model->getAttribute($columnName);

        if (! is_array($value)) {
            return [];
        }

        $preferences = [];

        foreach ($value as $key => $item) {
            $preferences[(string) $key] = $item;
        }

        return $preferences;
    }

    /**
     * Resolve the preferences column name for a model.
     *
     * @throws PreferenceColumnNotFound
     */
    protected function resolveColumnName(HasPreferences $model): string
    {
        $columnName = $model->preferencesConfig()->getColumn();

        if (! is_string($columnName) || $columnName === '') {
            throw PreferenceColumnNotFound::invalid($model);
        }

        return $columnName;
    }

    /**
     * Save preferences to the model's JSON column.
     *

     * @param array<string, mixed> $preferences
     */
    protected function savePreferences(HasPreferences $model, array $preferences): void
    {
        $columnName = $this->resolveColumnName($model);

        $model->newQuery()->whereKey($model->getKey())->update([$columnName => $preferences]);
        $model->setAttribute($columnName, $preferences);
        $model->syncOriginalAttribute($columnName);
    }
}
