<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketing_campaign_leads', function (Blueprint $table) {
            $table->string('normalized_email')->nullable()->after('email');
            $table->string('normalized_whatsapp')->nullable()->after('whatsapp_number');
            $table->unique(['campaign_id', 'normalized_email'], 'campaign_leads_campaign_email_unique');
            $table->unique(['campaign_id', 'normalized_whatsapp'], 'campaign_leads_campaign_whatsapp_unique');
        });
    }

    public function down(): void
    {
        Schema::table('marketing_campaign_leads', function (Blueprint $table) {
            $table->dropUnique('campaign_leads_campaign_email_unique');
            $table->dropUnique('campaign_leads_campaign_whatsapp_unique');
            $table->dropColumn(['normalized_email', 'normalized_whatsapp']);
        });
    }
};
