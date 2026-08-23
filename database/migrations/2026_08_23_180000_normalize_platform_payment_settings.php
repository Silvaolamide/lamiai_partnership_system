<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('platform_payment_settings')) {
            return;
        }

        $columns = [
            'bank_name',
            'account_name',
            'account_number',
            'support_phone',
            'support_whatsapp',
            'support_email',
        ];

        DB::transaction(function () use ($columns): void {
            $rows = DB::table('platform_payment_settings')
                ->orderBy('id')
                ->get();

            $canonical = $rows->first(function ($row) use ($columns): bool {
                foreach ($columns as $column) {
                    if ($row->{$column} !== null && $row->{$column} !== '') {
                        return true;
                    }
                }

                return false;
            }) ?? $rows->first();

            $values = [];
            foreach ($columns as $column) {
                $values[$column] = $canonical?->{$column};
            }

            $now = now();

            if (DB::table('platform_payment_settings')->where('id', 1)->exists()) {
                DB::table('platform_payment_settings')
                    ->where('id', 1)
                    ->update(array_merge($values, ['updated_at' => $now]));
            } else {
                DB::table('platform_payment_settings')->insert(array_merge(
                    ['id' => 1, 'created_at' => $canonical?->created_at ?? $now],
                    $values,
                    ['updated_at' => $now],
                ));
            }

            DB::table('platform_payment_settings')
                ->where('id', '!=', 1)
                ->delete();
        });
    }

    public function down(): void
    {
        // This is a data-normalization migration. The singleton record should
        // remain intact when rolling back application code.
    }
};
