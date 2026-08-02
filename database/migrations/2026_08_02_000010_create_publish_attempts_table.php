<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publish_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_platform_id')->constrained()->onDelete('cascade');
            $table->string('attempt_type')->default('publish'); // publish, retry, dry_run
            $table->integer('status_code')->nullable();
            $table->json('response_payload')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('attempted_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publish_attempts');
    }
};
