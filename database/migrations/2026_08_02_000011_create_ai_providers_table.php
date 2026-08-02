<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_providers', function (Blueprint $table) {
            $table->id();
            $table->string('provider_key')->unique(); // gemini, openai
            $table->string('name');
            $table->text('api_key')->nullable(); // encrypted cast
            $table->string('model_name')->default('gemini-2.0-flash');
            $table->float('temperature')->default(0.7);
            $table->integer('max_tokens')->default(1000);
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_providers');
    }
};
