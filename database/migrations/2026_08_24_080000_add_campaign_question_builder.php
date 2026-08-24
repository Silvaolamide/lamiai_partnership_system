<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketing_campaigns', function (Blueprint $table) {
            $table->json('questions')->nullable()->after('status');
        });

        Schema::table('marketing_campaign_leads', function (Blueprint $table) {
            $table->json('responses')->nullable()->after('user_agent');
        });
    }

    public function down(): void
    {
        Schema::table('marketing_campaign_leads', function (Blueprint $table) {
            $table->dropColumn('responses');
        });

        Schema::table('marketing_campaigns', function (Blueprint $table) {
            $table->dropColumn('questions');
        });
    }
};
