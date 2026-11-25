<?php

namespace LibreNMS\Tests\Unit\Polling;

use App\Actions\Device\BuildDefaultPollingMethods;
use App\Actions\Device\ResolvePollingMethodSecret;
use App\Facades\LibrenmsConfig;
use App\Models\Device;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Polling\Method\Definitions\IcmpDefinition;
use LibreNMS\Polling\Method\Methods\MtuPollingMethod;
use LibreNMS\Polling\Method\Methods\PollingMethod;
use LibreNMS\Polling\Method\PollingMethodRegistry;
use LibreNMS\Tests\TestCase;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;

final class BuildDefaultPollingMethodsTest extends TestCase
{
    /**
     * @param  list<PollingMethodType>  $expected
     */
    #[DataProvider('defaultMethodsProvider')]
    public function testDefaultMethodsFollowConfiguration(bool $icmp, int $mtu, array $expected): void
    {
        LibrenmsConfig::set('icmp_check', $icmp);
        LibrenmsConfig::set('mtu_options.bytes', $mtu);
        $device = new Device(['hostname' => 'example.com']);

        $methods = app(BuildDefaultPollingMethods::class)->execute($device);

        $this->assertSame($expected, $methods->pluck('method_type')->all());
        foreach ($methods as $method) {
            $this->assertTrue($method->enabled);
            $this->assertSame($device, $method->device);
            $this->assertSame([], $method->settings);
        }
    }

    /**
     * @return array<string, array{bool, int, list<PollingMethodType>}>
     */
    public static function defaultMethodsProvider(): array
    {
        return [
            'icmp enabled, mtu disabled' => [true, 0, [PollingMethodType::Icmp, PollingMethodType::Snmp]],
            'icmp enabled, mtu positive' => [true, 1, [PollingMethodType::Icmp, PollingMethodType::Snmp, PollingMethodType::Mtu]],
            'icmp enabled, mtu negative' => [true, -1, [PollingMethodType::Icmp, PollingMethodType::Snmp]],
            'icmp disabled, mtu positive' => [false, 1500, [PollingMethodType::Snmp]],
            'icmp disabled, mtu disabled' => [false, 0, [PollingMethodType::Snmp]],
        ];
    }

    public function testOtherMethodsHaveNoDefaultCreationInput(): void
    {
        $registry = app(PollingMethodRegistry::class);

        foreach ([PollingMethodType::Ipmi, PollingMethodType::ProxmoxApi, PollingMethodType::UnixAgent, PollingMethodType::Unknown] as $type) {
            $this->assertNull($registry->get($type)->defaultCreationInput());
        }
    }

    public function testMtuDefaultCreationInput(): void
    {
        $method = new MtuPollingMethod;
        LibrenmsConfig::set('icmp_check', true);
        LibrenmsConfig::forget('mtu_options.bytes');
        $this->assertNull($method->defaultCreationInput());

        LibrenmsConfig::set('mtu_options.bytes', 0);
        $this->assertNull($method->defaultCreationInput());

        LibrenmsConfig::set('mtu_options.bytes', -1);
        $this->assertNull($method->defaultCreationInput());

        LibrenmsConfig::set('mtu_options.bytes', 1500);
        $this->assertSame(['active' => true], $method->defaultCreationInput());

        LibrenmsConfig::set('icmp_check', false);
        $this->assertSame(['active' => false], $method->defaultCreationInput());
    }

    public function testDefaultsAreCollectedFromRegisteredMethods(): void
    {
        LibrenmsConfig::set('icmp_check', false);
        $extraMethod = Mockery::mock(PollingMethod::class)->makePartial();
        $extraMethod->shouldReceive('defaultCreationInput')->once()->andReturn([
            'active' => true,
            'enabled' => false,
            'settings' => ['ip_version' => 'ipv6'],
        ]);
        $extraMethod->shouldReceive('definition')->andReturn(new IcmpDefinition);
        $extraMethod->shouldReceive('defaultAffectsAvailability')->andReturn(false);

        $actualRegistry = app(PollingMethodRegistry::class);
        $registry = Mockery::mock(PollingMethodRegistry::class);
        $registry->shouldReceive('get')->andReturnUsing(
            fn (PollingMethodType $type): PollingMethod => $type === PollingMethodType::UnixAgent ? $extraMethod : $actualRegistry->get($type),
        );
        $builder = new BuildDefaultPollingMethods($registry, new ResolvePollingMethodSecret($registry));

        $methods = $builder->execute(new Device(['hostname' => 'example.com']));
        $this->assertSame([PollingMethodType::UnixAgent, PollingMethodType::Snmp], $methods->pluck('method_type')->all());
        $this->assertFalse($methods->first()->enabled);
        $this->assertSame(['ip_version' => 'ipv6'], $methods->first()->settings);
    }

    public function testExplicitMethodsReplaceDefaultsAndRetainTheirInput(): void
    {
        LibrenmsConfig::set('icmp_check', true);
        LibrenmsConfig::set('mtu_options.bytes', 1500);
        $device = new Device(['hostname' => 'example.com']);

        $methods = app(BuildDefaultPollingMethods::class)->execute($device, ['methods' => [
            'snmp' => ['active' => false],
            'icmp' => ['active' => true, 'enabled' => false, 'affects_availability' => false, 'settings' => ['ip_version' => 'ipv6']],
        ]]);

        $this->assertSame([PollingMethodType::Icmp], $methods->pluck('method_type')->all());
        $method = $methods->first();
        $this->assertFalse($method->enabled);
        $this->assertFalse($method->affects_availability);
        $this->assertSame(['ip_version' => 'ipv6'], $method->settings);
        $this->assertSame([], app(BuildDefaultPollingMethods::class)->execute($device, ['methods' => []])->all());
    }
}
