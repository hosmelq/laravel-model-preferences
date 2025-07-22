<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences\Models\Concerns;

use function HosmelQ\ModelPreferences\enum_value;

use BackedEnum;
use HosmelQ\ModelPreferences\Contracts\PreferenceRepository;
use HosmelQ\ModelPreferences\Exceptions\PreferenceValidationException;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Throwable;

/**
 * @mixin Model
 */
trait InteractsWithPreferences
{
    /**
     * Boot the InteractsWithPreferences trait.
     */
    public static function bootInteractsWithPreferences(): void
    {
        static::deleted(function (Model $model): void {
            $model->preferences()->delete();
        });
    }

    /**
     * Get all preferences merged with defaults.
     *
     * @return array<string, mixed>
     */
    public function allPreferences(): array
    {
        return array_merge($this->preferenceDefaults(), $this->getRepository()->all($this));
    }

    /**
     * Delete a preference.
     */
    public function deletePreference(BackedEnum|string $key): static
    {
        $this->getRepository()->delete($this, enum_value($key));

        return $this;
    }

    /**
     * Delete multiple preferences.
     *
     * @param list<BackedEnum|string> $keys
     */
    public function deletePreferences(array $keys): static
    {
        $this->getRepository()->deleteMany(
            $this,
            array_map(fn (BackedEnum|string $key) => enum_value($key), $keys)
        );

        return $this;
    }

    /**
     * Check if a preference exists.
     */
    public function hasPreference(BackedEnum|string $key): bool
    {
        return $this->getRepository()->has($this, enum_value($key));
    }

    /**
     * Load specific preferences into the relationship.
     *
     * @param list<BackedEnum|string> $keys
     */
    public function loadPreferences(array $keys = []): static
    {
        $query = $this->preferences();

        if ($keys !== []) {
            $query->whereIn(
                'key',
                array_map(fn (BackedEnum|string $key) => enum_value($key), $keys)
            );
        }

        return $this->setRelation('preferences', $query->get());
    }

    /**
     * Get a preference value.
     */
    public function preference(BackedEnum|string $key, mixed $default = null): mixed
    {
        $value = $this->getRepository()->get($this, enum_value($key));

        if (is_null($value)) {
            return $this->getDefaultPreference($key, $default);
        }

        return $value;
    }

    /**
     * Define default preference values.
     *
     * @return array<string, mixed>
     */
    public function preferenceDefaults(): array
    {
        return [];
    }

    /**
     * Define preference validation rules.
     *
     * @return array<string, list<mixed>|string|ValidationRule>
     */
    public function preferenceRules(): array
    {
        return [];
    }

    /**
     * Get the preferences for the model.
     */
    public function preferences(): MorphMany
    {
        return $this->morphMany(Config::string('model-preferences.models.preference'), 'preferable');
    }

    /**
     * Set a preference value.
     *
     * @throws PreferenceValidationException
     */
    public function setPreference(BackedEnum|string $key, mixed $value): static
    {
        $this->validatePreferences([enum_value($key) => $value]);

        $this->getRepository()->set($this, enum_value($key), $value);

        return $this;
    }

    /**
     * Set multiple preferences.
     *
     * @param array<string, mixed> $preferences
     *
     * @throws PreferenceValidationException
     * @throws Throwable
     */
    public function setPreferences(array $preferences): static
    {
        $this->validatePreferences($preferences);

        $this->getRepository()->setMany($this, $preferences);

        return $this;
    }

    /**
     * Get default preference value.
     */
    protected function getDefaultPreference(BackedEnum|string $key, mixed $default = null): mixed
    {
        $defaults = $this->preferenceDefaults();

        if (! array_key_exists(enum_value($key), $defaults)) {
            return value($default);
        }

        return value($defaults[enum_value($key)]);
    }

    /**
     * Get validation rules for a specific preference key.
     *
     * @return list<mixed>|string|ValidationRule
     */
    protected function getPreferenceRules(BackedEnum|string $key): mixed
    {
        $rules = $this->preferenceRules();

        return $rules[enum_value($key)] ?? [];
    }

    /**
     * Get the preference repository instance.
     */
    protected function getRepository(): PreferenceRepository
    {
        return resolve(PreferenceRepository::class);
    }

    /**
     * Validate multiple preferences against their rules.
     *
     * @param array<string, mixed> $preferences
     *
     * @throws PreferenceValidationException
     */
    protected function validatePreferences(array $preferences): void
    {
        $rules = [];

        foreach (array_keys($preferences) as $key) {
            $preferenceRules = $this->getPreferenceRules($key);

            if (! empty($preferenceRules)) {
                $rules[enum_value($key)] = $preferenceRules;
            }
        }

        if ($rules === []) {
            return;
        }

        $validator = Validator::make($preferences, $rules);

        if ($validator->fails()) {
            throw new PreferenceValidationException($validator);
        }
    }
}
