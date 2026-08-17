<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::create('clicks', function (Blueprint $table) {
        $table->id();

        $table->foreignId('program_id')
            ->constrained('partnership_programs')
            ->cascadeOnDelete();

        $table->foreignId('partner_id')
            ->constrained('program_partners')
            ->cascadeOnDelete();

        $table->string('campaign_id')->nullable();

        $table->string('referral_code');

        $table->string('ip_hash')->nullable();

        $table->text('user_agent')->nullable();

        $table->text('landing_url')->nullable();

        $table->timestamps();

        $table->index([
            'program_id',
            'partner_id',
        ]);

        $table->index('referral_code');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clicks');
    }
};
