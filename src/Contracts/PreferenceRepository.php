<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 */
interface PreferenceRepository
{
    /**
     * Get all preferences for a model.
     *
     * @param HasPreferences<TModel> $model
     *
     * @return array<string, mixed>
     */
    public function all(HasPreferences $model): array;

    /**
     * Delete a preference by key for a model.
     *
     * @param HasPreferences<TModel> $model
     */
    public function delete(HasPreferences $model, string $key): void;

    /**
     * Delete multiple preferences by keys for a model.
     *
     * @param HasPreferences<TModel> $model
     * @param list<string> $keys
     */
    public function deleteMany(HasPreferences $model, array $keys): void;

    /**
     * Get a preference value for a model.
     *
     * @param HasPreferences<TModel> $model
     */
    public function get(HasPreferences $model, string $key): mixed;

    /**
     * Check if a preference key exists for a model.
     *
     * @param HasPreferences<TModel> $model
     */
    public function has(HasPreferences $model, string $key): bool;

    /**
     * Set a preference for a model.
     *
     * @param HasPreferences<TModel> $model
     */
    public function set(HasPreferences $model, string $key, mixed $value): void;

    /**
     * Set multiple preferences for a model.
     *
     * @param HasPreferences<TModel> $model
     * @param array<string, mixed> $preferences
     */
    public function setMany(HasPreferences $model, array $preferences): void;
}
