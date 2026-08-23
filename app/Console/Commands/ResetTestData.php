<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetTestData extends Command
{
    protected $signature = 'aipm:reset-test';

    protected $description = 'Reset AIPM test data while preserving programs, products, admin accounts, and system configuration.';

    /**
     * Tables containing platform/reference data or Laravel infrastructure that
     * must survive a test-data reset.
     */
    private array $preservedTables = [
        'migrations',
        'cache',
        'cache_locks',
        'jobs',
        'job_batches',
        'failed_jobs',
        'password_reset_tokens',
        'sessions',
        'products',
        'partnership_programs',
        'program_products',
        'commission_rules',
        'platform_settings',
        'platform_payment_settings',
        'roles',
        'permissions',
        'role_has_permissions',
        'model_has_roles',
        'model_has_permissions',
    ];

    /**
     * Business/transaction tables are cleared before users so that foreign-key
     * relationships are removed in a predictable order.
     */
    private array $transactionTables = [
        'payment_disputes',
        'payment_submissions',
        'order_items',
        'commissions',
        'payouts',
        'business_payouts',
        'orders',
        'clicks',
        'referrals',
        'program_partners',
    ];

    public function handle(): int
    {
        if (app()->environment('production')) {
            $this->error('AIPM test reset is blocked while APP_ENV=production.');
            $this->line('Run this command against a test/staging database, or change the environment explicitly before running it.');

            return self::FAILURE;
        }

        if (! $this->confirm('This will permanently remove all non-admin users and their test activity. Continue?', false)) {
            $this->info('Reset cancelled.');

            return self::SUCCESS;
        }

        DB::transaction(function (): void {
            Schema::disableForeignKeyConstraints();

            try {
                foreach ($this->transactionTables as $table) {
                    if (Schema::hasTable($table)) {
                        DB::table($table)->delete();
                        $this->line("✓ Cleared {$table}");
                    }
                }

                $this->clearUserNotificationsAndActivity();
                $this->removeNonAdminUsers();
            } finally {
                Schema::enableForeignKeyConstraints();
            }
        });

        $this->newLine();
        $this->info('AIPM test data reset completed.');
        $this->line('Preserved: programs, products, program-product relationships, commission rules, platform settings, roles/permissions, and admin accounts.');

        return self::SUCCESS;
    }

    private function clearUserNotificationsAndActivity(): void
    {
        foreach (['notifications', 'activity_log'] as $table) {
            if (Schema::hasTable($table)) {
                DB::table($table)->delete();
                $this->line("✓ Cleared {$table}");
            }
        }
    }

    private function removeNonAdminUsers(): void
    {
        $adminRoleNames = ['Super Admin', 'Admin'];

        User::query()
            ->whereDoesntHave('roles', function ($query) use ($adminRoleNames): void {
                $query->whereIn('name', $adminRoleNames);
            })
            ->chunkById(100, function ($users): void {
                foreach ($users as $user) {
                    $user->delete();
                }
            });

        $this->line('✓ Removed non-admin users (customers, partners, businesses, and their accounts)');
    }
}
