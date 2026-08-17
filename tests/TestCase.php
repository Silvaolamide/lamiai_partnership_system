<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\Models\Role;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Tests use the application's authorization roles, but RefreshDatabase
        // starts with an empty database. Create the roles needed by feature
        // tests so each test suite is isolated and deterministic.
        foreach (['super_admin', 'program_manager', 'partner', 'customer'] as $role) {
            Role::firstOrCreate([
                'name' => $role,
                'guard_name' => 'web',
            ]);
        }
    }
}
