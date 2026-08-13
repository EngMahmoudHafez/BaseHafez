<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table): void {
            $table->id();
            $table->morphs('notifiable');
            $table->string('title_ar');
            $table->string('title_en')->nullable();
            $table->text('body_ar');
            $table->text('body_en')->nullable();
            $table->string('type', 50)->default('general')->index();
            $table->json('data')->nullable();
            $table->string('action_url', 500)->nullable();
            $table->string('icon', 100)->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['notifiable_type', 'notifiable_id', 'is_read'], 'notifications_notifiable_read_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
