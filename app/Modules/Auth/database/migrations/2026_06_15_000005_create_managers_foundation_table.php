<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('managers', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->string('name', 180);
            $table->string('email', 180);
            $table->string('phone', 30)->nullable();
            $table->string('whatsapp', 30)->nullable();
            $table->string('password', 255);
            $table->string('avatar', 255)->nullable();
            $table->string('status', 30)->default('active');
            $table->dateTime('last_login_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique('email');
            $table->index(['country_id', 'phone']);
            $table->index(['country_id', 'whatsapp']);
            $table->index('status');
            $table->foreign('country_id')->references('id')->on('countries')
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('managers');
    }
};
