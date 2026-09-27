<?php

namespace LibreNMS\Tests\Feature;

use App\Actions\Device\BuildDefaultPollingMethods;
use App\Actions\Device\ValidateDeviceAndCreate;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Secret;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\SecretType;
use LibreNMS\Exceptions\MissingSecretException;
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

    public function testForcedSnmpWithoutCredentialsUsesFirstDefaultCredential(): void
    {
        $first = Secret::factory()->create(['secret_type' => SecretType::Snmp]);
        $second = Secret::factory()->create(['secret_type' => SecretType::Snmp]);
        LibrenmsConfig::set('snmp.default_credentials', [$second->id, $first->id]);

        $device = new Device(['hostname' => 'forced.example.com']);
        $pollingMethods = app(BuildDefaultPollingMethods::class)->execute($device);

        $this->assertTrue((new ValidateDeviceAndCreate($device, $pollingMethods, force: true))->execute());
        $this->assertSame($second->id, $device->pollingMethod(PollingMethodType::Snmp)?->secret_id);
    }

    public function testForcedSnmpWithoutAnyCredentialsIsRejected(): void
    {
        LibrenmsConfig::set('snmp.default_credentials', []);

        $device = new Device(['hostname' => 'no-credentials.example.com']);
        $pollingMethods = app(BuildDefaultPollingMethods::class)->execute($device);

        $this->expectException(MissingSecretException::class);
        (new ValidateDeviceAndCreate($device, $pollingMethods, force: true))->execute();
    }

    public function testUncheckedMethodsAreNotDiscovered(): void
    {
        $default = Secret::factory()->create(['secret_type' => SecretType::Snmp]);
        LibrenmsConfig::set('snmp.default_credentials', [$default->id]);

        // an SNMP check of this host would fail and throw
        $device = new Device(['hostname' => 'unchecked.invalid']);
        $pollingMethods = app(BuildDefaultPollingMethods::class)->execute($device, ['methods' => [
            'icmp' => ['active' => true, 'enabled' => false],
            'snmp' => ['active' => true],
        ]]);

        $validator = new ValidateDeviceAndCreate($device, $pollingMethods, uncheckedMethods: [PollingMethodType::Snmp]);

        $this->assertTrue($validator->execute());
        $snmp = $device->pollingMethod(PollingMethodType::Snmp);
        $this->assertNull($snmp?->last_check_successful);
        $this->assertSame($default->id, $snmp?->secret_id);
    }

    /**
     * @return array<string, array{array<string, array<string, mixed>>, ?string, string}>
     */
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
