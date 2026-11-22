<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use HosmelQ\ModelPreferences\Events\PreferenceDeleted;
use HosmelQ\ModelPreferences\Events\PreferenceUpdated;
use HosmelQ\ModelPreferences\Exceptions\PreferenceValidationException;
use HosmelQ\ModelPreferences\Support\PreferencesConfig;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Workbench\App\Casts\Cents;
use Workbench\App\Enums\PreferenceKey;
use Workbench\App\Enums\Theme;
use Workbench\App\Enums\UnitPreferenceKey;
use Workbench\App\Models\User;

it('persists preferences', function (string $model): void {
    $user = $model::create();

    $user->preferences()->setMultiple([
        '42' => 'answer',
        'muted' => true,
        'notifications' => ['email' => true],
        'volume' => 7,
    ]);
    $user->preferences()->set('locale', null);

    expect($model::findOrFail($user->id)->preferences())
        ->get('42')->toBe('answer')
        ->get('muted')->toBeTrue()
        ->get('notifications')->toBe(['email' => true])
        ->get('volume')->toBe(7)
        ->get('locale', 'en')->toBeNull()
        ->has('locale')->toBeTrue()
        ->missing('timezone')->toBeTrue();
})->with('models');

it('deletes preferences', function (string $model): void {
    $user = $model::create();

    $user->preferences()->setMultiple(['digest' => 'daily', 'locale' => 'es', 'volume' => 7]);
    $user->preferences()->delete('digest');
    $user->preferences()->deleteMultiple(['locale']);

    expect($model::findOrFail($user->id)->preferences()->all())
        ->toBe(['digest' => 'weekly', 'theme' => Theme::System, 'volume' => 7]);

    $user->preferences()->clear();

    expect($model::findOrFail($user->id)->preferences()->has('volume'))->toBeFalse();
})->with('models');

it('refuses to change the preferences of unsaved models and models without their key', function (
    string $model,
): void {
    $preferences = new $model()->preferences();

    $model::create();

    $keyless = $model::query()->select('updated_at')->firstOrFail()->preferences();

    expect($preferences->get('digest'))->toBe('weekly')
        ->and(fn () => $preferences->set('digest', 'daily'))->toThrow(
            LogicException::class,
            'unsaved',
        )
        ->and(fn () => $preferences->clear())->toThrow(LogicException::class, 'unsaved')
        ->and(fn () => $keyless->set('digest', 'daily'))->toThrow(
            LogicException::class,
            'without a key',
        );
})->with('models');

it('falls back to the given default and then to the configured one', function (): void {
    $preferences = User::create()->preferences();

    expect($preferences)
        ->get('digest')->toBe('weekly')
        ->get('digest', 'daily')->toBe('daily')
        ->get('digest', fn (): null => null)->toBeNull()
        ->get('locale')->toBeNull()
        ->getMultiple(['digest', 'locale'])->toBe(['digest' => 'weekly', 'locale' => null])
        ->and(json_encode($preferences))->toBe('{"digest":"weekly","theme":"system"}');
});

it('casts preferences when storing and reading them', function (): void {
    config(['app.key' => 'base64:'.base64_encode(random_bytes(32))]);

    $user = new class () extends User {
        public function preferencesConfig(): PreferencesConfig
        {
            return parent::preferencesConfig()->withCasts([
                'budget' => Cents::class,
                'snoozed_until' => 'immutable_datetime',
                'theme' => Theme::class,
                'token' => 'encrypted',
            ]);
        }
    };

    $user->save();
    $user->preferences()->setMultiple([
        'budget' => 2,
        'snoozed_until' => CarbonImmutable::parse('2026-11-20 10:30:00'),
        'theme' => 'dark',
        'token' => 'secret',
    ]);

    $preferences = $user::findOrFail($user->id)->preferences();

    expect(DB::table('users')->where('id', $user->id)->value('preferences'))->not->toContain(
        'secret',
    )
        ->and($preferences->get('budget'))->toBe(200)
        ->and($preferences->get('theme'))->toBe(Theme::Dark)
        ->and($preferences->get('token'))->toBe('secret')
        ->and($preferences->get('snoozed_until'))
        ->toBeInstanceOf(CarbonImmutable::class)
        ->toISOString()->toBe('2026-11-20T10:30:00.000000Z');
});

