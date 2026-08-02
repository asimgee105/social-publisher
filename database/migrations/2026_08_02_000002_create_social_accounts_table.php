<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('platform'); // instagram, facebook, tiktok, youtube, linkedin
            $table->string('platform_user_id')->nullable();
            $table->string('account_name');
            $table->string('avatar_url')->nullable();
            $table->text('access_token'); // encrypted cast
            $table->text('refresh_token')->nullable(); // encrypted cast
            $table->timestamp('token_expires_at')->nullable();
            $table->string('status')->default('active'); // active, needs_reauth, expired, disconnected
            $table->boolean('is_default')->default(false);
            $table->json('scopes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_accounts');
    }
};
