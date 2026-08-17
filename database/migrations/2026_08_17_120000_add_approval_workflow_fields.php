<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('business_super_admin_approved_at')->nullable()->after('email_verified_at');
            $table->timestamp('business_rejected_at')->nullable()->after('business_super_admin_approved_at');
        });

        Schema::table('program_partners', function (Blueprint $table) {
            $table->timestamp('super_admin_approved_at')->nullable()->after('approved_at');
            $table->timestamp('business_approved_at')->nullable()->after('super_admin_approved_at');
        });

        Schema::create('platform_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        DB::table('platform_settings')->insert([
            'key' => 'partner_super_admin_approval_required',
            'value' => '1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

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
        Schema::dropIfExists('platform_settings');

        Schema::table('program_partners', function (Blueprint $table) {
            $table->dropColumn(['super_admin_approved_at', 'business_approved_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['business_super_admin_approved_at', 'business_rejected_at']);
        });
    }
};
