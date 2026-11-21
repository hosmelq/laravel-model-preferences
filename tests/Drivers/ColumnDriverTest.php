<?php

declare(strict_types=1);

use function Pest\Laravel\expectsDatabaseQueryCount;
use function Pest\Laravel\travel;

use HosmelQ\ModelPreferences\Support\PreferencesConfig;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\AsEncryptedArrayObject;
use Illuminate\Database\Eloquent\MissingAttributeException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Workbench\App\Enums\Theme;
use Workbench\App\Models\User;

it('reads and writes models selected without the preferences column', function (): void {
    $user = User::create();

    $user->preferences()->setMultiple(['digest' => 'daily', 'volume' => 7]);

    User::query()->select('id')->findOrFail($user->id)->preferences()->set('locale', 'es');

    $partial = User::query()->select('id')->findOrFail($user->id);

    $partial->preferences()->set('mode', Theme::Dark);

    expect(User::query()->select('id')->findOrFail($user->id)->preferences()->all())
        ->toMatchArray(['digest' => 'daily', 'locale' => 'es', 'mode' => 'dark', 'volume' => 7])
        ->and($partial->preferences()->get('mode'))->toBe('dark');
});

it('reads the preferences column of models selected without their key, but not of models without both', function (): void {
    User::create()->preferences()->set('locale', 'es');

    $selected = User::query()->select('preferences')->firstOrFail();
    $unselected = User::query()->select('updated_at')->firstOrFail();

    expectsDatabaseQueryCount(0);

    expect($selected->preferences()->get('locale'))->toBe('es')
        ->and(fn () => $unselected->preferences()->get('locale'))->toThrow(
            MissingAttributeException::class,
        );
});

it('refuses to write preferences while the preferences column has unsaved changes', function (): void {
    $user = User::create();
    $user->preferences = ['locale' => 'es'];
    $partial = User::query()->select('id')->findOrFail($user->id);
    $partial->preferences = [];
    $cleared = User::create();

    $cleared->preferences()->clear();

    $cleared->preferences = null;

    expect(fn () => $user->preferences()->set('volume', 7))->toThrow(
        LogicException::class,
        'Save the changes',
    )
        ->and(fn () => $partial->preferences()->set('volume', 7))->toThrow(
            LogicException::class,
            'Save the changes',
        )
        ->and(fn () => $cleared->preferences()->set('volume', 7))->toThrow(
            LogicException::class,
            'Save the changes',
        )
        ->and(User::findOrFail($user->id)->preferences()->all())->not->toHaveKey('volume');
});

it('keeps preferences written through other instances', function (): void {
    $user = User::create();
    $first = User::findOrFail($user->id);
    $second = User::findOrFail($user->id);

    $first->preferences()->set('digest', 'daily');

    $second->preferences()->set('locale', 'es');

    expect(User::findOrFail($user->id)->preferences()->all())
        ->toMatchArray(['digest' => 'daily', 'locale' => 'es']);
});

it('stores preferences through the cast declared by the model', function (): void {
    config(['app.key' => 'base64:'.base64_encode(random_bytes(32))]);

    $user = new class () extends User {
        public function preferencesConfig(): PreferencesConfig
        {
            return parent::preferencesConfig()->withColumn('encrypted_preferences');
        }

        protected function casts(): array
        {
            return ['encrypted_preferences' => 'encrypted:array'];
        }
    };

    $user->save();
    $user->preferences()->set('digest', 'daily');

    expect(
        DB::table('users')->where('id', $user->id)->value('encrypted_preferences'),
    )->not->toContain(
        'daily',
    )
        ->and($user::findOrFail($user->id)->preferences()->get('digest'))->toBe('daily');
});

it('writes preferences read through an encrypted cast while previous keys are set', function (): void {
    config([
        'app.key' => 'base64:'.base64_encode(random_bytes(32)),
        'app.previous_keys' => ['base64:'.base64_encode(random_bytes(32))],
    ]);

    $user = new class () extends User {
        public function preferencesConfig(): PreferencesConfig
        {
            return parent::preferencesConfig()->withColumn('encrypted_preferences');
        }

        protected function casts(): array
        {
            return ['encrypted_preferences' => AsEncryptedArrayObject::class];
        }
    };

    $user->save();
    $user->preferences()->set('digest', 'daily');

    expect($user->preferences()->get('digest'))->toBe('daily')->and($user->isDirty())->toBeFalse();

    $user->preferences()->set('locale', 'es');

    expect($user::findOrFail($user->id)->preferences()->all())->toMatchArray([
        'digest' => 'daily',
        'locale' => 'es',
    ]);
});

