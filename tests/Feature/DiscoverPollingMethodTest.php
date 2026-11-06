<?php

namespace LibreNMS\Tests\Feature;

use App\Actions\Device\BuildPollingMethodCandidates;
use App\Actions\Device\DiscoverPollingMethod;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Secret;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Data\Source\Snmp\RawSnmpResponse;
use LibreNMS\Data\Source\Snmp\SnmpBackendInterface;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\SecretType;
use LibreNMS\Polling\Method\Config\SnmpConfig;
use LibreNMS\Tests\DBTestCase;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;

final class DiscoverPollingMethodTest extends DBTestCase
{
    use DatabaseTransactions;

    /**
     * @param  array<string, mixed>  $settings
     * @param  string[]  $expected  the versions tried
     */
    #[DataProvider('givenSecretProvider')]
    public function testANewDeviceTriesACommunityAsV1(SecretType $secretType, array $settings, bool $exists, array $expected): void
    {
        $probed = [];
        $this->fakeSnmp($probed, fn (SnmpConfig $config): bool => $config->version === 'v1');

        $device = new Device(['hostname' => '192.0.2.1']);
        $device->exists = $exists;
        $deviceMethod = new DevicePollingMethod(['method_type' => PollingMethodType::Snmp, 'settings' => $settings]);
        $deviceMethod->setRelation('secret', new Secret([
            'description' => 'given',
            'secret_type' => $secretType,
            'data' => $secretType === SecretType::SnmpV3 ? ['authlevel' => 'noAuthNoPriv', 'authname' => 'user'] : ['community' => 'public'],
        ]));

        $result = app(DiscoverPollingMethod::class)->execute($device, $deviceMethod);

        $this->assertSame($expected, array_column($probed, 0));
        $this->assertSame(end($expected) === 'v1', $result->isSuccess());
        // a v1 reply is saved as the version, otherwise the settings are unchanged
        $this->assertSame($result->isSuccess() ? [...$settings, 'version' => 'v1'] : $settings, $deviceMethod->settings);
    }

    /**
     * @return array<string, array{SecretType, array<string, mixed>, bool, string[]}>
     */
    public static function givenSecretProvider(): array
    {
        return [
            'new device, community' => [SecretType::SnmpCommunity, ['port' => 1161], false, ['v2c', 'v1']],
            'new device, community, given version' => [SecretType::SnmpCommunity, ['version' => 'v2c'], false, ['v2c']],
            'new device, v3' => [SecretType::SnmpV3, [], false, ['v3']],
            'existing device keeps its version' => [SecretType::SnmpCommunity, [], true, ['v2c']],
        ];
    }

    public function testDefaultCredentialsTryCommunitiesAsV1Last(): void
    {
        $public = Secret::create(['description' => 'public', 'secret_type' => SecretType::SnmpCommunity, 'data' => ['community' => 'public']]);
        $v3 = Secret::create(['description' => 'v3', 'secret_type' => SecretType::SnmpV3, 'data' => ['authlevel' => 'noAuthNoPriv', 'authname' => 'user']]);
        $private = Secret::create(['description' => 'private', 'secret_type' => SecretType::SnmpCommunity, 'data' => ['community' => 'private']]);
        LibrenmsConfig::set('snmp.default_credentials', [$public->id, $v3->id, $private->id]);

        $probed = [];
        $this->fakeSnmp($probed, fn (SnmpConfig $config): bool => $config->version === 'v1' && $config->community === 'private');

        $deviceMethod = new DevicePollingMethod(['method_type' => PollingMethodType::Snmp]);
        $result = app(DiscoverPollingMethod::class)->execute(new Device(['hostname' => '192.0.2.1']), $deviceMethod);

        $this->assertTrue($result->isSuccess());
        $this->assertSame([['v2c', 'public'], ['v3', null], ['v2c', 'private'], ['v1', 'public'], ['v1', 'private']], $probed);
        $this->assertSame($private->id, $deviceMethod->secret_id);
        $this->assertSame($private->id, $deviceMethod->secret?->id);
        $this->assertSame(['version' => 'v1'], $deviceMethod->settings);
    }

    public function testFailureListsEveryCandidate(): void
    {
        $public = Secret::create(['description' => 'Default public', 'secret_type' => SecretType::SnmpCommunity, 'data' => ['community' => 'public']]);
        LibrenmsConfig::set('snmp.default_credentials', [$public->id]);
        $probed = [];
        $this->fakeSnmp($probed, fn (): bool => false);

        $deviceMethod = new DevicePollingMethod(['method_type' => PollingMethodType::Snmp]);
        $result = app(DiscoverPollingMethod::class)->execute(new Device(['hostname' => '192.0.2.1']), $deviceMethod);

        $this->assertFalse($result->isSuccess());
        $this->assertSame([
            'SNMP v2c: No reply using credential "Default public"',
            'SNMP v1: No reply using credential "Default public"',
        ], $result->reasons());
        $this->assertNull($deviceMethod->secret_id, 'nothing worked, the method is unchanged');
        $this->assertNull($deviceMethod->settings);
    }

    public function testOtherMethodsAreTriedAsTheyAre(): void
    {
        $deviceMethod = new DevicePollingMethod(['method_type' => PollingMethodType::Ipmi, 'settings' => ['port' => 623]]);

        $candidates = app(BuildPollingMethodCandidates::class)->execute(new Device, $deviceMethod);

        $this->assertCount(1, $candidates);
        $this->assertNotSame($deviceMethod, $candidates[0], 'a copy, the method is not changed');
        $this->assertSame(['port' => 623], $candidates[0]->settings);
    }

    /**
     * @param  list<array{string, ?string}>  $probed  filled with the version and community of each probe
     * @param  callable(SnmpConfig): bool  $replies
     */
    private function fakeSnmp(array &$probed, callable $replies): void
    {
        $backend = Mockery::mock(SnmpBackendInterface::class);
        $backend->shouldReceive('get')->andReturnUsing(function ($target, $oids, SnmpConfig $config) use (&$probed, $replies) {
            $probed[] = [$config->version, $config->community];

            return $replies($config)
                ? new RawSnmpResponse('SNMPv2-MIB::sysObjectID.0 = .1.3.6.1.4.1.8072.3.2.10', '', 0)
                : new RawSnmpResponse('', 'Timeout: No Response from udp:192.0.2.1:161.', 1);
        });
        $this->app->instance(SnmpBackendInterface::class, $backend);
    }
}
