<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences;

use HosmelQ\ModelPreferences\Commands\MakePreferencesTable;
use Illuminate\Contracts\Container\Container;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ModelPreferencesServiceProvider extends PackageServiceProvider
{
    /**
     * Configure package.
     */
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-model-preferences')
            ->hasConfigFile()
            ->hasCommand(MakePreferencesTable::class)
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('hosmelq/laravel-model-preferences')
                    ->publishConfigFile()
                    ->publishMigrations();
            })
            ->hasMigration('create_preferences_table');
    }

    /**
     * Register services.
     */
    public function packageRegistered(): void
    {
        $this->app->singleton(PreferencesManager::class, $this->createPreferencesManager(...));
    }

    /**
     * Create preferences manager instance.
     */
    private function createPreferencesManager(Container $app): PreferencesManager
    {
        return new PreferencesManager($app);
    }
}
