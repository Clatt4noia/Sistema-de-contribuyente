<?php

namespace Tests\Feature;

use Tests\TestCase;

class SeederSmokeTest extends TestCase
{
    public function test_migrate_fresh_seed_does_not_fail(): void
    {
        $this->artisan('migrate:fresh --seed')->assertExitCode(0);
    }
}

