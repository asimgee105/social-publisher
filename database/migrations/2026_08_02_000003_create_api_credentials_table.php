<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_credentials', function (Blueprint $table) {
            $table->id();
            $table->string('platform')->unique(); // meta, tiktok, google, linkedin
            $table->string('client_id')->nullable();
            $table->text('client_secret')->nullable(); // encrypted cast
            $table->string('redirect_uri')->nullable();
            $table->json('scopes')->nullable();
            $table->json('extra_config')->nullable(); // e.g. page_id, instagram_account_id, api_version
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_credentials');
    }
};
