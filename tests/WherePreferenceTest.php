<?php

declare(strict_types=1);

use HosmelQ\ModelPreferences\Facades\Preferences;
use HosmelQ\ModelPreferences\Support\PreferencesConfig;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Workbench\App\Casts\AsCents;
use Workbench\App\Casts\Cents;
use Workbench\App\Enums\Theme;
use Workbench\App\Models\TableUser;

it('filters models whose preference equals the value, counting defaults', function (
    string $model,
): void {
    $daily = $model::create();

    $daily->preferences()->setMultiple([
        'café' => 'au lait',
        'colors' => ['red', 'blue'],
        'digest' => 'daily',
        'locale' => null,
        'path/name' => 'value',
        'say "hi"' => 'hello',
        'label' => 'Red',
        'muted' => true,
        'ratio' => 3.5,
        'volume' => 7,
    ]);

    $weekly = $model::create();

    $weekly->preferences()->setMultiple([
        'digest' => 'weekly',
        'locale' => 'es',
        'muted' => 1,
        'theme' => Theme::Dark,
        'volume' => '7',
    ]);

    $default = $model::create();
    $ids = fn (string $key, mixed $value): array
        => $model::query()->wherePreference($key, $value)->orderBy('id')->pluck('id')->all();

    expect($ids('digest', 'daily'))->toBe([$daily->id])
        ->and($ids('digest', 'weekly'))->toBe([$weekly->id, $default->id])
        ->and($ids('locale', null))->toBe([$daily->id])
        ->and($ids('muted', true))->toBe([$daily->id])
        ->and($ids('ratio', 3.5))->toBe([$daily->id])
        ->and($ids('volume', 7))->toBe([$daily->id])
        ->and($ids('colors', 'red'))->toBeEmpty()
        ->and($ids('label', 'red'))->toBeEmpty()
        ->and($ids('label', 'Red '))->toBeEmpty()
        ->and($ids('café', 'au lait'))->toBe([$daily->id])
        ->and($ids('path/name', 'value'))->toBe([$daily->id])
        ->and($ids('say "hi"', 'hello'))->toBe([$daily->id])
        ->and($ids('theme', Theme::Dark))->toBe([$weekly->id])
        ->and($ids('theme', Theme::System))->toBe([$daily->id, $default->id]);
})->with('models');

it('filters date and timestamp preferences by the value they read back', function (): void {
    $user = new class () extends TableUser {
        public function preferencesConfig(): PreferencesConfig
        {
            return parent::preferencesConfig()->withCasts(['day' => 'date', 'seen' => 'timestamp']);
        }
    };

    $user->save();
    $user->preferences()->setMultiple([
        'day' => '2026-11-20 15:30:00',
        'seen' => '2026-11-20 15:30:00',
    ]);

    expect($user->preferences()->get('day')->toDateTimeString())->toBe('2026-11-20 00:00:00')
        ->and($user::query()->wherePreference('day', '2026-11-20')->pluck('id')->all())->toBe([
            $user->id,
        ])
        ->and(
            $user::query()
                ->wherePreference('seen', $user->preferences()->get('seen'))
                ->pluck('id')
                ->all(),
        )->toBe(
            [$user->id],
        );
});

it('counts defaults by their stored form or read form', function (): void {
    $model = new class () extends TableUser {
        public function preferencesConfig(): PreferencesConfig
        {
            return parent::preferencesConfig()
                ->withCasts([
                    'allowance' => AsCents::class,
                    'budget' => Cents::class,
                    'channels' => AsCollection::class,
                    'ratio' => 'decimal:2',
                ])
                ->withDefaults([
                    'allowance' => 300,
                    'budget' => 200,
                    'channels' => collect(['email']),
                    'ratio' => 0,
                ]);
        }
    };
    $stored = $model->newQuery()->create();

    $stored->preferences()->set('budget', 2);

    $default = $model->newQuery()->create();
    $large = $model->newQuery()->create();

    $large->preferences()->set('budget', 200);

    expect(
        $model->newQuery()->wherePreference('budget', 2)->orderBy('id')->pluck('id')->all(),
    )->toBe(
        [$stored->id, $default->id],
    )
        ->and($model->newQuery()->wherePreference('budget', 200)->pluck('id')->all())->toBe([
            $large->id,
        ])
        ->and($model->newQuery()->wherePreference('allowance', 300)->count())->toBe(0)
        ->and($model->newQuery()->wherePreference('channels', ['email'])->count())->toBe(3)
        ->and(
            $model->newQuery()->wherePreference('ratio', 0)->orderBy('id')->pluck('id')->all(),
        )->toBe(
            [$stored->id, $default->id, $large->id],
        );
});

it('filters models of a query that aliases their table', function (string $model): void {
    $daily = $model::create();

    $daily->preferences()->set('digest', 'daily');

    $model::create();

    expect(
        $model::query()->from('users as u')->wherePreference('digest', 'daily')->pluck('id')->all(),
    )->toBe(
        [$daily->id],
    )
        ->and(
            $model::query()->from('users AS u')->wherePreference('digest', 'weekly')->count(),
        )->toBe(
            1,
        );
})->with('models');

it('filters models of a table set at runtime by the preferences of that table', function (): void {
    $sql = new TableUser()
        ->setTable('archived_users')
        ->newQuery()
        ->wherePreference('digest', 'daily')
        ->toSql();

    expect($sql)->toContain('archived_user_preferences');
});

it('filters models of a query whose alias repeats the preferences table name', function (): void {
    $daily = TableUser::create();

    $daily->preferences()->set('digest', 'daily');

    TableUser::create();

    expect(
        TableUser::query()
            ->from('users as user_preferences')
            ->wherePreference('digest', 'daily')
            ->pluck('id')
            ->all(),
    )->toBe(
        [$daily->id],
    );
});

it('filters models through a relation to the same model', function (): void {
    $model = new class () extends TableUser {
        public function selves(): HasMany
        {
            return $this->hasMany(TableUser::class, 'id');
        }
    };
    $daily = TableUser::create();

    $daily->preferences()->set('digest', 'daily');

    TableUser::create();

    $ids = fn (): array => $model->newQuery()
        ->whereHas('selves', fn (Builder $query) => $query->wherePreference('digest', 'daily'))
        ->pluck('id')
        ->all();

    expect($ids())->toBe([$daily->id]);

    Preferences::fake();

    $daily->preferences()->set('digest', 'daily');

    expect($ids())->toBe([$daily->id]);
});
