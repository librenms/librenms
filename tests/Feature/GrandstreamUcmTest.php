<?php

namespace LibreNMS\Tests\Feature;

use App\Facades\DeviceCache;
use App\Models\Device;
use App\Models\Sensor;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use LibreNMS\Data\Source\Snmp\SnmpBackendInterface;
use LibreNMS\Data\Source\Snmp\SnmpResponse;
use LibreNMS\Modules\Core;
use LibreNMS\OS;
use LibreNMS\OS\GrandstreamUcm;
use LibreNMS\Tests\DBTestCase;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;

class GrandstreamUcmTest extends DBTestCase
{
    use DatabaseTransactions;

    private Device $device;
    private SnmpResponse $walkResponse;
    private SnmpResponse $getResponse;
    /** @var list<int|float> */
    private array $recordedValues = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->device = Device::factory()->create([
            'hostname' => '127.0.0.1', 'os' => 'grandstream-ucm',
            'disabled' => 0,
        ])->refresh();
        DeviceCache::setPrimary($this->device->device_id);
        $this->walkResponse = $this->trunkResponse('Registered');
        $this->getResponse = new SnmpResponse([GrandstreamUcm::TRUNK_OID_PREFIX . '1' => 'Registered']);

        $backend = Mockery::mock(SnmpBackendInterface::class);
        $backend->shouldReceive('walk')->andReturnUsing(
            fn ($target, $oid) => $oid === GrandstreamUcm::TRUNK_TABLE ? $this->walkResponse : new SnmpResponse
        );
        $backend->shouldReceive('get')->andReturnUsing(fn () => $this->getResponse);
        $this->app->instance(SnmpBackendInterface::class, $backend);
        $datastore = Mockery::mock();
        $datastore->shouldReceive('put')->andReturnUsing(function ($device, $measurement, $tags, $fields): void {
            $this->recordedValues[] = $fields['sensor'];
        });
        $this->app->instance('Datastore', $datastore);
    }

    private function trunkResponse(?string $status): SnmpResponse
    {
        $values = [GrandstreamUcm::TRUNK_NAME . '.1' => 'Test Trunk'];
        if ($status !== null) {
            $values[GrandstreamUcm::TRUNK_STATUS . '.1'] = $status;
        }

        return new SnmpResponse($values);
    }

    private function discover(): ?Sensor
    {
        DeviceCache::flush();
        DeviceCache::setPrimary($this->device->device_id);
        $this->app->forgetInstance('sensor-discovery');
        $device = DeviceCache::getPrimary()->toArray();
        $os = OS::make($device);
        discovery_process($os, 'state', $os->preCache());
        app('sensor-discovery')->sync(sensor_class: 'state', poller_type: 'snmp');

        return $this->device->sensors()->where('sensor_class', 'state')->where('sensor_deleted', 0)->first();
    }

    #[DataProvider('statusProvider')]
    public function testDiscoveryAndPollingDoNotTreatUnmappedTextAsHealthy(?string $raw, int $expected): void
    {
        $this->walkResponse = $this->trunkResponse($raw);
        $sensor = $this->discover();
        $this->assertNotNull($sensor);
        $this->assertSame($expected, (int) $sensor->sensor_current);

        // Start polling from a known healthy value to verify the stored transition.
        $sensor->update(['sensor_current' => 0]);
        $this->getResponse = new SnmpResponse($raw === null ? [] : [GrandstreamUcm::TRUNK_OID_PREFIX . '1' => $raw]);
        poll_sensor($this->device->toArray(), 'state');
        $this->assertSame($expected, (int) $sensor->fresh()->sensor_current);
        $this->assertSame([$expected], $this->recordedValues);
    }

    /** @return array<string, array{?string, int}> */
    public static function statusProvider(): array
    {
        return [
            'registered' => ['Registered', 0], 'reachable' => ['Reachable', 1],
            'available' => ['Available', 2], 'disabled' => ['Disabled', 3],
            'unmonitored' => ['Unmonitored', 4], 'unregistered' => ['Unregistered', 5],
            'unreachable' => ['Unreachable', 6], 'unknown' => ['Unknown', 7],
            'uppercase' => ['UNREACHABLE', 6], 'whitespace' => [' registered ', 0],
            'rejected' => ['Rejected', 7], 'failed' => ['Failed', 7],
            'timeout' => ['Timeout', 7], 'lagged' => ['Lagged', 7],
            'suffix' => ['Unreachable (timeout)', 7], 'digits' => ['Failed 403', 7],
            'numeric-zero' => ['0', 7], 'numeric-one' => ['1', 7],
            'empty' => ['', 7], 'missing' => [null, 7],
            'no-such-instance' => ['No Such Instance', 7],
        ];
    }

    public function testFailedTableWalkPreservesSensorIdentityAsUnknown(): void
    {
        $sensor = $this->discover();
        $this->assertNotNull($sensor);
        $this->walkResponse = new SnmpResponse([], 'Timeout: No Response from 127.0.0.1', 1);
        $retained = $this->discover();
        $this->assertNotNull($retained);
        $this->assertSame($sensor->sensor_id, $retained->sensor_id);
        $this->assertSame($sensor->sensor_index, $retained->sensor_index);
        $this->assertSame(GrandstreamUcm::UNKNOWN, (int) $retained->sensor_current);
    }

    public function testPartialFailedWalkKeepsMissingTrunkWithoutChangingReturnedTrunk(): void
    {
        $sensor = $this->discover();
        $this->walkResponse = new SnmpResponse([
            GrandstreamUcm::TRUNK_NAME . '.2' => 'Second Trunk',
            GrandstreamUcm::TRUNK_STATUS . '.2' => 'Registered',
        ], 'Timeout: No Response from 127.0.0.1', 1);
        $this->discover();
        $this->assertSame(GrandstreamUcm::UNKNOWN, (int) $sensor->fresh()->sensor_current);
        $second = $this->device->sensors()->where('sensor_oid', GrandstreamUcm::TRUNK_OID_PREFIX . '2')->firstOrFail();
        $this->assertSame(0, (int) $second->sensor_current);
    }

    public function testEmptySuccessfulWalkAllowsDeliberatelyRemovedTrunkToBeRetired(): void
    {
        $this->assertNotNull($this->discover());
        $this->walkResponse = new SnmpResponse;
        $this->assertNull($this->discover());
    }

    public function testHistoricalSnmpProducerStatusesAreDiscoveredAndPolled(): void
    {
        $history = json_decode(
            file_get_contents(base_path('tests/fixtures/grandstream-ucm/trunk-status-history.json')),
            true,
            flags: JSON_THROW_ON_ERROR
        );
        foreach ($history['observations'] as $observation) {
            $this->walkResponse = $this->trunkResponse($observation['status']);
            $sensor = $this->discover();
            $this->assertNotNull($sensor);
            $this->assertSame($observation['state_value'], (int) $sensor->sensor_current);
            $this->getResponse = new SnmpResponse([GrandstreamUcm::TRUNK_OID_PREFIX . '1' => $observation['status']]);
            poll_sensor($this->device->toArray(), 'state');
            $this->assertSame($observation['state_value'], (int) $sensor->fresh()->sensor_current);
        }
    }

    #[DataProvider('detectionProvider')]
    public function testDetectionStaysWithinDocumentedDeviceFamily(string $description, bool $matches): void
    {
        $device = clone $this->device;
        $device['sysObjectID'] = '.1.3.6.1.4.1.8072.3.2.10';
        $device['sysDescr'] = $description;
        $this->assertSame($matches, Core::detectOS($device, false) === 'grandstream-ucm');
    }

    /** @return list<array{string, bool}> */
    public static function detectionProvider(): array
    {
        return [
            ['Linux UCM6308 4.4.194', true], ['Linux UCM6302 4.4.194', true],
            ['Linux UCM6308A 4.4.194', true], ['Other Linux UCM6308', false],
            ['Linux UCM6208 4.4.194', false], ['Linux UCM6510 4.4.194', false],
            ['Linux UCM63fake 4.4.194', false],
        ];
    }
}
