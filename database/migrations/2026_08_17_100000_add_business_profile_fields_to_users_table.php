<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add the business profile fields used by the business onboarding flow.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('business_name')->nullable()->after('name');
            $table->string('business_website')->nullable()->after('business_name');
            $table->string('business_industry', 100)->nullable()->after('business_website');
            $table->string('business_phone', 40)->nullable()->after('business_industry');
        });
    }

    /**
     * Remove the business profile fields.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'business_name',
                'business_website',
                'business_industry',
                'business_phone',
            ]);
        });
    }
};
