<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('program_partners', function (Blueprint $table) {
            $table->string('approval_context')->default('initial')->after('status');
            $table->index(['approval_context', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('program_partners', function (Blueprint $table) {
            $table->dropIndex(['approval_context', 'status']);
            $table->dropColumn('approval_context');
        });
    }
};
