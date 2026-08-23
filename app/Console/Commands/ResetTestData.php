<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

class ResetTestData extends Command
{
    protected $signature = 'aipm:reset-test';

    protected $description = 'Completely reset AIPM test data and platform configuration, then create a fresh Super Admin account.';

    private array $tablesToClear = [
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
        'program_products',
        'commission_rules',
        'products',
        'partnership_programs',
        'platform_payment_settings',
        'platform_settings',
        'model_has_roles',
        'model_has_permissions',
        'role_has_permissions',
        'permissions',
        'roles',
        'notifications',
        'activity_log',
    ];

    public function handle(): int
    {
        if (app()->environment('production')) {
            $this->error('AIPM test reset is blocked while APP_ENV=production.');
            $this->line('Run this command against a test/staging database.');

            return self::FAILURE;
        }

        $superAdminEmail = 'olamideagunkejoye@gmail.com';

        $this->newLine();
        $this->warn('⚠️  AIPM DATABASE RESET');
        $this->warn('This operation will permanently delete all application data, platform catalogue/configuration, authorization data, and existing users.');
        $this->newLine();
        $this->warn('A new Super Admin will be created:');
        $this->line('  Name:     Olamide Agunkejoye');
        $this->line("  Email:    {$superAdminEmail}");
        $this->line('  Password: password123');
        $this->newLine();

        if (! $this->confirm('THIS CANNOT BE UNDONE. Continue?', false)) {
            $this->info('Reset cancelled.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($superAdminEmail): void {
            Schema::disableForeignKeyConstraints();

            try {
                $this->clearTables();
                $this->removeAllUsers();
                $this->createSuperAdmin($superAdminEmail);
            } finally {
                Schema::enableForeignKeyConstraints();
            }
        });

        $this->newLine();
        $this->info('✓ AIPM test database reset completed successfully.');
        $this->info('Fresh Super Admin account created:');
        $this->line('  Name:     Olamide Agunkejoye');
        $this->line("  Email:    {$superAdminEmail}");
        $this->line('  Password: password123');
        $this->newLine();
        $this->warn('IMPORTANT: Change the Super Admin password after first login.');

        return self::SUCCESS;
    }

    private function clearTables(): void
    {
        foreach ($this->tablesToClear as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            DB::table($table)->delete();
            $this->line("✓ Cleared {$table}");
        }
    }

    private function removeAllUsers(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        User::query()->chunkById(100, function ($users): void {
            foreach ($users as $user) {
                $user->delete();
            }
        });

        $this->line('✓ Removed all users, including existing Super Admin accounts');
    }

    private function createSuperAdmin(string $email): void
    {
        $superAdminRole = Role::create([
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]);

        $superAdmin = User::create([
            'name' => 'Olamide Agunkejoye',
            'email' => $email,
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $superAdmin->assignRole($superAdminRole);

        $this->line('✓ Created fresh Super Admin role');
        $this->line('✓ Created fresh Super Admin account');
    }
}
