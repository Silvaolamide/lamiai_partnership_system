<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('social_follow_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('social_follow_campaigns')->cascadeOnDelete();
            $table->string('session_token', 64)->unique();
            $table->unsignedInteger('score')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('social_follow_participants'); }
};
