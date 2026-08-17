<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // These columns may already exist on installations that received an earlier
        // version of the approval workflow. Add only what is missing.
        if (! Schema::hasColumn('users', 'business_super_admin_approved_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('business_super_admin_approved_at')->nullable()->after('email_verified_at');
            });
        }

        if (! Schema::hasColumn('users', 'business_rejected_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('business_rejected_at')->nullable()->after('business_super_admin_approved_at');
            });
        }

        if (! Schema::hasColumn('program_partners', 'super_admin_approved_at')) {
            Schema::table('program_partners', function (Blueprint $table) {
                $table->timestamp('super_admin_approved_at')->nullable()->after('approved_at');
            });
        }

        if (! Schema::hasColumn('program_partners', 'business_approved_at')) {
            Schema::table('program_partners', function (Blueprint $table) {
                $table->timestamp('business_approved_at')->nullable()->after('super_admin_approved_at');
            });
        }

        // platform_settings is created by the dedicated migration
        // 2026_08_17_100000_create_platform_settings_table.php.
        // Do not create it again here.
        if (! Schema::hasTable('platform_settings')) {
            Schema::create('platform_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        // Seed the default only when it does not already exist.
        if (! DB::table('platform_settings')->where('key', 'partner_super_admin_approval_required')->exists()) {
            DB::table('platform_settings')->insert([
                'key' => 'partner_super_admin_approval_required',
                'value' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Preserve the access state of existing records created before this workflow existed.
        DB::table('program_partners')
            ->where('status', 'active')
            ->update([
                'super_admin_approved_at' => now(),
                'business_approved_at' => now(),
            ]);

        DB::table('users')
            ->whereIn('id', DB::table('model_has_roles')
                ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                ->where('roles.name', 'program_manager')
                ->pluck('model_id'))
            ->update(['business_super_admin_approved_at' => now()]);
    }

    public function down(): void
    {
        // platform_settings has its own dedicated migration, so this migration
        // must not drop it.

        if (Schema::hasColumn('program_partners', 'business_approved_at')) {
            Schema::table('program_partners', function (Blueprint $table) {
                $table->dropColumn('business_approved_at');
            });
        }

        if (Schema::hasColumn('program_partners', 'super_admin_approved_at')) {
            Schema::table('program_partners', function (Blueprint $table) {
                $table->dropColumn('super_admin_approved_at');
            });
        }

        if (Schema::hasColumn('users', 'business_rejected_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('business_rejected_at');
            });
        }

        if (Schema::hasColumn('users', 'business_super_admin_approved_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('business_super_admin_approved_at');
            });
        }
    }
};
