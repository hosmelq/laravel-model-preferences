<?php

declare(strict_types=1);

use HosmelQ\ModelPreferences\Support\Config;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function down(): void
    {
        Schema::dropIfExists(Config::storesSharedTable());
    }

    public function getConnection(): null|string
    {
        return Config::storesSharedConnection();
    }

    public function up(): void
    {
        Schema::create(Config::storesSharedTable(), function (Blueprint $table): void {
            $table->id();
            $table->string('preferable_type');

            match (Builder::$defaultMorphKeyType) {
                'uuid' => $table->uuid('preferable_id'),
                'ulid' => $table->ulid('preferable_id'),
                default => $table->unsignedBigInteger('preferable_id'),
            };

            $table->string('key');
            $table->json('value');

            $table->unique(['preferable_type', 'preferable_id', 'key']);
        });
    }
};
