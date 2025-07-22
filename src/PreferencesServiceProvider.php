<?php

declare(strict_types=1);

namespace HosmelQ\ModelPreferences;

use HosmelQ\ModelPreferences\Contracts\PreferenceRepository as PreferenceRepositoryContract;
use HosmelQ\ModelPreferences\Repositories\PreferenceRepository;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class PreferencesServiceProvider extends PackageServiceProvider
{
    /**
     * Configure package.
     */
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-model-preferences')
            ->hasConfigFile()
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
        $this->app->bind(PreferenceRepositoryContract::class, PreferenceRepository::class);
    }
}
