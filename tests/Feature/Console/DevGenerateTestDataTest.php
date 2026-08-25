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
            ->expectsOutput('Specify --all or --os.')
            ->assertExitCode(1);
    }

    public function testAllAndOsAreMutuallyExclusive(): void
    {
        $this->artisan('dev:generate-test-data', ['--all' => true, '--os' => 'routeros'])
            ->expectsOutput('--all and --os cannot be used together.')
            ->assertExitCode(1);
    }

    public function testVariantRequiresOs(): void
    {
        $this->artisan('dev:generate-test-data', ['--all' => true, '--variant' => 'wifi'])
            ->expectsOutput('--variant requires --os.')
            ->assertExitCode(1);
    }

    public function testVariantCompletionIsFilteredByOs(): void
    {
        $command = new DevGenerateTestData;
        $input = new ArrayInput(['--os' => 'ios']);
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
        $input = new ArrayInput(['--os' => 'routeros']);
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
