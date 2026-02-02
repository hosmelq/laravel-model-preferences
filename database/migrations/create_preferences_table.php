<?php

declare(strict_types=1);

use HosmelQ\ModelPreferences\Support\Config as PreferencesConfig;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(PreferencesConfig::storesSharedTable());
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(PreferencesConfig::storesSharedTable(), function (Blueprint $table) {
            $table->id();

            $table->morphs('preferable');

            $table->string('key')->index();
            $table->json('value')->nullable();

            $table->timestamps();

            $table->unique(['preferable_type', 'preferable_id', 'key']);
        });
    }
};
