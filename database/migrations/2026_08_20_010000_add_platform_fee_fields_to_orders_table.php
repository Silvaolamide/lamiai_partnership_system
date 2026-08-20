<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('orders', 'platform_fee_percent')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->decimal('platform_fee_percent', 8, 4)->nullable()->after('total');
            });
        }

        if (!Schema::hasColumn('orders', 'platform_fee_amount')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->decimal('platform_fee_amount', 15, 2)->nullable()->after('platform_fee_percent');
            });
        }

        if (!Schema::hasColumn('orders', 'business_net_amount')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->decimal('business_net_amount', 15, 2)->nullable()->after('platform_fee_amount');
            });
        }
    }

    public function down(): void
    {
        $columns = [];

        foreach (['business_net_amount', 'platform_fee_amount', 'platform_fee_percent'] as $column) {
            if (Schema::hasColumn('orders', $column)) {
                $columns[] = $column;
            }
        }

        if ($columns) {
            Schema::table('orders', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
