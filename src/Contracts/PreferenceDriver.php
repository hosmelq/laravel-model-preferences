<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences\Contracts;

interface PreferenceDriver
{
    /**
     * Get all preferences for a model.
     *
     * @return array<string, mixed>
     */
    public function all(HasPreferences $model): array;

    /**
     * Delete all preferences for a model.
     */
    public function clear(HasPreferences $model): void;

    /**
     * Delete a preference for a model.
     */
    public function delete(HasPreferences $model, string $key): void;

    /**
     * Delete multiple preferences for a model.
     *
     * @param array<int, string> $keys
     */
    public function deleteMultiple(HasPreferences $model, array $keys): void;

    /**
     * Get a preference value for a model.
     */
    public function get(HasPreferences $model, string $key): mixed;

    /**
     * Get multiple preference values for a model.
     *
     * @param array<int, string> $keys
     *
     * @return array<string, mixed>
     */
    public function getMultiple(HasPreferences $model, array $keys): array;

    /**
     * Check if a preference exists for a model.
     */
    public function has(HasPreferences $model, string $key): bool;

    /**
     * Determine if a preference is missing for a model.
     */
    public function missing(HasPreferences $model, string $key): bool;

    /**
     * Set a preference value for a model.
     */
    public function set(HasPreferences $model, string $key, mixed $value): void;

    /**
     * Set multiple preference values for a model.
     *
     * @param array<string, mixed> $values
     */
    public function setMultiple(HasPreferences $model, array $values): void;
}
