<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('NGN');
            $table->string('method')->nullable();
            $table->enum('status', [
                'requested', 'approved', 'processing', 'processed', 'paid',
                'rejected', 'failed', 'cancelled',
            ])->default('requested');
            $table->string('reference')->nullable()->unique();
            $table->text('notes')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['business_id', 'status']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('business_payout_id')
                ->nullable()
                ->after('program_id')
                ->constrained('business_payouts')
                ->nullOnDelete();

            $table->index(['business_payout_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['business_payout_id']);
            $table->dropIndex(['business_payout_id', 'status']);
            $table->dropColumn('business_payout_id');
        });

        Schema::dropIfExists('business_payouts');
    }
};
