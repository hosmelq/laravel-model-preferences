<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences\Tests\TestSupport\Drivers;

use HosmelQ\ModelPreferences\Contracts\HasPreferences;
use HosmelQ\ModelPreferences\Contracts\PreferenceDriver;

class DummyPreferenceDriver implements PreferenceDriver
{
    public function all(HasPreferences $model): array
    {
        return [];
    }

    public function clear(HasPreferences $model): void
    {
    }

    public function delete(HasPreferences $model, string $key): void
    {
    }

    public function deleteMultiple(HasPreferences $model, array $keys): void
    {
    }

    public function get(HasPreferences $model, string $key): mixed
    {
        return null;
    }

    public function getMultiple(HasPreferences $model, array $keys): array
    {
        return [];
    }

    public function has(HasPreferences $model, string $key): bool
    {
        return false;
    }

    public function missing(HasPreferences $model, string $key): bool
    {
        return true;
    }

    public function set(HasPreferences $model, string $key, mixed $value): void
    {
    }

    public function setMultiple(HasPreferences $model, array $values): void
    {
    }
}
