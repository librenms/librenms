<?php

namespace LibreNMS\Tests\Feature\Commands;

use App\Models\Device;
use Illuminate\Support\Facades\Artisan;
use LibreNMS\Tests\InMemoryDbTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class BashCompletionDeviceTest extends InMemoryDbTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Device::factory()->create(['hostname' => 'amber.example.com']);
        Device::factory()->create(['hostname' => 'amethyst.example.com']);
        Device::factory()->create(['hostname' => 'bronze.example.com']);
    }

    protected function tearDown(): void
    {
        putenv('COMP_LINE');
        putenv('COMP_CURRENT');
        putenv('COMP_PREVIOUS');

        parent::tearDown();
    }

    public static function deviceCommands(): array
    {
        return [
            ['device:poll'],
            ['device:discover'],
            ['device:ping'],
            ['device:remove'],
            ['device:rename'],
            ['port:tune'],
            ['snmp:get'],
            ['report:devices'],
        ];
    }

    #[DataProvider('deviceCommands')]
    public function testCompletesDeviceHostnamePrefix(string $command): void
    {
        $this->assertSame(['amber.example.com', 'amethyst.example.com'], $this->complete("lnms $command am", 'am', $command));
    }

    public function testCompletesAllDevicesWhenEmpty(): void
    {
        $this->assertSame(
            ['amber.example.com', 'amethyst.example.com', 'bronze.example.com'],
            $this->complete('lnms device:poll ', '', 'device:poll')
        );
    }

    public function testDoesNotCompleteDeviceForOptionValue(): void
    {
        $this->assertNotContains('amber.example.com', $this->complete('lnms device:poll amber.example.com -m am', 'am', '-m'));
    }

    public static function optionValueCommands(): array
    {
        return [
            ['lnms report:devices -o ', '-o', ['table', 'csv', 'json', 'none']],
            ['lnms device:poll -m ', '-m', null],
            ['lnms device:poll --os ', '--os', null],
            ['lnms device:discover -m ', '-m', null],
            ['lnms device:ping -g ', '-g', null],
        ];
    }

    #[DataProvider('optionValueCommands')]
    public function testDoesNotCompleteDeviceForEmptyOptionValueBeforeDeviceSpec(string $line, string $previous, ?array $expected): void
    {
        $completions = $this->complete($line, '', $previous);

        $this->assertNotContains('amber.example.com', $completions);
        if ($expected !== null) {
            $this->assertSame($expected, $completions);
        }
    }

    public function testDoesNotCompleteDeviceRenameNewHostname(): void
    {
        $this->assertNotContains('amber.example.com', $this->complete('lnms device:rename bronze.example.com am', 'am', 'bronze.example.com'));
    }

    /**
     * @return string[]
     */
    private function complete(string $line, string $current, string $previous): array
    {
        putenv("COMP_LINE=$line");
        putenv("COMP_CURRENT=$current");
        putenv("COMP_PREVIOUS=$previous");

        ob_start();
        Artisan::call('list:bash-completion');
        $output = ob_get_clean();

        return array_values(array_filter(explode(PHP_EOL, $output)));
    }
}
