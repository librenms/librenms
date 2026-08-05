<?php

namespace LibreNMS\Tests\Feature\Console;

use App\Models\Device;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;

final class DevCollectSnmprecTest extends TestCase
{
    use DatabaseTransactions;

    public function testHelpOutput(): void
    {
        $this->artisan('dev:collect-snmprec', ['--help' => true])
            ->assertExitCode(0);
    }

    public function testMissingDevice(): void
    {
        $this->artisan('dev:collect-snmprec', ['device' => 'non_existent_device_99999', '--variant' => 'test'])
            ->expectsOutput("Device 'non_existent_device_99999' not found.")
            ->assertExitCode(1);
    }

    public function testMissingVariantOption(): void
    {
        $device = Device::factory()->create();

        $this->artisan('dev:collect-snmprec', ['device' => (string) $device->device_id])
            ->expectsOutput('The --variant (-v) option is required.')
            ->assertExitCode(1);
    }

    public function testInvalidVariantWithUnderscore(): void
    {
        $device = Device::factory()->create();

        $this->artisan('dev:collect-snmprec', ['device' => (string) $device->device_id, '--variant' => 'invalid_variant'])
            ->expectsOutput('Variant name cannot contain an underscore (_).')
            ->assertExitCode(1);
    }

    public function testCompletion(): void
    {
        $device = Device::factory()->create([
            'hostname' => 'router.example.com',
        ]);

        $cmd = new \App\Console\Commands\DevCollectSnmprec();

        $completionsByHost = $cmd->completeArgument('device', 'router.');
        $this->assertContains('router.example.com', $completionsByHost);

        $completionsById = $cmd->completeArgument('device', (string) $device->device_id);
        $this->assertContains('router.example.com', $completionsById);
    }
}
