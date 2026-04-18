<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('instagram-digest.tables.profiles'), function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('instagram_username')->unique();
            $table->string('instagram_id')->nullable();
            $table->string('full_name')->nullable();
            $table->text('biography')->nullable();
            $table->unsignedInteger('followers_count')->default(0);
            $table->unsignedInteger('following_count')->nullable();
            $table->string('profile_pic_url')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->string('external_url')->nullable();
            $table->string('status')->default('pending')->index();
            $table->string('source_hashtag')->nullable()->index();
            $table->timestamp('reviewed_at')->nullable();
            $table->string('telegram_message_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('instagram-digest.tables.profiles'));
    }
};
