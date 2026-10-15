<?php

namespace LibreNMS\Tests\Feature\Console;

use App\Console\Commands\DevCollectSnmprec;
use App\Console\DynamicInputOption;
use App\Models\Device;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\TestCase;
use LibreNMS\Tests\Traits\FakesTestFixtures;

final class DevCollectSnmprecTest extends TestCase
{
    use DatabaseTransactions;
    use FakesTestFixtures;

    public function testHelpOutput(): void
    {
        $this->artisan('dev:collect-snmprec', ['--help' => true])
            ->assertExitCode(0);
    }

    public function testMissingDevice(): void
    {
        $this->artisan('dev:collect-snmprec', ['device' => 'non_existent_device_99999', '--variant' => 'wrt54g'])
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

        $this->artisan('dev:collect-snmprec', ['device' => (string) $device->device_id, '--variant' => 'wrt_54g'])
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

        $this->artisan('dev:collect-snmprec', ['device' => (string) $device->device_id, '--os' => 'linksys', '--variant' => 'wrt54g'])
            ->expectsConfirmation("There is no OS definition for 'linksys'. Collect test data for a new OS?", 'no')
            ->expectsOutput("Unknown OS 'linksys'.")
            ->assertExitCode(1);
    }

    public function testFullWalkRequiresOutput(): void
    {
        $this->artisan('dev:collect-snmprec', ['device' => 'unused', '--full' => true])
            ->expectsOutput('--full requires --output, because a full walk is not saved as a test fixture.')
            ->assertExitCode(1);
    }

    public function testFullWalkRejectsModules(): void
    {
        $this->artisan('dev:collect-snmprec', [
            'device' => 'unused',
            '--variant' => 'wrt54g',
            '--full' => true,
            '--modules' => 'ports',
        ])->expectsOutput('--full and --modules cannot be used together because a full walk does not run modules.')
            ->assertExitCode(1);
    }

    public function testCompletion(): void
    {
        $device = Device::factory()->create([
            'hostname' => 'wrt54g.example.com',
        ]);
        $this->fakeTestFixtures(['linksys', 'linksys_wrt54g', 'linksys_wrt54gl']);

        $cmd = new DevCollectSnmprec;

        $completionsByHost = $cmd->completeArgument('device', 'wrt54g.');
        $this->assertContains('wrt54g.example.com', $completionsByHost);

        $completionsById = $cmd->completeArgument('device', (string) $device->device_id);
        $this->assertContains('wrt54g.example.com', $completionsById);

        $modulesOption = $cmd->getDefinition()->getOption('modules');
        $this->assertInstanceOf(DynamicInputOption::class, $modulesOption);
        $moduleCompletions = $cmd->completeOptionValue($modulesOption, 'ports,sen');
        $this->assertContains('ports,sensors', $moduleCompletions);

        $variantOption = $cmd->getDefinition()->getOption('variant');
        $this->assertInstanceOf(DynamicInputOption::class, $variantOption);
        $variantCompletions = $cmd->completeOptionValue($variantOption, 'wrt');
        $this->assertSame(['wrt54g', 'wrt54gl'], $variantCompletions->all());
    }
}
