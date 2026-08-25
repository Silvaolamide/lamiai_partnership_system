<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('social_follow_campaign_accounts')) {
            Schema::create('social_follow_campaign_accounts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('campaign_id')->constrained('social_follow_campaigns')->cascadeOnDelete();
                $table->foreignId('social_account_id')->constrained('social_accounts')->cascadeOnDelete();
                $table->unsignedInteger('points')->default(1);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        // Keep the index name short enough for MySQL's 64-character identifier limit.
        $indexes = collect(Schema::getIndexes('social_follow_campaign_accounts'))
            ->pluck('name')
            ->all();

        if (!in_array('sfca_campaign_account_unique', $indexes, true)) {
            Schema::table('social_follow_campaign_accounts', function (Blueprint $table) {
                $table->unique(
                    ['campaign_id', 'social_account_id'],
                    'sfca_campaign_account_unique'
                );
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('social_follow_campaign_accounts');
    }
};
