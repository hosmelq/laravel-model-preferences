<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences\Repositories;

use HosmelQ\ModelPreferences\Contracts\HasPreferences;
use HosmelQ\ModelPreferences\Contracts\PreferenceRepository as PreferenceRepositoryContract;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 *
 * @implements PreferenceRepositoryContract<TModel>
 */
class PreferenceRepository implements PreferenceRepositoryContract
{
    /**
     * Get all preferences for a model.
     *
     * @param HasPreferences<TModel> $model
     *
     * @return array<string, mixed>
     */
    public function all(HasPreferences $model): array
    {
        /** @var array<string, mixed> */
        return $model->preferences()->pluck('value', 'key')->toArray();
    }

    /**
     * Delete a preference by key for a model.
     *
     * @param HasPreferences<TModel> $model
     */
    public function delete(HasPreferences $model, string $key): void
    {
        $model->preferences()->where('key', $key)->delete();
    }

    /**
     * Delete multiple preferences by keys for a model.
     *
     * @param HasPreferences<TModel> $model
     * @param list<string> $keys
     */
    public function deleteMany(HasPreferences $model, array $keys): void
    {
        $model->preferences()->whereIn('key', $keys)->delete();
    }

    /**
     * Get a preference value for a model.
     *
     * @param HasPreferences<TModel> $model
     */
    public function get(HasPreferences $model, string $key): mixed
    {
        return $model->preferences()->where('key', $key)->value('value');
    }

    /**
     * Check if a preference key exists for a model.
     *
     * @param HasPreferences<TModel> $model
     */
    public function has(HasPreferences $model, string $key): bool
    {
        return $model->preferences()->where('key', $key)->exists();
    }

    /**
     * Set a preference for a model.
     *
     * @param HasPreferences<TModel> $model
     */
    public function set(HasPreferences $model, string $key, mixed $value): void
    {
        $model->preferences()->updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * Set multiple preferences for a model.
     *
     * @param HasPreferences<TModel> $model
     * @param array<string, mixed> $preferences
     */
    public function setMany(HasPreferences $model, array $preferences): void
    {
        foreach ($preferences as $key => $value) {
            $this->set($model, $key, $value);
        }
    }
}