it('validates dates, enums, and binary strings', function (): void {
    config(['app.key' => 'base64:'.base64_encode(random_bytes(32))]);

    $user = new class () extends User {
        public function preferencesConfig(): PreferencesConfig
        {
            return parent::preferencesConfig()
                ->withCasts([
                    'due' => 'immutable_datetime',
                    'key' => UnitPreferenceKey::class,
                    'keys' => AsEnumCollection::of(UnitPreferenceKey::class),
                    'secret' => 'encrypted',
                ])
                ->withRules(['due' => ['date'], 'mode' => ['in:dark,light']]);
        }
    };

    $user->save();
    $user->preferences()->setMultiple([
        'due' => date_create_immutable('2026-11-20 10:30:00'),
        'key' => UnitPreferenceKey::Locale,
        'keys' => collect([UnitPreferenceKey::Locale]),
        'mode' => Theme::Dark,
        'secret' => $secret = random_bytes(16),
    ]);

    expect($user::findOrFail($user->id)->preferences())
        ->get('due')->toDateTimeString()->toBe('2026-11-20 10:30:00')
        ->get('key')->toBe(UnitPreferenceKey::Locale)
        ->get('keys')->all()->toBe([UnitPreferenceKey::Locale])
        ->get('mode')->toBe('dark')
        ->get('secret')->toBe($secret);
});

it('serializes preferences as Eloquent serializes cast attributes', function (): void {
    $user = new class () extends User {
        public function preferencesConfig(): PreferencesConfig
        {
            return parent::preferencesConfig()
                ->withCasts([
                    'budget' => Cents::class,
                    'key' => UnitPreferenceKey::class,
                    'theme' => Theme::class,
                ])
                ->withDefaults(['budget' => 200, 'digest' => 'weekly', 'theme' => Theme::System]);
        }
    };

    $user->save();
    $user->preferences()->set('key', UnitPreferenceKey::Locale);

    expect(
        json_decode(
            json_encode($user->preferences(), JSON_THROW_ON_ERROR),
            true,
            512,
            JSON_THROW_ON_ERROR,
        ),
    )
        ->toMatchArray([
            'budget' => 200,
            'digest' => 'weekly',
            'key' => 'Locale',
            'theme' => 'system',
        ]);
});

it('stores primitive casts as their cast value', function (): void {
    $user = new class () extends User {
        public function preferencesConfig(): PreferencesConfig
        {
            return parent::preferencesConfig()->withCasts([
                'enabled' => 'boolean',
                'price' => 'decimal:2',
            ]);
        }
    };

    $user->save();
    $user->preferences()->setMultiple(['enabled' => '1', 'price' => 1]);

    expect($user->preferences())
        ->get('enabled')->toBeTrue()
        ->get('price')->toBe('1.00')
        ->and($user::query()->wherePreference('enabled', true)->pluck('id')->all())->toBe([
            $user->id,
        ])
        ->and($user::query()->wherePreference('enabled', '1')->pluck('id')->all())->toBe([
            $user->id,
        ])
        ->and($user::query()->wherePreference('price', '1.00')->pluck('id')->all())->toBe([
            $user->id,
        ]);
});

it('only casts the requested preferences', function (): void {
    $user = User::create();

    DB::table('users')
        ->where('id', $user->id)
        ->update(['preferences' => json_encode(['locale' => 'es', 'theme' => 'sepia'])]);

    expect(User::findOrFail($user->id)->preferences())
        ->get('locale')->toBe('es')
        ->getMultiple(['locale'])->toBe(['locale' => 'es']);
});

it('accepts enum keys', function (): void {
    $preferences = User::create()->preferences();

    $preferences->set(PreferenceKey::Digest, 'daily');
    $preferences->set(UnitPreferenceKey::Locale, 'es');

    expect($preferences->getMultiple([PreferenceKey::Digest, UnitPreferenceKey::Locale]))
        ->toBe(['digest' => 'daily', 'Locale' => 'es']);
});

