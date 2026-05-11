<?php

namespace Tests;

use Database\Seeders\RoleAbilitySeeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (Schema::hasTable('roles') && Schema::hasTable('abilities')) {
            $this->seed(RoleAbilitySeeder::class);
        }
    }
}
