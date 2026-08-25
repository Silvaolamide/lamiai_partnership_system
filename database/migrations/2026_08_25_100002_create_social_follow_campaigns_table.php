<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('social_follow_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('headline')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('minimum_score')->default(1);
            $table->string('resource_title')->nullable();
            $table->string('resource_url')->nullable();
            $table->string('resource_button_text')->default('Get Resource');
            $table->string('cover_image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['user_id', 'slug']);
        });
    }
    public function down(): void { Schema::dropIfExists('social_follow_campaigns'); }
};
