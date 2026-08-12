<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->string('name', 180);
            $table->string('phone', 30)->nullable();
            $table->string('whatsapp', 30);
            $table->string('email', 180)->nullable();
            $table->string('password', 255)->nullable();
            $table->string('avatar', 255)->nullable();
            $table->string('status', 30)->default('active');
            // Token generation embedded in every JWT as the `tv` claim; bumping it
            // (on password change/reset) revokes all previously issued tokens.
            $table->unsignedInteger('token_version')->default(1);
            $table->dateTime('last_login_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->index('status');
            $table->index(['country_id', 'phone']);
            $table->unique(['country_id', 'whatsapp']);
            $table->unique('email');
            $table->foreign('country_id')->references('id')->on('countries')
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
