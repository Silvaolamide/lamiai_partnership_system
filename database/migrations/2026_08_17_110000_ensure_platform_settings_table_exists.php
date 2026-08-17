<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // This migration is intentionally defensive. It repairs installations
        // where the original platform_settings migration exists in the codebase
        // but was not actually applied to the current database.
        if (! Schema::hasTable('platform_settings')) {
            Schema::create('platform_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        DB::table('platform_settings')->updateOrInsert(
            ['key' => 'partner_super_admin_approval_required'],
            [
                'value' => '1',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        // Do not drop the table here because it may have been created by the
        // original platform settings migration.
    }
};
