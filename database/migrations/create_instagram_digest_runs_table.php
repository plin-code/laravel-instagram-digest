<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('instagram-digest.tables.runs'), function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('status')->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->json('hashtags_scanned')->nullable();
            $table->unsignedInteger('profiles_found')->default(0);
            $table->unsignedInteger('profiles_added')->default(0);
            $table->unsignedInteger('profiles_updated')->default(0);
            $table->text('error_message')->nullable();
            $table->longText('error_trace')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('instagram-digest.tables.runs'));
    }
};
