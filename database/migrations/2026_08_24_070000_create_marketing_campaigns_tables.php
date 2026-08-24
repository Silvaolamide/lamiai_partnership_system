<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('headline')->nullable();
            $table->text('description')->nullable();
            $table->string('redirect_url', 2048);
            $table->enum('status', ['draft', 'active', 'paused'])->default('draft')->index();
            $table->timestamps();
        });

        Schema::create('marketing_campaign_leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('marketing_campaigns')->cascadeOnDelete();
            $table->string('name');
            $table->string('whatsapp_number', 50);
            $table->string('email');
            $table->boolean('has_sold_online');
            $table->text('what_sold')->nullable();
            $table->enum('sales_result', ['very_good', 'good', 'not_good'])->nullable();
            $table->string('utm_source')->nullable()->index();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable()->index();
            $table->string('utm_content')->nullable();
            $table->string('utm_term')->nullable();
            $table->text('landing_page')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_campaign_leads');
        Schema::dropIfExists('marketing_campaigns');
    }
};
