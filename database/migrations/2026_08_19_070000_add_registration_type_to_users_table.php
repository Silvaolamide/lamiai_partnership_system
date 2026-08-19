<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('registration_type', 30)->default('customer')->after('email');
            $table->index('registration_type');
        });

        // Preserve the meaning of accounts that already existed before this field.
        DB::table('users')->whereIn('id', DB::table('model_has_roles')->whereIn('role_id', function ($q) {
            $q->select('id')->from('roles')->where('name', 'program_manager');
        })->pluck('model_id'))->update(['registration_type' => 'business']);

        DB::table('users')->whereIn('id', DB::table('model_has_roles')->whereIn('role_id', function ($q) {
            $q->select('id')->from('roles')->where('name', 'partner');
        })->pluck('model_id'))->update(['registration_type' => 'partner']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['registration_type']);
            $table->dropColumn('registration_type');
        });
    }
};
