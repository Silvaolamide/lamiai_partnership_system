<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('platform_payment_settings', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name')->nullable();
            $table->string('account_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('support_phone')->nullable();
            $table->string('support_whatsapp')->nullable();
            $table->string('support_email')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('platform_payment_settings'); }
};
