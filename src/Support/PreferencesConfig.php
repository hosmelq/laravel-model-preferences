<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences\Support;

use function HosmelQ\ModelPreferences\enum_value;

use HosmelQ\ModelPreferences\Enums\StoreDriver;

class PreferencesConfig
{
    /**
     * Create a new preferences config instance.
     */
    private function __construct(
        public null|string $column = null,
        /**
         * @var array<string, mixed>
         */
        public array $defaults = [],
        public null|string $driver = null,
        /**
         * @var array<string, mixed>
         */
        public array $rules = [],
        public null|string $table = null
    ) {
    }

    /**
     * Start configuring model preferences.
     */
    public static function configure(): self
    {
        return new self(
            column: Config::storesColumnName(),
            driver: Config::default()
        );
    }

    /**
     * Get the configured column name.
     */
    public function getColumn(): null|string
    {
        return $this->column;
    }

    /**
     * Get the configured default values.
     *
     * @return array<string, mixed>
     */
    public function getDefaults(): array
    {
        return $this->defaults;
    }

    /**
     * Get the configured driver name.
     */
    public function getDriver(): null|string
    {
        return $this->driver;
    }

    /**
     * Get the configured validation rules.
     *
     * @return array<string, mixed>
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * Get the configured table name.
     */
    public function getTable(): null|string
    {
        return $this->table;
    }

    /**
     * Set the configured column name.
     */
    public function withColumn(string $column): self
    {
        $this->column = $column;

        return $this;
    }

    /**
     * Set the configured default values.
     *
     * @param array<string, mixed> $defaults
     */
    public function withDefaults(array $defaults): self
    {
        $this->defaults = $defaults;

        return $this;
    }

    /**
     * Set the configured driver name.
     */
    public function withDriver(StoreDriver|string $driver): self
    {
        $this->driver = (string) enum_value($driver);

        return $this;
    }

    /**
     * Set the configured validation rules.
     *
     * @param array<string, mixed> $rules
     */
    public function withRules(array $rules): self
    {
        $this->rules = $rules;

        return $this;
    }

    /**
     * Set the configured table name.
     */
    public function withTable(string $table): self
    {
        $this->table = $table;

        return $this;
    }
}
