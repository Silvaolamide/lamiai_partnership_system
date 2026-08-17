<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL uses an ENUM for commission status, so update the ENUM there.
        // SQLite does not support MySQL's MODIFY syntax. This migration is
        // also executed by RefreshDatabase in the test suite, so keep the
        // migration portable across both databases.
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE commissions MODIFY status ENUM('pending','available','approved','payable','paid','reversed','cancelled') NOT NULL DEFAULT 'pending'");
        }

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

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE commissions MODIFY status ENUM('pending','available','paid','reversed','cancelled') NOT NULL DEFAULT 'pending'");
        }
    }
};
