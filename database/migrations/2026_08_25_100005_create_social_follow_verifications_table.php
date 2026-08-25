<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('social_follow_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->constrained('social_follow_participants')->cascadeOnDelete();
            $table->foreignId('social_account_id')->constrained('social_accounts')->cascadeOnDelete();
            $table->string('status', 30)->default('claimed');
            $table->string('verification_method', 50)->default('user_confirmation');
            $table->timestamp('verified_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['participant_id', 'social_account_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('social_follow_verifications'); }
};
