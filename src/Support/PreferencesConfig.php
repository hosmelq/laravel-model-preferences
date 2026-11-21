<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences\Support;

class PreferencesConfig
{
    /**
     * @var array<string, string>
     */
    private array $casts = [];

    private null|string $column = null;

    /**
     * @var array<string, mixed>
     */
    private array $defaults = [];

    private null|string $driver = null;

    /**
     * @var array<array-key, mixed>
     */
    private array $rules = [];

    private bool $strict = false;

    private null|string $table = null;

    public static function configure(): self
    {
        return new self();
    }

    /**
     * @return array<string, string>
     */
    public function getCasts(): array
    {
        return $this->casts;
    }

    public function getColumn(): null|string
    {
        return $this->column;
    }

    /**
     * @return array<string, mixed>
     */
    public function getDefaults(): array
    {
        return $this->defaults;
    }

    public function getDriver(): string
    {
        return $this->driver ?? Config::default();
    }

    /**
     * @return array<array-key, mixed>
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    public function getTable(): null|string
    {
        return $this->table;
    }

    public function isStrict(): bool
    {
        return $this->strict;
    }

    public function strict(): self
    {
        $this->strict = true;

        return $this;
    }

    /**
     * @param array<string, string> $casts
     */
    public function withCasts(array $casts): self
    {
        $this->casts = $casts;

        return $this;
    }

    public function withColumn(string $column): self
    {
        $this->column = $column;

        return $this;
    }

    /**
     * @param array<string, mixed> $defaults
     */
    public function withDefaults(array $defaults): self
    {
        $this->defaults = $defaults;

        return $this;
    }

    public function withDriver(string $driver): self
    {
        $this->driver = $driver;

        return $this;
    }

    /**
     * @param array<array-key, mixed> $rules
     */
    public function withRules(array $rules): self
    {
        $this->rules = $rules;

        return $this;
    }

    public function withTable(string $table): self
    {
        $this->table = $table;

        return $this;
    }
}
