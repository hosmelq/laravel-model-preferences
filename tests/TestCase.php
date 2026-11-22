<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    use RefreshDatabase;
    use WithWorkbench;

    /**
     * {@inheritDoc}
     */
    protected function defineEnvironment($app): void
    {
        tap($app['config'], function (mixed $config): void {
            $config->set('model-preferences.default', 'shared');
            $config->set('model-preferences.stores.column.name', 'preferences');
            $config->set('model-preferences.stores.table.connection');
            $config->set('model-preferences.stores.shared.connection');
            $config->set('model-preferences.stores.shared.table', 'preferences');
        });
    }
}
