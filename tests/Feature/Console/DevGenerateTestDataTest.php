<?php

namespace LibreNMS\Tests\Feature\Console;

use App\Console\Commands\DevGenerateTestData;
use App\Console\DynamicInputOption;
use LibreNMS\Tests\TestCase;
use Symfony\Component\Console\Input\ArrayInput;

final class DevGenerateTestDataTest extends TestCase
{
    public function testRequiresExplicitScope(): void
    {
        $this->artisan('dev:generate-test-data')
            ->expectsOutput('Specify an OS (or all).')
            ->assertExitCode(1);
    }

    public function testVariantRequiresOs(): void
    {
        $this->artisan('dev:generate-test-data', ['os' => 'all', '--variant' => 'wifi'])
            ->expectsOutput('--variant requires an OS.')
            ->assertExitCode(1);
    }

    public function testAcceptsOsAsArgument(): void
    {
        $this->artisan('dev:generate-test-data', ['os' => 'nonexistent-os-xyz'])
            ->expectsOutput('No matching JSON test fixtures found for OS "nonexistent-os-xyz".')
            ->assertExitCode(1);
    }

    public function testOsArgumentCompletionIncludesAllAndOsList(): void
    {
        $command = new DevGenerateTestData;

        $completions = $command->completeArgument('os', 'al');
        $this->assertIsArray($completions);
        $this->assertContains('all', $completions);

        $completions = $command->completeArgument('os', 'io');
        $this->assertIsArray($completions);
        $this->assertContains('ios', $completions);
        $this->assertNotContains('all', $completions);
    }

    public function testVariantCompletionIsFilteredByOsArgument(): void
    {
        $command = new DevGenerateTestData;
        $input = new ArrayInput(['os' => 'ios']);
        $input->bind($command->getDefinition());
        $option = $command->getDefinition()->getOption('variant');
        $this->assertInstanceOf(DynamicInputOption::class, $option);

        $completions = $command->completeOptionValue(
            $option,
            'c35',
            $input,
        );

        $this->assertContains('c3560', $completions);
        $this->assertNotContains('debian', $completions);
    }

    public function testVariantCompletionIncludesSnmprecWithoutJson(): void
    {
        $command = new DevGenerateTestData;
        $input = new ArrayInput(['os' => 'routeros']);
        $input->bind($command->getDefinition());
        $option = $command->getDefinition()->getOption('variant');
        $this->assertInstanceOf(DynamicInputOption::class, $option);

        $completions = $command->completeOptionValue(
            $option,
            'rbl',
            $input,
        );

        $this->assertContains('rblhgr', $completions);
    }
}
