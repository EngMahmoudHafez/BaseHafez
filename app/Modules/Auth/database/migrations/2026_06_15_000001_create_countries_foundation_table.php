<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100)->nullable();
            $table->char('iso2', 2)->nullable();
            $table->string('dial_code', 10)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique('iso2');
            $table->unique('dial_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
