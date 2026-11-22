<?php

declare(strict_types=1);

use function Pest\Laravel\expectsDatabaseQueryCount;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\MissingAttributeException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Workbench\App\Models\SharedUser;
use Workbench\App\Models\SoftDeletingUser;
use Workbench\App\Models\Team;

it('reads every preference of a model with one query', function (string $model): void {
    $model::create()->preferences()->setMultiple(['digest' => 'daily', 'volume' => 7]);

    $user = $model::query()->firstOrFail();

    expectsDatabaseQueryCount(1);

    expect($user->preferences())
        ->get('digest')->toBe('daily')
        ->get('volume')->toBe(7)
        ->has('locale')->toBeFalse();
})->with('database models');

it('eager loads the preferences of many models with one query, skipping models without a key', function (
    string $model,
): void {
    $model::create()->preferences()->set('volume', 1);
    $model::create()->preferences()->set('volume', 2);

    $users = $model::query()->orderBy('id')->get();
    $keyless = $model::query()->select('updated_at')->firstOrFail();

    expectsDatabaseQueryCount(1);

    $users->concat([$keyless])->loadPreferences();

    expect($users->map(fn ($user): mixed => $user->preferences()->get('volume'))->all())->toBe([
        1,
        2,
    ])
        ->and(fn () => $keyless->preferences()->get('volume'))->toThrow(
            MissingAttributeException::class,
        );
})->with('database models');

it('eager loads preferences without the settings of other stores', function (string $model): void {
    config(['model-preferences.stores.column' => null]);

    $model::create()->preferences()->set('volume', 1);

    expect(
        $model::query()->get()->loadPreferences()->firstOrFail()->preferences()->get('volume'),
    )->toBe(
        1,
    );
})->with('database models');

it('eager loads two instances of one model with one query', function (): void {
    $team = Team::create();
    $first = Team::findOrFail($team->id);
    $second = Team::findOrFail($team->id);

    expectsDatabaseQueryCount(1);

    new Collection([$first, $second])->loadPreferences();

    expect($first->preferences()->get('digest'))->toBeNull()
        ->and($second->preferences()->get('digest'))->toBeNull();
});

it('stores preferences with one query', function (string $model): void {
    $user = $model::create();

    expectsDatabaseQueryCount(1);

    $user->preferences()->setMultiple(['digest' => 'daily', 'volume' => 7]);
})->with('database models');

it('stores and eager loads preferences of models with UUID keys in one query', function (): void {
    $team = Team::create();

    $team->preferences()->set('digest', 'daily');

    $upper = Team::forceCreate(['id' => Str::upper((string) Str::uuid())]);

    $upper->preferences()->set('digest', 'weekly');

    $copy = $upper->fresh();

    $copy->preferences()->set('digest', 'never');

    expectsDatabaseQueryCount(1);

    new Collection([$team, $upper, $copy])->loadPreferences();

    expect($team->preferences()->get('digest'))->toBe('daily')
        ->and($upper->preferences()->get('digest'))->toBe('never')
        ->and($copy->preferences()->get('digest'))->toBe('never');
});

it('keeps shared preferences on the configured connection', function (): void {
    config([
        'database.connections.preferences' => ['database' => ':memory:', 'driver' => 'sqlite'],
        'model-preferences.stores.shared.connection' => 'preferences',
    ]);

    DB::usingConnection(
        'preferences',
        fn () => (require __DIR__.'/../../database/migrations/create_preferences_table.php')->up(),
    );

    $daily = SharedUser::create();

    $daily->preferences()->set('digest', 'daily');

    SharedUser::create();

    expect(DB::connection('preferences')->table('preferences')->count())->toBe(1)
        ->and(DB::table('preferences')->count())->toBe(0)
        ->and(SharedUser::findOrFail($daily->id)->preferences()->get('digest'))->toBe('daily')
        ->and(SharedUser::query()->wherePreference('digest', 'daily')->pluck('id')->all())->toBe([
            $daily->id,
        ])
        ->and(SharedUser::query()->wherePreference('digest', 'weekly')->count())->toBe(1);
});

it('deletes the preferences of deleted models', function (string $model): void {
    $user = $model::create();

    $user->preferences()->set('digest', 'daily');

    $user->delete();

    expect(
        new $model()->newFromBuilder(['id' => $user->id])->preferences()->has('digest'),
    )->toBeFalse();
})->with('database models');

it('addresses the preferences of models by their stored key', function (string $model): void {
    $user = $model::create();

    $user->preferences()->set('digest', 'daily');

    $other = $model::create();

    $other->preferences()->set('digest', 'never');

    $user->id = $other->id;

    expect($user->preferences()->get('digest'))->toBe('daily');

    $user->delete();

    expect($other->fresh()->preferences()->get('digest'))->toBe('never');
})->with('database models');

it('keeps the preferences of soft deleted models until they are force deleted', function (): void {
    $user = SoftDeletingUser::create();

    $user->preferences()->set('digest', 'daily');

    $user->delete();
    $user->restore();

    expect($user->preferences()->get('digest'))->toBe('daily');

    $user->forceDelete();

    expect(
        new SoftDeletingUser()->newFromBuilder(['id' => $user->id])->preferences()->has('digest'),
    )->toBeFalse();
});
