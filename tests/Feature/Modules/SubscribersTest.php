<?php

namespace LibreNMS\Tests\Feature\Modules;

use App\Facades\DeviceCache;
use App\Models\Device;
use App\Models\Port;
use App\Models\SubscriberCount;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Discovery\SubscriberDiscovery;
use LibreNMS\Modules\Subscribers;
use LibreNMS\OS;
use LibreNMS\Tests\DBTestCase;
use Mockery;

/**
 * The rules every vendor gets from the module, tested with a fake os that only implements SubscriberDiscovery
 */
final class SubscribersTest extends DBTestCase
{
    use DatabaseTransactions;

    public function testCountsAreNormalized(): void
    {
        $counts = Subscribers::normalize(collect([
            $this->subscribers(null, 'total', 5),
            $this->subscribers(null, 'dhcp', 2), // unknown type
            $this->subscribers(null, 'other', 1),
            $this->subscribers(10, 'ipoe', 3),
            $this->subscribers(10, 'ipoe', 4), // same port and type
            $this->subscribers(10, 'total', 7), // sum of the session types of the port
            $this->subscribers(11, 'total', 9), // a port without session types
        ]))->map(fn (SubscriberCount $count) => "$count->port_id|$count->session_type=$count->sessions")->sort()->values()->all();

        $this->assertSame(['10|ipoe=7', '11|total=9', '|other=3', '|total=5'], $counts);
    }

    public function testDevicesReportingZeroAreNoSubscriberDevice(): void
    {
        $os = $this->os([$this->subscribers(null, 'ppp', 0)]);
        (new Subscribers)->discover($os);

        $this->assertFalse($os->getDevice()->subscriberCounts()->exists());
    }

    public function testDiscoveryKeepsCountsAtZeroOnceSeen(): void
    {
        $os = $this->os([$this->subscribers(null, 'ppp', 29)]);
        (new Subscribers)->discover($os);
        $os->reply = collect([$this->subscribers(null, 'ppp', 0)]);
        (new Subscribers)->discover($os);

        $this->assertSame([['session_type' => 'ppp', 'sessions' => 0]], $os->getDevice()->subscriberCounts()->get(['session_type', 'sessions'])->toArray());
    }

    public function testDiscoveryKeepsCountsWhenTheDeviceDoesNotAnswer(): void
    {
        $os = $this->os([$this->subscribers(null, 'ppp', 29)]);
        (new Subscribers)->discover($os);
        $os->reply = null;
        (new Subscribers)->discover($os);

        $this->assertSame(['ppp' => 29], $os->getDevice()->subscriberCounts()->pluck('sessions', 'session_type')->all());
    }

    public function testPollingSetsCountsTheDeviceNoLongerReportsToZero(): void
    {
        $os = $this->os([]);
        $port = Port::factory()->for($os->getDevice())->create();
        $os->reply = collect([$this->subscribers(null, 'ipoe', 2), $this->subscribers($port->port_id, 'ipoe', 1), $this->subscribers($port->port_id, 'pppoe', 1)]);
        (new Subscribers)->discover($os);

        $os->reply = collect([$this->subscribers(null, 'ipoe', 1), $this->subscribers($port->port_id, 'ipoe', 1)]);
        $datastore = Mockery::mock(DataStorageInterface::class);
        $datastore->shouldReceive('put')->once()->withArgs(fn ($device, $measurement, $tags, $fields) => $measurement == 'subscribers' && $tags['session_type'] == 'ipoe' && $fields == ['sessions' => 1]);
        (new Subscribers)->poll($os, $datastore);

        $expected = ['|ipoe' => 1, "$port->port_id|ipoe" => 1, "$port->port_id|pppoe" => 0];
        ksort($expected);
        $this->assertSame(
            $expected,
            $os->getDevice()->subscriberCounts()->get()->mapWithKeys(fn (SubscriberCount $count) => [$count->getCompositeKey() => $count->sessions])->sortKeys()->all(),
        );
    }

    public function testPollingAddsNewCounts(): void
    {
        $os = $this->os([]);
        $port = Port::factory()->for($os->getDevice())->create();
        $newPort = Port::factory()->for($os->getDevice())->create();
        $os->reply = collect([$this->subscribers(null, 'total', 5), $this->subscribers($port->port_id, 'total', 5)]);
        (new Subscribers)->discover($os);

        // the port total is replaced by the session types of the port, and a new port appears
        $os->reply = collect([
            $this->subscribers(null, 'total', 6),
            $this->subscribers($port->port_id, 'pppoe', 4),
            $this->subscribers($port->port_id, 'ipoe', 1),
            $this->subscribers($newPort->port_id, 'pppoe', 1),
        ]);
        $datastore = Mockery::mock(DataStorageInterface::class);
        $datastore->shouldReceive('put')->once();
        (new Subscribers)->poll($os, $datastore);

        $this->assertEqualsCanonicalizing([
            '|total=6',
            "$port->port_id|ipoe=1",
            "$port->port_id|pppoe=4",
            "$newPort->port_id|pppoe=1",
        ], $os->getDevice()->subscriberCounts()->get()->map(fn (SubscriberCount $count) => $count->getCompositeKey() . '=' . $count->sessions)->all());
    }

    public function testDataIsRemovedWhenTheOsNoLongerReportsSubscribers(): void
    {
        $device = Device::factory()->create(['os' => 'generic']);
        $device->subscriberCounts()->create(['port_id' => null, 'session_type' => 'ppp', 'sessions' => 29]);
        DeviceCache::setPrimary($device->device_id);
        $deviceArray = DeviceCache::refresh($device->device_id)->toArray();

        (new Subscribers)->discover(OS::make($deviceArray));

        $this->assertFalse($device->subscriberCounts()->exists());
    }

    private function subscribers(?int $portId, string $type, int $sessions): SubscriberCount
    {
        return new SubscriberCount(['port_id' => $portId, 'session_type' => $type, 'sessions' => $sessions]);
    }

    /**
     * @param  SubscriberCount[]  $reply
     */
    private function os(array $reply): FakeSubscriberOs
    {
        $device = Device::factory()->create(['os' => 'generic']);
        DeviceCache::setPrimary($device->device_id);
        $deviceArray = DeviceCache::refresh($device->device_id)->toArray();

        $os = FakeSubscriberOs::fake($deviceArray);
        $os->reply = collect($reply);

        return $os;
    }
}

final class FakeSubscriberOs extends OS implements SubscriberDiscovery
{
    /** @var Collection<int, SubscriberCount>|null */
    public ?Collection $reply = null;

    /**
     * @param  array<string, mixed>  $device
     */
    public static function fake(array $device): self
    {
        return new self($device);
    }

    public function discoverSubscribers(): ?Collection
    {
        return $this->reply?->map(fn (SubscriberCount $count) => clone $count);
    }
}
