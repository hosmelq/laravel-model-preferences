<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences;

use BackedEnum;
use HosmelQ\ModelPreferences\Contracts\HasPreferences;
use HosmelQ\ModelPreferences\Contracts\PreferenceDriver;
use HosmelQ\ModelPreferences\Contracts\PresenceAwarePreferenceDriver;
use HosmelQ\ModelPreferences\Exceptions\PreferenceValidationException;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;

class PendingPreferenceInteraction
{
    /**
     * Create a new pending preference interaction instance.
     */
    public function __construct(protected PreferenceDriver $driver, protected HasPreferences $model)
    {
    }

    /**
     * Get all preferences.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->driver->all($this->model);
    }

    /**
     * Delete all preferences.
     */
    public function clear(): void
    {
        $this->driver->clear($this->model);
    }

    /**
     * Delete a preference.
     */
    public function delete(BackedEnum|string $key): void
    {
        $this->driver->delete($this->model, (string) enum_value($key));
    }

    /**
     * Delete multiple preferences.
     *
     * @param array<int, BackedEnum|string> $keys
     */
    public function deleteMultiple(array $keys): void
    {
        $normalized = [];

        foreach ($keys as $key) {
            $normalized[] = (string) enum_value($key);
        }

        $this->driver->deleteMultiple($this->model, $normalized);
    }

    /**
     * Get a preference value.
     */
    public function get(BackedEnum|string $key, mixed $default = null): mixed
    {
        $keyValue = (string) enum_value($key);

        if ($this->driver instanceof PresenceAwarePreferenceDriver) {
            $read = $this->driver->getWithPresence($this->model, $keyValue);

            if ($read->exists) {
                return $read->value;
            }

            return $this->resolveMissingDefault($keyValue, $default);
        }

        if (! is_null($value = $this->driver->get($this->model, $keyValue))) {
            return $value;
        }

        if ($this->driver->has($this->model, $keyValue)) {
            return null;
        }

        return $this->resolveMissingDefault($keyValue, $default);
    }

    /**
     * Get multiple preference values.
     *
     * @param array<int, BackedEnum|string> $keys
     *
     * @return array<string, mixed>
     */
    public function getMultiple(array $keys): array
    {
        $normalizedKeys = [];

        foreach ($keys as $key) {
            $normalizedKeys[] = (string) enum_value($key);
        }

        $values = $this->driver->getMultiple($this->model, $normalizedKeys);
        $results = [];

        foreach ($normalizedKeys as $key) {
            if (array_key_exists($key, $values)) {
                $results[$key] = $values[$key];

                continue;
            }

            $results[$key] = $this->resolveMissingDefault($key, null);
        }

        return $results;
    }

    /**
     * Check if a preference exists.
     */
    public function has(BackedEnum|string $key): bool
    {
        return $this->driver->has($this->model, (string) enum_value($key));
    }

    /**
     * Determine if a preference is missing.
     */
    public function missing(BackedEnum|string $key): bool
    {
        return ! $this->has($key);
    }

    /**
     * Set a preference value.
     *
     * @throws PreferenceValidationException
     */
    public function set(BackedEnum|string $key, mixed $value): void
    {
        $keyValue = (string) enum_value($key);

        $this->validatePreference($keyValue, $value);

        $this->driver->set($this->model, $keyValue, $value);
    }

    /**
     * Set multiple preference values.
     *
     * @param array<string, mixed> $values
     *
     * @throws PreferenceValidationException
     */
    public function setMultiple(array $values): void
    {
        $normalized = [];

        foreach ($values as $key => $value) {
            $keyValue = (string) enum_value($key);

            $this->validatePreference($keyValue, $value);

            $normalized[$keyValue] = $value;
        }

        $this->driver->setMultiple($this->model, $normalized);
    }

    /**
     * Resolve the default value for a missing preference.
     */
    protected function resolveMissingDefault(string $keyValue, mixed $default): mixed
    {
        $defaults = $this->model->preferencesConfig()->getDefaults();

        if (array_key_exists($keyValue, $defaults)) {
            return $defaults[$keyValue];
        }

        if (! is_null($default)) {
            return value($default);
        }

        return null;
    }

    /**
     * Validate a single preference against model rules.
     *
     * @throws PreferenceValidationException
     */
    protected function validatePreference(string $key, mixed $value): void
    {
        /** @var array<string, array<mixed>|string|ValidationRule> $allRules */
        $allRules = $this->model->preferencesConfig()->getRules();

        if (is_null($rules = $allRules[$key] ?? null)) {
            return;
        }

        $validator = Validator::make([$key => $value], [$key => $rules]);

        if ($validator->fails()) {
            throw new PreferenceValidationException($validator);
        }
    }
}
