<?php

namespace LibreNMS\Tests\Feature;

use App\Actions\Device\BuildDefaultPollingMethods;
use App\Actions\Device\ValidateDeviceAndCreate;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Secret;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Data\Source\Icmp\Fping;
use LibreNMS\Data\Source\Icmp\FpingResponse;
use LibreNMS\Data\Source\Snmp\RawSnmpResponse;
use LibreNMS\Data\Source\Snmp\SnmpBackendInterface;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\SecretType;
use LibreNMS\Exceptions\HostSysnameExistsException;
use LibreNMS\Exceptions\MissingSecretException;
use LibreNMS\Tests\DBTestCase;
use Mockery;
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
        $secret = Secret::factory()->create(['secret_type' => SecretType::Snmp]);
        LibrenmsConfig::set('snmp.default_credentials', [$secret->id]); // used by forced snmp

        $device = new Device(['hostname' => 'os-test.example.com', 'os' => $os]);
        $pollingMethods = app(BuildDefaultPollingMethods::class)->execute($device, ['methods' => $methods]);

        $this->assertTrue(resolve(ValidateDeviceAndCreate::class)->execute($device, $pollingMethods, force: true));
        $this->assertSame($expected, $device->fresh()->os);
    }

    public function testDefaultCredentialsAreTriedInOrderAndTheWorkingOneIsKept(): void
    {
        $wrong = Secret::factory()->create(['secret_type' => SecretType::Snmp, 'data' => ['version' => 'v2c', 'community' => 'wrong']]);
        $correct = Secret::factory()->create(['secret_type' => SecretType::Snmp, 'data' => ['version' => 'v2c', 'community' => 'correct']]);
        LibrenmsConfig::set('snmp.default_credentials', [$wrong->id, $correct->id]);

        $fping = Mockery::mock(Fping::class);
        $fping->shouldReceive('ping')->andReturn(FpingResponse::artificialUp());
        $this->app->instance(Fping::class, $fping);

        $tried = [];
        $backend = Mockery::mock(SnmpBackendInterface::class);
        $backend->shouldReceive('get')->andReturnUsing(function ($target, $oids, $config) use (&$tried) {
            $tried[] = $config->community;

            return $config->community === 'correct'
                ? new RawSnmpResponse('SNMPv2-MIB::sysObjectID.0 = OID: SNMPv2-SMI::enterprises.9.1.1', '', 0)
                : new RawSnmpResponse('', 'Timeout', 1);
        });
        $this->app->instance(SnmpBackendInterface::class, $backend);

        $device = new Device(['hostname' => 'detect.example.com']);

        $this->assertTrue(app(ValidateDeviceAndCreate::class)->execute($device));
        $this->assertSame(['wrong', 'correct'], array_values(array_unique($tried)));
        $this->assertSame($correct->id, $device->pollingMethod(PollingMethodType::Snmp)?->secret_id);
    }

    public function testTheFirstWorkingDefaultCredentialIsPreferred(): void
    {
        $v3 = Secret::factory()->create(['secret_type' => SecretType::Snmp, 'data' => ['version' => 'v3', 'authlevel' => 'authPriv', 'authname' => 'preferred', 'authpass' => 'authpass', 'cryptopass' => 'cryptopass']]);
        $v2c = Secret::factory()->create(['secret_type' => SecretType::Snmp, 'data' => ['version' => 'v2c', 'community' => 'public']]);
        LibrenmsConfig::set('snmp.default_credentials', [$v3->id, $v2c->id]);
        $this->mockFpingUp();
        $this->mockSnmpSysName('both-versions'); // the device answers v3 and v2c

        $device = new Device(['hostname' => 'both-versions.example.com']);

        $this->assertTrue(app(ValidateDeviceAndCreate::class)->execute($device));
        $this->assertSame($v3->id, $device->pollingMethod(PollingMethodType::Snmp)?->secret_id);
    }

    public function testForcedSnmpWithoutCredentialsUsesFirstDefaultCredential(): void
    {
        $first = Secret::factory()->create(['secret_type' => SecretType::Snmp]);
        $second = Secret::factory()->create(['secret_type' => SecretType::Snmp]);
        LibrenmsConfig::set('snmp.default_credentials', [$second->id, $first->id]);

        $device = new Device(['hostname' => 'forced.example.com']);
        $pollingMethods = app(BuildDefaultPollingMethods::class)->execute($device);

        $this->assertTrue(app(ValidateDeviceAndCreate::class)->execute($device, $pollingMethods, force: true));
        $this->assertSame($second->id, $device->pollingMethod(PollingMethodType::Snmp)?->secret_id);
    }

    public function testForcedSnmpWithoutAnyCredentialsIsRejected(): void
    {
        LibrenmsConfig::set('snmp.default_credentials', []);

        $device = new Device(['hostname' => 'no-credentials.example.com']);
        $pollingMethods = app(BuildDefaultPollingMethods::class)->execute($device);

        $this->expectException(MissingSecretException::class);
        app(ValidateDeviceAndCreate::class)->execute($device, $pollingMethods, force: true);
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

        $this->assertTrue(app(ValidateDeviceAndCreate::class)->execute($device, $pollingMethods, uncheckedMethods: [PollingMethodType::Snmp]));
        $snmp = $device->pollingMethod(PollingMethodType::Snmp);
        $this->assertNull($snmp?->last_check_successful);
        $this->assertSame($default->id, $snmp?->secret_id);
    }

    public function testPingOnlyDeviceIsNotCheckedForDuplicateSysName(): void
    {
        LibrenmsConfig::set('allow_duplicate_sysName', false);
        Device::factory()->create(['hostname' => '10.0.0.1', 'sysName' => 'router1']);
        $this->mockFpingUp();

        $device = new Device(['hostname' => 'router1']);
        $pollingMethods = app(BuildDefaultPollingMethods::class)->execute($device, ['methods' => ['icmp' => ['active' => true]]]);

        $this->assertTrue(app(ValidateDeviceAndCreate::class)->execute($device, $pollingMethods));
        $this->assertSame('router1', $device->fresh()->sysName); // defaults to the hostname
    }

    public function testGivenSysNameIsCheckedForDuplicates(): void
    {
        LibrenmsConfig::set('allow_duplicate_sysName', false);
        Device::factory()->create(['hostname' => '10.0.0.1', 'sysName' => 'router1']);
        $this->mockFpingUp();

        $device = new Device(['hostname' => 'ping-device', 'sysName' => 'router1']);
        $pollingMethods = app(BuildDefaultPollingMethods::class)->execute($device, ['methods' => ['icmp' => ['active' => true]]]);

        $this->expectException(HostSysnameExistsException::class);
        app(ValidateDeviceAndCreate::class)->execute($device, $pollingMethods);
    }

    public function testEmptySnmpSysNameIsNotCheckedForDuplicates(): void
    {
        LibrenmsConfig::set('allow_duplicate_sysName', false);
        Device::factory()->create(['hostname' => '10.0.0.1', 'sysName' => 'router1']);
        $this->mockFpingUp();
        $this->mockSnmpSysName('');

        $device = new Device(['hostname' => 'router1']);
        $pollingMethods = app(BuildDefaultPollingMethods::class)->execute($device, ['methods' => [
            'snmp' => ['active' => true, 'secret_data' => ['version' => 'v2c', 'community' => 'public']],
        ]]);

        $this->assertTrue(app(ValidateDeviceAndCreate::class)->execute($device, $pollingMethods));
        $this->assertSame('router1', $device->fresh()->sysName); // defaults to the hostname
    }

    public function testDuplicateSnmpSysNameIsRejected(): void
    {
        LibrenmsConfig::set('allow_duplicate_sysName', false);
        Device::factory()->create(['hostname' => '10.0.0.1', 'sysName' => 'router1']);
        $this->mockFpingUp();
        $this->mockSnmpSysName('router1');

        $device = new Device(['hostname' => 'router1.example.com']);
        $pollingMethods = app(BuildDefaultPollingMethods::class)->execute($device, ['methods' => [
            'snmp' => ['active' => true, 'secret_data' => ['version' => 'v2c', 'community' => 'public']],
        ]]);

        $this->expectException(HostSysnameExistsException::class);
        app(ValidateDeviceAndCreate::class)->execute($device, $pollingMethods);
    }

    private function mockFpingUp(): void
    {
        $fping = Mockery::mock(Fping::class);
        $fping->shouldReceive('ping')->andReturn(FpingResponse::artificialUp());
        $this->app->instance(Fping::class, $fping);
    }

    /**
     * The device answers SNMP with this sysName.
     */
    private function mockSnmpSysName(string $sysName): void
    {
        $backend = Mockery::mock(SnmpBackendInterface::class);
        $backend->shouldReceive('get')->andReturnUsing(fn ($target, $oids) => in_array('SNMPv2-MIB::sysName.0', $oids)
            ? new RawSnmpResponse("SNMPv2-MIB::sysName.0 = $sysName", '', 0)
            : new RawSnmpResponse('SNMPv2-MIB::sysObjectID.0 = .1.3.6.1.4.1.8072.3.2.10', '', 0));
        $backend->shouldIgnoreMissing(new RawSnmpResponse('', '', 0));
        $this->app->instance(SnmpBackendInterface::class, $backend);
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
