<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences;

use HosmelQ\ModelPreferences\Contracts\HasPreferences;
use HosmelQ\ModelPreferences\Contracts\PreferenceDriver;
use HosmelQ\ModelPreferences\Drivers\ArrayDriver;
use HosmelQ\ModelPreferences\Drivers\ColumnDriver;
use HosmelQ\ModelPreferences\Drivers\SharedTableDriver;
use HosmelQ\ModelPreferences\Drivers\TableDriver;
use HosmelQ\ModelPreferences\Support\Config;
use HosmelQ\ModelPreferences\Support\PreferencesConfig;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Manager;
use Override;
use UnexpectedValueException;
use WeakMap;

class PreferencesManager extends Manager
{
    /**
     * @var WeakMap<HasPreferences&Model, PreferencesConfig>
     */
    protected WeakMap $configs;

    protected null|ArrayDriver $fake = null;

    public function __construct(Container $container)
    {
        parent::__construct($container);

        $this->configs = new WeakMap();
    }

    public function config(HasPreferences&Model $model): PreferencesConfig
    {
        return $this->configs[$model] ??= $model->preferencesConfig();
    }

    public function driverFor(PreferencesConfig $config): PreferenceDriver
    {
        $driver = $this->fake ?? $this->driver($config->getDriver());

        if (! $driver instanceof PreferenceDriver) {
            throw new UnexpectedValueException(
                sprintf(
                    'The [%s] driver must implement [%s].',
                    $config->getDriver(),
                    PreferenceDriver::class,
                ),
            );
        }

        return $driver;
    }

    public function fake(): ArrayDriver
    {
        return $this->fake = new ArrayDriver();
    }

    public function for(HasPreferences&Model $model): PreferenceRepository
    {
        $config = $this->config($model);

        return new PreferenceRepository($model, $config, $this->driverFor($config));
    }

    #[Override]
    public function getDefaultDriver(): string
    {
        return Config::default();
    }

    /**
     * @param iterable<Model> $models
     */
    public function load(iterable $models): void
    {
        $groups = [];

        foreach ($models as $model) {
            if (
                $model instanceof HasPreferences
                && $model->exists
                && isset($model->getAttributes()[$model->getKeyName()])
            ) {
                $config = $this->config($model);
                $groups[implode(
                    '|',
                    [
                        $model::class,
                        $model->getConnection()->getName(),
                        $model->getTable(),
                        $config->getDriver(),
                        $config->getTable(),
                        $config->getColumn(),
                    ],
                )][] = $model;
            }
        }

        foreach ($groups as $group) {
            $config = $this->config($group[0]);

            $this->driverFor($config)->load($group, $config);
        }
    }

    protected function createColumnDriver(): ColumnDriver
    {
        return new ColumnDriver();
    }

    protected function createSharedDriver(): SharedTableDriver
    {
        return new SharedTableDriver();
    }

    protected function createTableDriver(): TableDriver
    {
        return new TableDriver();
    }
}
