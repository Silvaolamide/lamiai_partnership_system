<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

class TestBaseline extends Command
{
    protected $signature = 'aipm:test-baseline
                            {action : save or restore}
                            {--force : Skip the confirmation prompt}';

    protected $description = 'Save or restore the current AIPM testing database baseline.';

    public function handle(): int
    {
        if (app()->environment('production')) {
            $this->error('This command is blocked while APP_ENV=production.');
            $this->line('Use it only against the dedicated testing/staging database.');

            return self::FAILURE;
        }

        $action = strtolower($this->argument('action'));

        return match ($action) {
            'save' => $this->saveBaseline(),
            'restore' => $this->restoreBaseline(),
            default => $this->invalidAction(),
        };
    }

    private function saveBaseline(): int
    {
        $path = $this->baselinePath();

        $this->newLine();
        $this->warn('This will replace the existing AIPM test baseline with the database as it exists now.');
        $this->line("Baseline: {$path}");
        $this->newLine();

        if (! $this->option('force') && ! $this->confirm('Save this database as the new testing baseline?', false)) {
            $this->info('Baseline save cancelled.');

            return self::SUCCESS;
        }

        return $this->runDatabaseCommand('dump', $path);
    }

    private function restoreBaseline(): int
    {
        $path = $this->baselinePath();

        if (! File::exists($path)) {
            $this->error('No AIPM test baseline exists yet.');
            $this->line('Create one first with:');
            $this->line('php artisan aipm:test-baseline save');

            return self::FAILURE;
        }

        $this->newLine();
        $this->error('⚠️  DATABASE RESTORE');
        $this->warn('This will replace the current testing database with the saved baseline.');
        $this->line("Baseline: {$path}");
        $this->line('All changes made since the baseline was saved will be lost.');
        $this->newLine();

        if (! $this->option('force') && ! $this->confirm('RESTORE THE TESTING DATABASE NOW?', false)) {
            $this->info('Baseline restore cancelled.');

            return self::SUCCESS;
        }

        return $this->runDatabaseCommand('restore', $path);
    }

    private function runDatabaseCommand(string $action, string $path): int
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        if (($config['driver'] ?? null) !== 'mysql') {
            $this->error('The AIPM test baseline command currently supports MySQL only.');

            return self::FAILURE;
        }

        $binary = $action === 'dump' ? $this->findBinary('mysqldump') : $this->findBinary('mysql');

        if (! $binary) {
            $this->error($action === 'dump'
                ? 'mysqldump was not found on this server.'
                : 'mysql was not found on this server.');
            $this->line('Install/enable the MySQL client utilities or provide them in PATH.');

            return self::FAILURE;
        }

        $directory = dirname($path);
        File::ensureDirectoryExists($directory);

        $defaultsFile = $directory . '/.aipm-db-' . bin2hex(random_bytes(8)) . '.cnf';

        File::put($defaultsFile, $this->mysqlDefaultsFile($config));
        @chmod($defaultsFile, 0600);

        try {
            $database = $config['database'] ?? null;

            if (! $database) {
                $this->error('The configured database name is missing.');

                return self::FAILURE;
            }

            if ($action === 'dump') {
                $command = [
                    $binary,
                    "--defaults-extra-file={$defaultsFile}",
                    '--single-transaction',
                    '--quick',
                    '--routines',
                    '--triggers',
                    '--events',
                    $database,
                ];

                $process = new Process($command);
                $process->setTimeout(600);
                $process->run(function (string $type, string $buffer): void {
                    if ($type === Process::ERR) {
                        $this->output->write($buffer);
                    }
                });

                if (! $process->isSuccessful()) {
                    if (File::exists($path)) {
                        File::delete($path);
                    }

                    $this->error('Database baseline save failed.');
                    $this->line(trim($process->getErrorOutput()));

                    return self::FAILURE;
                }

                // mysqldump writes to stdout; save it atomically only after success.
                File::put($path, $process->getOutput());

                $this->info('✓ AIPM testing baseline saved successfully.');
                $this->line("  {$path}");

                return self::SUCCESS;
            }

            $command = [
                $binary,
                "--defaults-extra-file={$defaultsFile}",
                $database,
            ];

            $process = new Process($command, null, null, File::get($path));
            $process->setTimeout(600);
            $process->run(function (string $type, string $buffer): void {
                if ($type === Process::ERR) {
                    $this->output->write($buffer);
                }
            });

            if (! $process->isSuccessful()) {
                $this->error('Database baseline restore failed.');
                $this->line(trim($process->getErrorOutput()));

                return self::FAILURE;
            }

            $this->info('✓ AIPM testing database restored to the saved baseline.');

            return self::SUCCESS;
        } finally {
            if (File::exists($defaultsFile)) {
                File::delete($defaultsFile);
            }
        }
    }

    private function mysqlDefaultsFile(array $config): string
    {
        $lines = [
            '[client]',
            'user=' . ($config['username'] ?? ''),
            'password=' . ($config['password'] ?? ''),
        ];

        if (! empty($config['host'])) {
            $lines[] = 'host=' . $config['host'];
        }

        if (! empty($config['port'])) {
            $lines[] = 'port=' . $config['port'];
        }

        if (! empty($config['unix_socket'])) {
            $lines[] = 'socket=' . $config['unix_socket'];
        }

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }

    private function baselinePath(): string
    {
        return storage_path('app/private/aipm-test-baseline.sql');
    }

    private function findBinary(string $binary): ?string
    {
        $process = new Process(['sh', '-c', 'command -v ' . escapeshellarg($binary)]);
        $process->run();

        if (! $process->isSuccessful()) {
            return null;
        }

        $path = trim($process->getOutput());

        return $path !== '' ? $path : null;
    }

    private function invalidAction(): int
    {
        $this->error('Invalid action. Use "save" or "restore".');

        return self::FAILURE;
    }
}
