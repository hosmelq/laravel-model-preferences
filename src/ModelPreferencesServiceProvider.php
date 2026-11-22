<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences;

use HosmelQ\ModelPreferences\Commands\MakePreferencesTable;
use HosmelQ\ModelPreferences\Facades\Preferences;
use Illuminate\Database\Eloquent\Collection;
use Override;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ModelPreferencesServiceProvider extends PackageServiceProvider
{
    #[Override]
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

    #[Override]
    public function packageBooted(): void
    {
        Collection::macro('loadPreferences', function (): Collection {
            Preferences::load($this);

            return $this;
        });
    }

    #[Override]
    public function packageRegistered(): void
    {
        $this->app->singleton(PreferencesManager::class);
    }
}
