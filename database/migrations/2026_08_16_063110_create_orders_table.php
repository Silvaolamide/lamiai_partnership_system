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
    Schema::create('orders', function (Blueprint $table) {
        $table->id();

        $table->string('order_number')->unique();

        $table->foreignId('customer_id')
            ->nullable()
            ->constrained('users')
            ->nullOnDelete();

        $table->foreignId('program_id')
            ->nullable()
            ->constrained('partnership_programs')
            ->nullOnDelete();

        $table->foreignId('partner_id')
            ->nullable()
            ->constrained('program_partners')
            ->nullOnDelete();

        $table->decimal('subtotal', 15, 2);
        $table->decimal('discount', 15, 2)->default(0);
        $table->decimal('total', 15, 2);

        $table->string('currency', 3)->default('NGN');

        $table->enum('status', [
            'pending',
            'paid',
            'failed',
            'cancelled',
            'refunded',
            'partially_refunded',
        ])->default('pending');

        $table->string('payment_provider')->nullable();

        $table->string('payment_reference')
            ->nullable()
            ->unique();

        $table->timestamp('paid_at')->nullable();
        $table->timestamp('refunded_at')->nullable();

        $table->timestamps();

        $table->index([
            'program_id',
            'status',
        ]);

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
        Schema::dropIfExists('orders');
    }
};
