<?php

namespace LibreNMS\Tests\Feature\Console;

use App\Console\Commands\DevCollectSnmprec;
use App\Console\DynamicInputOption;
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

        $this->artisan('dev:collect-snmprec', ['device' => (string) $device->device_id, '--no-interaction' => true])
            ->expectsOutput('The --variant option is required when not running interactively to avoid accidentally updating the base fixture; use --variant= to select it explicitly.')
            ->assertExitCode(1);
    }

    public function testInvalidVariantWithUnderscore(): void
    {
        $device = Device::factory()->create();

        $this->artisan('dev:collect-snmprec', ['device' => (string) $device->device_id, '--variant' => 'invalid_variant'])
            ->expectsOutput('Variant name cannot contain an underscore (_).')
            ->assertExitCode(1);
    }

    public function testInvalidVariantCharacters(): void
    {
        $this->artisan('dev:collect-snmprec', ['device' => 'unused', '--variant' => '../foo'])
            ->expectsOutput('Variant name can only contain letters, numbers, dots (.), plus (+), and dashes (-).')
            ->assertExitCode(1);
    }

    public function testInvalidOsCharacters(): void
    {
        $device = Device::factory()->create(['os' => 'generic']);

        $this->artisan('dev:collect-snmprec', ['device' => (string) $device->device_id, '--os' => '../foo', '--variant' => ''])
            ->expectsOutput("Unknown OS '../foo'.")
            ->assertExitCode(1);
    }

    public function testInvalidOsOption(): void
    {
        $device = Device::factory()->create(['os' => 'generic']);

        $this->artisan('dev:collect-snmprec', ['device' => (string) $device->device_id, '--os' => 'foobar', '--variant' => ''])
            ->expectsConfirmation("There is no OS definition for 'foobar'. Collect test data for a new OS?", 'no')
            ->expectsOutput("Unknown OS 'foobar'.")
            ->assertExitCode(1);
    }

    public function testFullWalkRejectsModules(): void
    {
        $this->artisan('dev:collect-snmprec', [
            'device' => 'unused',
            '--variant' => 'test',
            '--full' => true,
            '--modules' => 'ports',
        ])->expectsOutput('--full and --modules cannot be used together because a full walk does not run modules.')
            ->assertExitCode(1);
    }

    public function testCompletion(): void
    {
        $device = Device::factory()->create([
            'hostname' => 'router.example.com',
        ]);

        $cmd = new DevCollectSnmprec;

        $completionsByHost = $cmd->completeArgument('device', 'router.');
        $this->assertContains('router.example.com', $completionsByHost);

        $completionsById = $cmd->completeArgument('device', (string) $device->device_id);
        $this->assertContains('router.example.com', $completionsById);

        $modulesOption = $cmd->getDefinition()->getOption('modules');
        $this->assertInstanceOf(DynamicInputOption::class, $modulesOption);
        $moduleCompletions = $cmd->completeOptionValue($modulesOption, 'ports,sen');
        $this->assertContains('ports,sensors', $moduleCompletions);

        $variantOption = $cmd->getDefinition()->getOption('variant');
        $this->assertInstanceOf(DynamicInputOption::class, $variantOption);
        $variantCompletions = $cmd->completeOptionValue($variantOption, 'wi');
        $this->assertContains('wifi', $variantCompletions);
    }
}
