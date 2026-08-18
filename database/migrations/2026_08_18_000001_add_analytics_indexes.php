<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'orders_status_created_at_index');
            $table->index(['program_id', 'status', 'created_at'], 'orders_program_status_created_at_index');
            $table->index(['partner_id', 'status', 'created_at'], 'orders_partner_status_created_at_index');
        });

        Schema::table('commissions', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'commissions_status_created_at_index');
            $table->index(['program_id', 'status', 'created_at'], 'commissions_program_status_created_at_index');
            $table->index(['partner_id', 'status', 'created_at'], 'commissions_partner_status_created_at_index');
        });

        Schema::table('program_partners', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'program_partners_status_created_at_index');
            $table->index(['program_id', 'status'], 'program_partners_program_status_index');
        });

        Schema::table('partnership_programs', function (Blueprint $table) {
            $table->index(['owner_id', 'status'], 'partnership_programs_owner_status_index');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_status_created_at_index');
            $table->dropIndex('orders_program_status_created_at_index');
            $table->dropIndex('orders_partner_status_created_at_index');
        });
        Schema::table('commissions', function (Blueprint $table) {
            $table->dropIndex('commissions_status_created_at_index');
            $table->dropIndex('commissions_program_status_created_at_index');
            $table->dropIndex('commissions_partner_status_created_at_index');
        });
        Schema::table('program_partners', function (Blueprint $table) {
            $table->dropIndex('program_partners_status_created_at_index');
            $table->dropIndex('program_partners_program_status_index');
        });
        Schema::table('partnership_programs', function (Blueprint $table) {
            $table->dropIndex('partnership_programs_owner_status_index');
        });
    }
};
