<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduled_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->onDelete('cascade');
            $table->foreignId('post_platform_id')->constrained()->onDelete('cascade');
            $table->timestamp('scheduled_time_utc');
            $table->string('timezone')->default('Asia/Karachi');
            $table->string('status')->default('pending'); // pending, processing, completed, failed, cancelled
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_posts');
    }
};
