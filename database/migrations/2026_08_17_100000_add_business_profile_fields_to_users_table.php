<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add the business profile fields used by the business onboarding flow.
     *
     * This migration is intentionally defensive because some existing local
     * installations may already have these columns while the migration itself
     * is still marked as pending.
     */
    public function up(): void
    {
        $columns = [
            'business_name' => fn (Blueprint $table) => $table->string('business_name')->nullable()->after('name'),
            'business_website' => fn (Blueprint $table) => $table->string('business_website')->nullable()->after('business_name'),
            'business_industry' => fn (Blueprint $table) => $table->string('business_industry', 100)->nullable()->after('business_website'),
            'business_phone' => fn (Blueprint $table) => $table->string('business_phone', 40)->nullable()->after('business_industry'),
        ];

        foreach ($columns as $column => $definition) {
            if (! Schema::hasColumn('users', $column)) {
                Schema::table('users', $definition);
            }
        }
    }

    /**
     * Remove the business profile fields.
     */
    public function down(): void
    {
        $columns = [
            'business_name',
            'business_website',
            'business_industry',
            'business_phone',
        ];

        foreach ($columns as $column) {
            if (Schema::hasColumn('users', $column)) {
                Schema::table('users', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
