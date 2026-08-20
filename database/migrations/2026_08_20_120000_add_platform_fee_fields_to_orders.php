<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('platform_fee_percent', 8, 4)->default(0)->after('total');
            $table->decimal('platform_fee_amount', 15, 2)->default(0)->after('platform_fee_percent');
            $table->decimal('business_net_amount', 15, 2)->default(0)->after('platform_fee_amount');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['platform_fee_percent', 'platform_fee_amount', 'business_net_amount']);
        });
    }
};
