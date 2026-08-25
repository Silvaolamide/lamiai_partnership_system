<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_follow_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('social_follow_campaigns')->cascadeOnDelete();
            $table->string('session_token')->unique();
            $table->unsignedInteger('score')->default(0);
            $table->string('status')->default('in_progress');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_follow_participants');
    }
};
