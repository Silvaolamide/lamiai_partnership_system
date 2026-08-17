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
        Schema::create('program_partners', function (Blueprint $table) {
            $table->id();

            $table->foreignId('program_id')
                ->constrained('partnership_programs')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('partner_code');

            $table->enum('status', [
                'pending',
                'active',
                'suspended',
                'rejected',
                'inactive',
            ])->default('pending');

            $table->foreignId('parent_partner_id')
                ->nullable()
                ->constrained('program_partners')
                ->nullOnDelete();

            $table->timestamp('joined_at')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();

            $table->unique([
                'program_id',
                'user_id',
            ]);

            $table->unique([
                'program_id',
                'partner_code',
            ]);

            $table->index([
                'program_id',
                'status',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_partners');
    }
};
