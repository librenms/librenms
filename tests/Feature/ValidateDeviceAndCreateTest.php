<?php

namespace LibreNMS\Tests\Feature;

use App\Actions\Device\BuildDefaultPollingMethods;
use App\Actions\Device\ValidateDeviceAndCreate;
use App\Models\Device;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Tests\DBTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class ValidateDeviceAndCreateTest extends DBTestCase
{
    use DatabaseTransactions;

    /**
     * @param  array<string, array<string, mixed>>  $methods
     */
    #[DataProvider('osProvider')]
    public function testOsWithoutSnmpIsPing(array $methods, ?string $os, string $expected): void
    {
        $device = new Device(['hostname' => 'os-test.example.com', 'os' => $os]);
        $pollingMethods = app(BuildDefaultPollingMethods::class)->execute($device, ['methods' => $methods]);

        $this->assertTrue((new ValidateDeviceAndCreate($device, $pollingMethods, force: true))->execute());
        $this->assertSame($expected, $device->fresh()->os);
    }

    public static function osProvider(): array
    {
        return [
            'icmp only' => [['icmp' => ['active' => true]], null, 'ping'],
            'icmp only with os' => [['icmp' => ['active' => true]], 'linux', 'linux'],
            'snmp disabled' => [['icmp' => ['active' => true], 'snmp' => ['active' => true, 'enabled' => false]], null, 'ping'],
            'snmp' => [['icmp' => ['active' => true], 'snmp' => ['active' => true]], null, 'generic'],
        ];
    }
}
