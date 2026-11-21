<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences\Contracts;

use HosmelQ\ModelPreferences\Support\PreferencesConfig;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

interface PreferenceDriver
{
    /**
     * @return array<string, mixed>
     */
    public function all(HasPreferences&Model $model, PreferencesConfig $config): array;

    /**
     * @return list<string>
     */
    public function clear(HasPreferences&Model $model, PreferencesConfig $config): array;

    /**
     * @param list<string> $keys
     */
    public function delete(
        HasPreferences&Model $model,
        PreferencesConfig $config,
        array $keys,
    ): void;

    /**
     * @param non-empty-list<HasPreferences&Model> $models
     */
    public function load(array $models, PreferencesConfig $config): void;

    /**
     * @param array<string, mixed> $values
     */
    public function set(
        HasPreferences&Model $model,
        PreferencesConfig $config,
        array $values,
    ): void;

    /**
     * @param Builder<*> $query
     */
    public function whereMissing(Builder $query, PreferencesConfig $config, string $key): void;

    /**
     * @param Builder<*> $query
     */
    public function whereStored(
        Builder $query,
        PreferencesConfig $config,
        string $key,
        mixed $value,
    ): void;
}
