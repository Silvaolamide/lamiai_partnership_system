<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $partners = DB::table('program_partners')->orderBy('user_id')->orderBy('id')->get();
        $seenUsers = [];

        foreach ($partners as $partner) {
            if (!isset($seenUsers[$partner->user_id])) {
                $seenUsers[$partner->user_id] = true;
                continue;
            }

            DB::table('program_partners')->where('id', $partner->id)->update(['approval_context' => 'program']);

            if ($partner->status !== 'pending') {
                continue;
            }

            $program = DB::table('partnership_programs')->where('id', $partner->program_id)->first();
            $settings = $program && $program->settings ? json_decode($program->settings, true) : [];
            $businessApproval = (bool) ($settings['partner_business_approval_required'] ?? false);
            $superAdminApproval = (bool) ($settings['partner_super_admin_approval_required'] ?? false);

            if ($businessApproval || $superAdminApproval) {
                continue;
            }

            $code = 'LAMI-' . Str::upper(Str::random(8));
            while (DB::table('program_partners')->where('partner_code', $code)->exists()) {
                $code = 'LAMI-' . Str::upper(Str::random(8));
            }

            DB::table('program_partners')->where('id', $partner->id)->update([
                'status' => 'active',
                'super_admin_approved_at' => $partner->super_admin_approved_at ?: now(),
                'business_approved_at' => $partner->business_approved_at ?: now(),
                'partner_code' => $code,
                'approved_at' => $partner->approved_at ?: now(),
            ]);
        }
    }

    public function down(): void
    {
        // Context is metadata and the activation backfill is intentionally not reversed.
    }
};
