<?php

namespace LibreNMS\Tests\Feature;

use App\Facades\DeviceCache;
use App\Facades\LibrenmsConfig;
use App\Jobs\DiscoverDevice;
use App\Jobs\PollDevice;
use App\Models\Device;
use App\Models\DevicePollingMethod;
use App\Models\Secret;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Data\Source\Icmp\Fping;
use LibreNMS\Data\Source\Icmp\FpingResponse;
use LibreNMS\Data\Source\Snmp\SnmpQueryBuilder;
use LibreNMS\Data\Source\Snmp\SnmpResponse;
use LibreNMS\Enum\SecretType;
use LibreNMS\Enum\Severity;
use LibreNMS\Exceptions\SnmpException;
use LibreNMS\OS;
use LibreNMS\Tests\DBTestCase;
use LibreNMS\Util\ModuleList;

final class HarmonicCableosRpdTest extends DBTestCase
{
    use DatabaseTransactions;

    public function testRpdStatesArePolledAndRediscoveredWithoutChangingIdentity(): void
    {
        $this->requireSnmpsim();
        LibrenmsConfig::set('rrd.enable', false);
        $this->mock(Fping::class)->shouldReceive('ping')->andReturn(FpingResponse::artificialUp());

        $device = Device::factory()->create([
            'hostname' => $this->getSnmpsimIp(),
            'os' => 'harmonic-cableos',
            'status' => 1,
        ]);
        $secret = Secret::factory()->create([
            'secret_type' => SecretType::SnmpCommunity,
            'data' => ['community' => 'harmonic-cableos_cloud-native'],
        ]);
        DevicePollingMethod::factory()->for($device)->for($secret)->create([
            'settings' => ['transport' => 'udp', 'port' => $this->getSnmpsimPort()],
        ]);

        $modules = ModuleList::fromUserOverrides(['sensors']);
        new DiscoverDevice($device->device_id, $modules)->handle();

        $states = $device->sensors()->where('sensor_class', 'state')->orderBy('sensor_index');
        $discovered = $states->get();
        $this->assertCount(9, $discovered);
        foreach ($discovered as $offset => $sensor) {
            $index = $offset + 1;
            $this->assertSame(sprintf('RPD 02:00:00:00:00:%02x', $index), $sensor->sensor_descr);
            $this->assertSame('hrmCmtsRpdOperStatus', $sensor->sensor_type);
            $this->assertSame((string) $index, $sensor->sensor_index);
            $this->assertSame(".1.3.6.1.4.1.1563.10.2.3.116.1.7.1.1.3.$index", $sensor->sensor_oid);
        }

        // Existing installations have the same identity without the new polling callback.
        $states->update(['user_func' => null]);
        $this->resetDeviceScope();
        new DiscoverDevice($device->device_id, $modules)->handle();
        $this->assertSame($discovered->modelKeys(), $states->get()->modelKeys());

        [$online, $missing, $invalid] = (clone $states)->with('translations')->take(3)->get()->all();
        $online->update(['sensor_current' => 0]);
        $missing->update(['sensor_oid' => $missing->sensor_oid . '.99', 'sensor_current' => 0]);
        $invalid->update(['sensor_oid' => '.1.3.6.1.2.1.1.1.0', 'sensor_current' => 3]);

        $this->resetDeviceScope();
        new PollDevice($device->device_id, $modules)->handle();
        $this->assertSame('Online', $online->refresh()->formatValue());
        foreach ([$missing, $invalid] as $sensor) {
            $this->assertSame('Unknown', $sensor->refresh()->formatValue());
            $this->assertSame(Severity::Unknown, $sensor->currentStatus());
        }

        // Exercise the polling persistence path with every vendor enum and malformed values.
        $samples = [
            ['0', 'Offline', Severity::Error],
            ['1', 'Identifying', Severity::Warning],
            ['2', 'Configuring', Severity::Warning],
            ['3', 'Online', Severity::Ok],
            ['4', 'Identification failed', Severity::Error],
            ['5', 'Configuration failed', Severity::Error],
            ['6', 'Discovered', Severity::Warning],
            ['7', 'Unknown', Severity::Unknown],
            ['0', 'Offline', Severity::Error],
            ['', 'Unknown', Severity::Unknown],
            ['3.0', 'Unknown', Severity::Unknown],
            ['0', 'Offline', Severity::Error],
        ];
        foreach ($samples as [$raw, $label, $severity]) {
            record_sensor_data($device->toArray(), [$online->getAttributes() + ['new_value' => $raw]]);
            $this->assertSame($label, $online->refresh()->formatValue());
            $this->assertSame($severity, $online->currentStatus());
        }

        $this->resetDeviceScope();
        new DiscoverDevice($device->device_id, $modules)->handle();
        $this->assertSame($discovered->modelKeys(), $states->get()->modelKeys());
        $this->assertSame($discovered->pluck('sensor_oid')->all(), $states->pluck('sensor_oid')->all());
    }

    public function testRpdWalkConnectionErrorsDoNotSynchronizeStateSensors(): void
    {
        $device = Device::factory()->create(['os' => 'harmonic-cableos']);
        $deviceArray = $device->toArray();
        $os = OS::make($deviceArray);
        $entry = '.1.3.6.1.4.1.1563.10.2.3.116.1.7.1.1';
        $firstRpd = ["$entry.2.1" => '02 00 00 00 00 01', "$entry.3.1" => '3'];
        $secondRpd = ["$entry.2.2" => '02 00 00 00 00 02', "$entry.3.2" => '3'];
        $timeout = 'Timeout: No Response from udp:192.0.2.1:161';

        $query = $this->mock(SnmpQueryBuilder::class);
        $query->shouldReceive('options')->with('-OQx')->andReturnSelf();
        $query->shouldReceive('numeric')->andReturnSelf();
        $query->shouldReceive('displayHints')->with(false)->andReturnSelf();
        $query->shouldReceive('walk')->with($entry)->times(4)->andReturn(
            new SnmpResponse($firstRpd + $secondRpd),
            new SnmpResponse([], $timeout, 1),
            new SnmpResponse(["$entry.2.1" => $firstRpd["$entry.2.1"], "$entry.3.1" => '0'], $timeout, 1),
            new SnmpResponse,
        );

        DeviceCache::setPrimary($device->device_id);
        sensors(['state'], $os);
        $states = $device->sensors()->where('sensor_class', 'state')->orderBy('sensor_index');
        $discovered = $states->get()->toArray();
        $this->assertCount(2, $discovered);

        foreach (['empty timed-out walk', 'partially returned timed-out walk'] as $case) {
            $this->resetDeviceScope();
            DeviceCache::setPrimary($device->device_id);
            try {
                sensors(['state'], $os);
                $this->fail("Expected a connection exception for $case");
            } catch (SnmpException $exception) {
                $this->assertSame($timeout, $exception->getMessage());
            }
            $this->assertSame($discovered, $states->get()->toArray(), $case);
        }

        $this->resetDeviceScope();
        DeviceCache::setPrimary($device->device_id);
        sensors(['state'], $os);
        $this->assertSame(0, $states->count());
    }

    private function resetDeviceScope(): void
    {
        $this->app->forgetScopedInstances();
        DeviceCache::clearResolvedInstance('device-cache');
    }
}