it('validates preferences against the rules that apply to them', function (): void {
    $preferences = User::create()->preferences();

    expect(fn () => $preferences->setMultiple([
        'digest' => 'hourly',
        'notifications' => ['email' => 'yes'],
        'theme' => 'sepia',
    ]))->toThrow(function (PreferenceValidationException $exception): void {
        expect($exception->errors())->toHaveKeys(['digest', 'notifications.email', 'theme']);
    })
        ->and($preferences->has('notifications'))->toBeFalse();
});

it('validates preferences together, so rules may compare them', function (): void {
    $user = new class () extends User {
        public function preferencesConfig(): PreferencesConfig
        {
            return parent::preferencesConfig()->withRules([
                'pin' => ['digits:4'],
                'pin_confirmation' => ['same:pin'],
            ]);
        }
    };

    $user->save();
    $user->preferences()->setMultiple(['pin' => '1234', 'pin_confirmation' => '1234']);

    expect(
        fn () => $user->preferences()->setMultiple(['pin' => '1234', 'pin_confirmation' => '4321']),
    )->toThrow(PreferenceValidationException::class, 'pin confirmation');
});

it('rejects undeclared preferences in strict mode', function (): void {
    $user = new class () extends User {
        public function preferencesConfig(): PreferencesConfig
        {
            return parent::preferencesConfig()->strict();
        }
    };

    $user->save();
    $user->preferences()->setMultiple(['digest' => 'daily', 'notifications' => ['email' => true]]);

    expect(fn () => $user->preferences()->set('thme', 'dark'))
        ->toThrow(PreferenceValidationException::class, 'thme')
        ->and($user->preferences()->has('thme'))->toBeFalse();
});

it('validates dotted and wildcard rules', function (): void {
    $user = new class () extends User {
        public function preferencesConfig(): PreferencesConfig
        {
            return parent::preferencesConfig()->withRules([
                '42' => ['integer'],
                'labels.*' => ['distinct', 'max:6'],
                'labels.work' => ['min:2'],
                'mail.confirmation' => ['same:mail.pin'],
            ]);
        }
    };

    $user->save();

    expect(fn () => $user->preferences()->set('labels.work', 'o'))->toThrow(
        PreferenceValidationException::class,
    )
        ->and(fn () => $user->preferences()->set('labels.work', 'kitchen'))->toThrow(
            PreferenceValidationException::class,
        )
        ->and(
            fn ()
                => $user->preferences()->setMultiple([
                    'labels.east' => 'home',
                    'labels.west' => 'home',
                ]),
        )->toThrow(
            PreferenceValidationException::class,
        )
        ->and(fn () => $user->preferences()->set('42', 'x'))->toThrow(
            PreferenceValidationException::class,
        );

    $user->preferences()->setMultiple([
        '42' => 7,
        'mail.confirmation' => '1234',
        'mail.pin' => '1234',
    ]);

    expect($user->preferences()->get('mail.confirmation'))->toBe('1234');
});

it('rejects overlapping keys in a single setMultiple call', function (): void {
    $preferences = User::create()->preferences();

    expect(fn () => $preferences->setMultiple([
        'notifications' => ['email' => 'yes'],
        'notifications.email' => true,
    ]))->toThrow(InvalidArgumentException::class, 'overlapping')
        ->and($preferences->has('notifications'))->toBeFalse();
});

it('dispatches events for stored and deleted preferences', function (string $model): void {
    Event::fake([PreferenceDeleted::class, PreferenceUpdated::class]);

    $user = $model::create();

    $user->preferences()->setMultiple(['digest' => 'daily', 'volume' => 7]);
    $user->preferences()->delete('digest');

    $model::findOrFail($user->id)->preferences()->set('mode', 'compact');

    $user->preferences()->clear();

    Event::assertDispatched(
        PreferenceUpdated::class,
        fn (PreferenceUpdated $event): bool
            => $event->model->is($user) && $event->values === ['digest' => 'daily', 'volume' => 7],
    );
    Event::assertDispatched(
        PreferenceDeleted::class,
        fn (PreferenceDeleted $event): bool => $event->keys === ['digest'],
    );
    Event::assertDispatched(
        PreferenceDeleted::class,
        fn (PreferenceDeleted $event): bool
            => array_values(Arr::sort($event->keys)) === ['mode', 'volume'],
    );
})->with('models');
