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
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('program_id')
                ->constrained('partnership_programs')
                ->cascadeOnDelete();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->foreignId('partner_id')
                ->constrained('program_partners')
                ->cascadeOnDelete();

            $table->foreignId('source_partner_id')
                ->nullable()
                ->constrained('program_partners')
                ->nullOnDelete();

            $table->foreignId('rule_id')
                ->constrained('commission_rules')
                ->restrictOnDelete();

            $table->unsignedInteger('level');

            $table->enum('commission_type', [
                'percentage',
                'fixed',
            ]);

            $table->decimal('rate', 15, 4);

            $table->decimal('base_amount', 15, 2);

            $table->decimal('commission_amount', 15, 2);

            // Keep the complete current status lifecycle in the base schema.
            // This is important because RefreshDatabase builds the SQLite
            // test database from the full migration history.
            $table->enum('status', [
                'pending',
                'available',
                'approved',
                'payable',
                'paid',
                'reversed',
                'cancelled',
            ])->default('pending');

            $table->timestamp('available_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('reversed_at')->nullable();

            $table->timestamps();

            $table->index([
                'partner_id',
                'status',
            ]);

            $table->index([
                'order_id',
                'partner_id',
            ]);

            $table->unique([
                'order_id',
                'partner_id',
                'rule_id',
                'level',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};