it('fails a write rather than overwrite preferences stored concurrently on another connection', function (): void {
    $database = tempnam(sys_get_temp_dir(), 'preferences');

    foreach (['first', 'second'] as $connection) {
        config([
            "database.connections.{$connection}" => [
                'database' => $database,
                'driver' => 'sqlite',
                'journal_mode' => 'wal',
            ],
        ]);
    }

    $this->beforeApplicationDestroyed(
        fn (): bool => File::delete([$database, $database.'-shm', $database.'-wal']),
    );

    Artisan::call('migrate', ['--database' => 'first']);

    $user = User::on('first')->create();
    $other = User::on('second')->findOrFail($user->id);
    $overlapped = false;

    DB::connection('first')->beforeExecuting(
        function (string $query) use ($other, &$overlapped): void {
            if (! $overlapped && str_starts_with($query, 'update')) {
                $overlapped = true;

                $other->preferences()->set('locale', 'es');
            }
        },
    );

    expect(fn () => $user->preferences()->set('digest', 'daily'))->toThrow(
        QueryException::class,
        'database is locked',
    );

    $user->preferences()->set('digest', 'daily');

    expect(User::on('second')->findOrFail($user->id)->preferences()->all())->toMatchArray([
        'digest' => 'daily',
        'locale' => 'es',
    ]);
});

it('keeps unsaved changes to other attributes when writing preferences', function (): void {
    config(['app.key' => 'base64:'.base64_encode(random_bytes(32))]);

    $user = new class () extends User {
        protected function casts(): array
        {
            return [...parent::casts(), 'encrypted_preferences' => AsEncryptedArrayObject::class];
        }
    };

    $user->setAttribute('encrypted_preferences', ['theme' => 'dark']);
    $user->save();

    $loaded = $user::findOrFail($user->id);
    $loaded->getAttribute('encrypted_preferences')['theme'] = 'light';

    $loaded->preferences()->set('digest', 'daily');
    $loaded->save();

    expect($user::findOrFail($user->id)->getAttribute('encrypted_preferences')['theme'])->toBe(
        'light',
    );
});

it('supports casts that return objects', function (): void {
    $user = new class () extends User {
        protected function casts(): array
        {
            return ['preferences' => AsArrayObject::class];
        }
    };

    $user->save();

    DB::table('users')->where('id', $user->id)->update(['preferences' => '{"digest": "daily"}']);

    $loaded = $user::findOrFail($user->id);

    $loaded->getAttribute('preferences');
    $loaded->preferences()->set('locale', 'es');

    expect($user::findOrFail($user->id)->preferences()->all())->toMatchArray([
        'digest' => 'daily',
        'locale' => 'es',
    ]);
});

it('writes preferences of models hidden by global scopes', function (): void {
    $user = new class () extends User {
        use SoftDeletes;
    };

    $user->save();
    $user->delete();
    $user->preferences()->set('digest', 'daily');

    expect($user::withTrashed()->findOrFail($user->id)->preferences()->get('digest'))->toBe(
        'daily',
    );
});

it('fails to write preferences of models whose row was deleted', function (): void {
    $user = User::create();

    DB::table('users')->delete();

    $user->preferences()->set('digest', 'daily');
})->throws(ModelNotFoundException::class);

it('touches the model when its preferences change', function (): void {
    $user = User::create();

    travel(1)->day();

    $user->preferences()->set('digest', 'daily');

    expect(User::findOrFail($user->id)->updated_at->isToday())->toBeTrue()
        ->and($user->isDirty())->toBeFalse();
});

it('keeps an unsaved updated_at timestamp for save()', function (): void {
    $imported = now()->subYear()->startOfSecond();
    $user = User::create();
    $user->updated_at = $imported;

    $user->preferences()->set('digest', 'daily');
    $user->save();

    expect(User::findOrFail($user->id)->updated_at->toDateTimeString())->toBe(
        $imported->toDateTimeString(),
    );
});

it('loads the preferences column of many models with one query', function (): void {
    User::create()->preferences()->set('volume', 1);
    User::create()->preferences()->set('volume', 2);

    $users = User::query()->select('id')->orderBy('id')->get();

    expectsDatabaseQueryCount(1);

    $users->loadPreferences();

    expect($users->map(fn (User $user): mixed => $user->preferences()->get('volume'))->all())->toBe(
        [1, 2],
    );
});
