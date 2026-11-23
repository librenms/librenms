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
use LibreNMS\Enum\SecretType;
use LibreNMS\Enum\Severity;
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

    private function resetDeviceScope(): void
    {
        $this->app->forgetScopedInstances();
        DeviceCache::clearResolvedInstance('device-cache');
    }
}
