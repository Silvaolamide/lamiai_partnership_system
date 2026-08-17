<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL enum must explicitly include the states already used by the
        // commission admin workflow.
        DB::statement("ALTER TABLE commissions MODIFY status ENUM('pending','available','approved','payable','paid','reversed','cancelled') NOT NULL DEFAULT 'pending'");

        Schema::create('payout_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payout_id')->constrained('payouts')->cascadeOnDelete();
            $table->foreignId('commission_id')->constrained('commissions')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['payout_id', 'commission_id']);
            $table->unique('commission_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payout_commissions');
        DB::statement("ALTER TABLE commissions MODIFY status ENUM('pending','available','paid','reversed','cancelled') NOT NULL DEFAULT 'pending'");
    }
};
