<?php

namespace LibreNMS\Tests\Feature\Console;

use App\Console\Commands\DevGenerateTestData;
use App\Console\DynamicInputOption;
use LibreNMS\Tests\TestCase;
use LibreNMS\Tests\Traits\FakesTestFixtures;
use Symfony\Component\Console\Input\ArrayInput;

final class DevGenerateTestDataTest extends TestCase
{
    use FakesTestFixtures;

    public function testRequiresExplicitScope(): void
    {
        $this->artisan('dev:generate-test-data', ['--no-interaction' => true])
            ->expectsOutput('Specify an OS (or all).')
            ->assertExitCode(1);
    }

    public function testVariantRequiresOs(): void
    {
        $this->artisan('dev:generate-test-data', ['os' => 'all', '--variant' => 'wrt54g'])
            ->expectsOutput('--variant requires an OS.')
            ->assertExitCode(1);
    }

    public function testUnknownOs(): void
    {
        $this->fakeTestFixtures();

        $this->artisan('dev:generate-test-data', ['os' => 'linksys'])
            ->expectsOutput("Unknown OS 'linksys'.")
            ->assertExitCode(1);
    }

    public function testInvalidOsCharacters(): void
    {
        $this->artisan('dev:generate-test-data', ['os' => '../foo'])
            ->expectsOutput("Unknown OS '../foo'.")
            ->assertExitCode(1);
    }

    public function testOsCaseIsFixed(): void
    {
        $this->fakeTestFixtures(['linksys_wrt54g'], ['linksys_wrt54g'], ['linksys']);

        $this->artisan('dev:generate-test-data', ['os' => 'LINKSYS', '--modules' => 'vlans', '--no-interaction' => true])
            ->expectsOutput('No matching JSON test fixtures found for OS "linksys".')
            ->assertExitCode(1);
    }

    public function testInvalidVariantWithUnderscore(): void
    {
        $this->fakeTestFixtures(['linksys_wrt54g'], osDefinitions: ['linksys']);

        $this->artisan('dev:generate-test-data', ['os' => 'linksys', '--variant' => 'wrt54g,wrt_54g'])
            ->expectsOutput('Variant name cannot contain an underscore (_).')
            ->assertExitCode(1);
    }

    public function testInvalidVariantCharacters(): void
    {
        $this->fakeTestFixtures(['linksys_wrt54g'], osDefinitions: ['linksys']);

        $this->artisan('dev:generate-test-data', ['os' => 'linksys', '--variant' => '../foo'])
            ->expectsOutput('Variant name can only contain letters, numbers, dots (.), plus (+), and dashes (-).')
            ->assertExitCode(1);
    }

    public function testAcceptsOsWithOnlyFixtures(): void
    {
        // OS names like arubaos-cx_6300_vsf only exist as fixture names, without an OS definition
        $this->fakeTestFixtures(['linksys_wrt54g']);

        $this->artisan('dev:generate-test-data', ['os' => 'linksys', '--variant' => 'wrt54gl'])
            ->expectsOutput("There is no snmprec file for 'linksys_wrt54gl'. Collect it with lnms dev:collect-snmprec first.")
            ->assertExitCode(1);
    }

    public function testMissingSnmprec(): void
    {
        $this->fakeTestFixtures(['linksys'], osDefinitions: ['linksys']);

        $this->artisan('dev:generate-test-data', ['os' => 'linksys', '--variant' => 'wrt54g'])
            ->expectsOutput("There is no snmprec file for 'linksys_wrt54g'. Collect it with lnms dev:collect-snmprec first.")
            ->assertExitCode(1);
    }

    public function testOsArgumentCompletionIncludesAllAndOsList(): void
    {
        $this->fakeTestFixtures(['linksys', 'linksys_wrt54g']);
        $command = new DevGenerateTestData;

        $completions = $command->completeArgument('os', 'al');
        $this->assertIsArray($completions);
        $this->assertContains('all', $completions);

        $completions = $command->completeArgument('os', 'li');
        $this->assertIsArray($completions);
        $this->assertSame(['linksys'], $completions);
    }

    public function testVariantCompletionIsFilteredByOsArgument(): void
    {
        $this->fakeTestFixtures(['linksys_wrt54g', 'openwrt_wrt54gl']);
        $command = new DevGenerateTestData;
        $input = new ArrayInput(['os' => 'linksys']);
        $input->bind($command->getDefinition());
        $option = $command->getDefinition()->getOption('variant');
        $this->assertInstanceOf(DynamicInputOption::class, $option);

        $completions = $command->completeOptionValue(
            $option,
            'wrt',
            $input,
        );

        $this->assertContains('wrt54g', $completions);
        $this->assertNotContains('wrt54gl', $completions);
    }

    public function testVariantCompletionIncludesSnmprecWithoutJson(): void
    {
        $this->fakeTestFixtures(['linksys_wrt54g', 'linksys_wrt54gl'], ['linksys_wrt54g']);
        $command = new DevGenerateTestData;
        $input = new ArrayInput(['os' => 'linksys']);
        $input->bind($command->getDefinition());
        $option = $command->getDefinition()->getOption('variant');
        $this->assertInstanceOf(DynamicInputOption::class, $option);

        $completions = $command->completeOptionValue(
            $option,
            'wrt54',
            $input,
        );

        $this->assertContains('wrt54gl', $completions);
    }
}
