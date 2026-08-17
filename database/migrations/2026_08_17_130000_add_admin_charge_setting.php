<?php

use App\Models\PlatformSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        PlatformSetting::setValue('admin_charge_percent', PlatformSetting::getValue('admin_charge_percent', 0));
    }

    public function down(): void
    {
        PlatformSetting::where('key', 'admin_charge_percent')->delete();
    }
};
