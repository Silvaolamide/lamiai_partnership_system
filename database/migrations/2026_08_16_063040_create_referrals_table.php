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
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('program_id')
                ->constrained('partnership_programs')
                ->cascadeOnDelete();

            $table->foreignId('parent_partner_id')
                ->constrained('program_partners')
                ->cascadeOnDelete();

            $table->foreignId('child_partner_id')
                ->constrained('program_partners')
                ->cascadeOnDelete();

            $table->enum('status', [
                'active',
                'inactive',
            ])->default('active');

            $table->timestamps();

            $table->unique([
                'program_id',
                'child_partner_id',
            ]);

            $table->index([
                'program_id',
                'parent_partner_id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
