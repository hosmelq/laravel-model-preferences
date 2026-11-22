<?php

declare(strict_types=1);

use HosmelQ\ModelPreferences\Drivers\ArrayDriver;
use HosmelQ\ModelPreferences\Facades\Preferences;
use HosmelQ\ModelPreferences\Support\PreferencesConfig;
use Illuminate\Database\Eloquent\MissingAttributeException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Workbench\App\Enums\Theme;
use Workbench\App\Models\TableUser;
use Workbench\App\Models\Team;
use Workbench\App\Models\User;

it('fakes the storage of every model', function (): void {
    Preferences::fake();

    $user = TableUser::create();

    $user->preferences()->setMultiple([
        'mode' => Theme::Dark,
        'theme' => Theme::Dark,
        'volume' => 7,
    ]);

    $team = Team::create();

    $team->preferences()->set('digest', 'daily');

    Team::forceCreate(['id' => Str::upper((string) Str::uuid())])
        ->preferences()
        ->set('digest', 'daily');

    expect(Team::findOrFail($team->id)->preferences()->get('digest'))->toBe('daily')
        ->and(Team::query()->wherePreference('digest', 'daily')->count())->toBe(2)
        ->and(TableUser::findOrFail($user->id)->preferences())
        ->get('theme')->toBe(Theme::Dark)
        ->get('volume')->toBe(7)
        ->and(new TableUser()->preferences()->get('digest'))->toBe('weekly')
        ->and(TableUser::query()->wherePreference('volume', 7)->pluck('id')->all())->toBe([
            $user->id,
        ])
        ->and(TableUser::query()->wherePreference('mode', Theme::Dark)->pluck('id')->all())->toBe([
            $user->id,
        ])
        ->and(TableUser::query()->wherePreference('digest', 'weekly')->pluck('id')->all())->toBe([
            $user->id,
        ])
        ->and(DB::table('user_preferences')->count())->toBe(0);
});

it('fakes the storage of models by their stored key', function (): void {
    Preferences::fake();

    $user = TableUser::create();
    $other = TableUser::create();

    $other->preferences()->set('digest', 'never');

    $user->id = $other->id;

    $user->delete();

    expect($other->fresh()->preferences()->get('digest'))->toBe('never');
});

it('fakes the refusal to read models selected without their key', function (): void {
    Preferences::fake();

    TableUser::create();

    TableUser::query()->select('updated_at')->firstOrFail()->preferences()->get('digest');
})->throws(MissingAttributeException::class);

it('uses custom drivers', function (): void {
    Preferences::extend('memory', fn (): ArrayDriver => new ArrayDriver());

    $user = new class () extends User {
        public function preferencesConfig(): PreferencesConfig
        {
            return parent::preferencesConfig()->withDriver('memory');
        }
    };

    $user->save();
    $user->preferences()->set('digest', 'daily');

    expect($user->preferences()->get('digest'))->toBe('daily')
        ->and(DB::table('users')->value('preferences'))->toBeNull();
});
