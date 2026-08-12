<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('auth_otps', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('country_id')
                ->nullable()
                ->constrained('countries')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->string('token', 64)->unique();
            $table->string('identifier', 180);
            $table->string('code_hash', 255);
            $table->string('channel', 30)->default('phone');
            $table->string('purpose', 50)->default('login');
            $table->dateTime('expires_at');
            $table->dateTime('verified_at')->nullable();
            $table->integer('attempts')->default(0);
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_otps');
    }
};
