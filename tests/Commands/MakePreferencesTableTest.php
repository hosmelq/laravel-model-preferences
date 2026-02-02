<?php

declare(strict_types=1);

use function Pest\Laravel\artisan;

use HosmelQ\ModelPreferences\Commands\MakePreferencesTable;
use Orchestra\Testbench\Concerns\InteractsWithPublishedFiles;

uses(InteractsWithPublishedFiles::class);

it('creates a migration file with the correct name', function (): void {
    artisan(MakePreferencesTable::class, ['name' => 'test_preferences'])
        ->assertSuccessful();

    $this->assertMigrationFileExists('*_create_test_preferences_table.php');
});

it('displays success message with migration name', function (): void {
    artisan(MakePreferencesTable::class, ['name' => 'user_preferences'])
        ->expectsOutputToContain('created successfully')
        ->assertSuccessful();
});

it('generates migration with all required columns', function (): void {
    artisan(MakePreferencesTable::class, ['name' => 'test_preferences'])
        ->assertSuccessful();

    $this->assertMigrationFileContains([
        '$table->id()',
        '$table->unsignedBigInteger(\'model_id\')',
        '$table->string(\'key\')',
        '$table->json(\'value\')->nullable()',
        '$table->timestamps()',
    ], '*_create_test_preferences_table.php');
});

it('generates migration with correct index', function (): void {
    artisan(MakePreferencesTable::class, ['name' => 'test_preferences'])
        ->assertSuccessful();

    $this->assertMigrationFileContains([
        "\$table->unique(['model_id', 'key'])",
    ], '*_create_test_preferences_table.php');
});
