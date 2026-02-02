<?php

declare(strict_types=1);

use function HosmelQ\ModelPreferences\enum_value;

use HosmelQ\ModelPreferences\Tests\TestSupport\Enums\UserPreference;

it('converts enum values to scalar equivalents', function (): void {
    expect(enum_value(UserPreference::Theme))->toBe('theme');
});

it('falls back to the provided default when value is null', function (): void {
    $result = enum_value(null, fn (): string => 'default');

    expect($result)->toBe('default');
});
