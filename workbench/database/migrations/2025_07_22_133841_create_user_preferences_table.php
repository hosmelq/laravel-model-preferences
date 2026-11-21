<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Workbench\App\Models\User;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('user_preferences', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(User::class, 'model_id')->constrained()->cascadeOnDelete();

            $table->string('key');
            $table->json('value');

            $table->unique(['model_id', 'key']);
        });
    }
};
