<?php

declare(strict_types=1);

use function Pest\Laravel\artisan;

use HosmelQ\ModelPreferences\Commands\MakePreferencesTable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Orchestra\Testbench\Concerns\InteractsWithPublishedFiles;
use Workbench\App\Models\ArchivedTeam;
use Workbench\App\Models\Team;

pest()->use(InteractsWithPublishedFiles::class);

it('creates a migration whose foreign key deletes the preferences with their model', function (): void {
    artisan(MakePreferencesTable::class, ['model' => Team::class])->assertSuccessful();

    config([
        'database.connections.generated' => [
            'database' => ':memory:',
            'driver' => 'sqlite',
            'foreign_key_constraints' => true,
        ],
    ]);

    $migration = require glob(database_path('migrations/*_create_team_preferences_table.php'))[0];

    DB::usingConnection('generated', function () use ($migration): void {
        Schema::create('teams', fn (Blueprint $table) => $table->uuid('id')->primary());

        $migration->up();

        $team = (string) Str::uuid();

        DB::table('teams')->insert(['id' => $team]);
        DB::table('team_preferences')->insert([
            'key' => 'digest',
            'model_id' => $team,
            'value' => '"daily"',
        ]);
        DB::table('teams')->delete();
    });

    expect(DB::connection('generated')->table('team_preferences')->count())->toBe(0);
});

it('creates the migration on the database connection of the model', function (): void {
    artisan(MakePreferencesTable::class, ['model' => ArchivedTeam::class])->assertSuccessful();

    $migration = require glob(
        database_path('migrations/*_create_archived_team_preferences_table.php'),
    )[0];

    expect($migration->getConnection())->toBe('archive');
});

it('refuses to create a migration that already exists', function (): void {
    artisan(MakePreferencesTable::class, ['model' => Team::class])->assertSuccessful();
    artisan(MakePreferencesTable::class, ['model' => Team::class])->assertFailed();
});
