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

    public function testDeviceRequiredWithoutInteraction(): void
    {
        $this->artisan('dev:collect-snmprec', ['--model' => 'test', '--no-interaction' => true])
            ->expectsOutput('Specify the device, for example lnms dev:collect-snmprec 42.')
            ->assertExitCode(1);
    }

    public function testAsksForDevice(): void
    {
        $device = Device::factory()->create(['os' => 'generic']);

        $this->artisan('dev:collect-snmprec', ['--model' => 'test'])
            ->expectsQuestion('Which device do you want to collect data from?', (string) $device->device_id)
            ->expectsOutput('The device is detected as generic. Add detection for its OS first, then collect the test data. To get a full walk to work from, use --full --output=<file>.')
            ->assertExitCode(1);
    }

    public function testMissingDevice(): void
    {
        $this->artisan('dev:collect-snmprec', ['device' => 'non_existent_device_99999', '--model' => 'test'])
            ->expectsOutput("Device 'non_existent_device_99999' not found.")
            ->assertExitCode(1);
    }

    public function testMissingModelOption(): void
    {
        $device = Device::factory()->create();

        $this->artisan('dev:collect-snmprec', ['device' => (string) $device->device_id, '--no-interaction' => true])
            ->expectsOutput('Specify the device model, for example -M wrt54g.')
            ->assertExitCode(1);
    }

    public function testInvalidModelWithUnderscore(): void
    {
        $device = Device::factory()->create();

        $this->artisan('dev:collect-snmprec', ['device' => (string) $device->device_id, '--model' => 'invalid_model'])
            ->expectsOutput('Model name can only contain letters, numbers, dots (.), plus (+), and dashes (-).')
            ->assertExitCode(1);
    }

    public function testInvalidModelCharacters(): void
    {
        $this->artisan('dev:collect-snmprec', ['device' => 'unused', '--model' => '../foo'])
            ->expectsOutput('Model name can only contain letters, numbers, dots (.), plus (+), and dashes (-).')
            ->assertExitCode(1);
    }

    public function testInvalidModule(): void
    {
        $this->artisan('dev:collect-snmprec', ['device' => 'unused', '--model' => 'test', '--modules' => 'nope'])
            ->expectsOutput('Invalid module name: nope')
            ->assertExitCode(1);
    }

    public function testGenericDeviceIsRejected(): void
    {
        $device = Device::factory()->create(['os' => 'generic']);

        $this->artisan('dev:collect-snmprec', ['device' => (string) $device->device_id, '--model' => 'wrt54g'])
            ->expectsOutput('The device is detected as generic. Add detection for its OS first, then collect the test data. To get a full walk to work from, use --full --output=<file>.')
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
            '--model' => 'test',
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

        $modelOption = $cmd->getDefinition()->getOption('model');
        $this->assertInstanceOf(DynamicInputOption::class, $modelOption);
        $modelCompletions = $cmd->completeOptionValue($modelOption, 'wi');
        $this->assertContains('wifi', $modelCompletions);
    }
}
