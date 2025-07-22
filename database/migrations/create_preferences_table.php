<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(Config::string('model-preferences.table'));
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(Config::string('model-preferences.table'), function (Blueprint $table) {
            $table->id();

            $table->morphs('preferable');

            $table->string('key');
            $table->json('value')->nullable();

            $table->timestamps();

            $table->index('key');

            $table->unique(['preferable_type', 'preferable_id', 'key']);
        });
    }
};
