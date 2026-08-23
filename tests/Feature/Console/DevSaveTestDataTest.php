<?php

namespace LibreNMS\Tests\Feature\Console;

use LibreNMS\Tests\TestCase;

final class DevSaveTestDataTest extends TestCase
{
    public function testRequiresExplicitScope(): void
    {
        $this->artisan('dev:save-test-data')
            ->expectsOutput('Specify --all or --os.')
            ->assertExitCode(1);
    }

    public function testAllAndOsAreMutuallyExclusive(): void
    {
        $this->artisan('dev:save-test-data', ['--all' => true, '--os' => 'routeros'])
            ->expectsOutput('--all and --os cannot be used together.')
            ->assertExitCode(1);
    }

    public function testVariantRequiresOs(): void
    {
        $this->artisan('dev:save-test-data', ['--all' => true, '--variant' => 'wifi'])
            ->expectsOutput('--variant requires --os.')
            ->assertExitCode(1);
    }
}
