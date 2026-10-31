<?php

namespace LibreNMS\Tests\Feature\Console;

use App\Console\Commands\DevGenerateTestData;
use LibreNMS\Tests\TestCase;

final class DevGenerateTestDataTest extends TestCase
{
    public function testRequiresExplicitScope(): void
    {
        $this->artisan('dev:generate-test-data')
            ->expectsOutput('Specify an OS (or all).')
            ->assertExitCode(1);
    }

    public function testAcceptsOsAsArgument(): void
    {
        $this->artisan('dev:generate-test-data', ['prefix' => 'nonexistent-os-xyz'])
            ->expectsOutput('No matching JSON test fixtures found for prefix "nonexistent-os-xyz".')
            ->assertExitCode(1);
    }

    public function testOsArgumentCompletionIncludesAllAndOsList(): void
    {
        $command = new DevGenerateTestData;

        $completions = $command->completeArgument('prefix', 'al');
        $this->assertIsArray($completions);
        $this->assertContains('all', $completions);

        $completions = $command->completeArgument('prefix', 'io');
        $this->assertIsArray($completions);
        $this->assertContains('ios', $completions);
        $this->assertNotContains('all', $completions);
    }

    public function testPrefixMatchesFixtureNames(): void
    {
        $command = new DevGenerateTestData;
        $completions = $command->completeArgument('prefix', 'ios_');
        $this->assertIsArray($completions);
        $this->assertNotContains('ios', $completions);
        foreach ($completions as $completion) {
            $this->assertStringStartsWith('ios_', $completion);
        }
    }

    public function testPrefixCompletionIncludesSnmprecWithoutJson(): void
    {
        $command = new DevGenerateTestData;
        $completions = $command->completeArgument('prefix', '3com_');

        $this->assertIsArray($completions);
        $this->assertContains('3com_1', $completions);
    }
}
