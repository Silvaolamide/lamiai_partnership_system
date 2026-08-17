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
        Schema::create('payouts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('partner_id')
                ->constrained('program_partners')
                ->cascadeOnDelete();

            $table->foreignId('program_id')
                ->constrained('partnership_programs')
                ->cascadeOnDelete();

            $table->decimal('amount', 15, 2);

            $table->string('currency', 3)->default('NGN');

            $table->string('method')->nullable();

            $table->enum('status', [
                'requested',
                'approved',
                'processing',
                'processed',
                'paid',
                'rejected',
                'failed',
                'cancelled',
            ])->default('requested');

            $table->string('reference')->nullable()->unique();

            $table->text('notes')->nullable();

            $table->timestamp('requested_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('processed_at')->nullable();

            $table->timestamps();

            $table->index([
                'partner_id',
                'status',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payouts');
    }
};
