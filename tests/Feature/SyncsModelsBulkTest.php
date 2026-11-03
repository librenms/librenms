<?php

namespace LibreNMS\Tests\Feature;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\Ipv4Address;
use App\Models\Ipv4Mac;
use App\Models\Ipv6Nd;
use App\Models\Port;
use App\Models\Storage;
use App\Models\Vrf;
use App\Observers\ModuleModelObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Interfaces\Discovery\ArpTableDiscovery;
use LibreNMS\Interfaces\Discovery\Ipv6NdDiscovery;
use LibreNMS\Modules\ArpTable;
use LibreNMS\OS;
use LibreNMS\Tests\DBTestCase;
use Mockery;
use Monolog\Handler\NullHandler;
use Monolog\Handler\TestHandler;
use Monolog\Logger as MonologLogger;
use PHPUnit\Framework\Attributes\DataProvider;

final class SyncsModelsBulkTest extends DBTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        config(['logging.channels.stdout' => ['driver' => 'monolog', 'handler' => NullHandler::class]]);
        Log::forgetChannel('stdout');
    }

    public function testArpDiscoveryUsesBoundedInsertQueries(): void
    {
        $device = Device::factory()->create();
        $models = $this->arpRows(2051);
        $inserts = [];
        DB::listen(function (QueryExecuted $query) use (&$inserts): void {
            if (preg_match('/^insert into [`"]?ipv4_mac\b/i', $query->sql)) {
                $inserts[] = $query;
            }
        });

        $os = Mockery::mock(OS::class, ArpTableDiscovery::class);
        $os->shouldReceive('getDevice')->andReturn($device);
        $os->shouldReceive('discoverArpTable')->once()->andReturn($models);
        (new ArpTable)->discover($os);

        $this->assertSame(2051, $device->macs()->count());
        $this->assertCount(3, $inserts);
        $this->assertSame([5000, 5000, 255], array_map(fn (QueryExecuted $query) => count($query->bindings), $inserts));
        $this->assertSame([$device->device_id], $device->macs()->distinct()->pluck('device_id')->all());
        $this->assertFalse($models->first()->exists, 'The bulk API does not pretend to return saved instances');
        $this->assertNull($models->first()->getKey());
    }

    public function testNormalSyncStillReturnsSavedInstancesAndGeneratedIds(): void
    {
        $device = Device::factory()->create();
        $model = $this->arpRows(1)->first();
        $model->device_id = $device->device_id + 1;
        $saved = $this->syncer()->syncModels($device, 'macs', new Collection([$model]));

        $this->assertSame($model, $saved->first());
        $this->assertNotNull($model->getKey());
        $this->assertTrue($model->exists);
        $this->assertTrue($model->wasRecentlyCreated);
        $this->assertFalse($model->isDirty());
        $this->assertSame($device->device_id, $model->device_id);
        $this->assertSame($model->getKey(), $device->macs()->sole()->getKey());
    }

    public function testBulkSyncKeepsUpdateDeleteAndLastDiscoveredDuplicateSemantics(): void
    {
        $device = Device::factory()->create();
        $attributes = $this->arpAttributes(1);
        $first = $device->macs()->createQuietly($attributes);
        $duplicate = $device->macs()->createQuietly($attributes);
        $gone = $device->macs()->createQuietly(array_replace($attributes, ['ipv4_address' => '192.0.2.1']));
        $deleted = [];
        $updated = [];
        Ipv4Mac::deleted(function (Ipv4Mac $model) use (&$deleted): void {
            $deleted[] = $model->getKey();
        });
        Ipv4Mac::updated(function (Ipv4Mac $model) use (&$updated): void {
            $updated[] = $model->getKey();
        });
        $models = $this->arpRows(2);
        $models->push(new Ipv4Mac(array_replace($attributes, ['mac_address' => 'ffffffffffff'])));

        $this->syncer()->syncModelsInBulk($device, 'macs', $models);

        $this->assertSame(2, $device->macs()->count());
        $this->assertSame('ffffffffffff', $first->fresh()->mac_address);
        $this->assertSame([$first->id], $updated);
        $this->assertEqualsCanonicalizing([$duplicate->id, $gone->id], $deleted);
        $device->unsetRelation('macs');
        $this->syncer()->syncModelsInBulk($device, 'macs', new Collection);
        $this->assertSame(0, $device->macs()->count());
    }

    /** @return array<string, array{string}> */
    public static function extraListeners(): array
    {
        return ['saving' => ['saving'], 'creating' => ['creating'], 'created' => ['created'], 'saved' => ['saved'], 'wildcard' => ['wildcard']];
    }

    #[DataProvider('extraListeners')]
    public function testExtraListenersFallBackToTheEloquentLifecycle(string $event): void
    {
        $device = Device::factory()->create();
        $models = $this->arpRows(2);
        $seen = [];
        if ($event === 'wildcard') {
            Event::listen('eloquent.created: *', function (string $name, array $payload) use (&$seen): void {
                $seen[] = $payload[0]->getKey();
            });
        } else {
            Ipv4Mac::$event(function (Ipv4Mac $model) use (&$seen, $event): void {
                if (in_array($event, ['saving', 'creating'])) {
                    $model->mac_address = 'aabbccddeeff';
                }
                $seen[] = $model->getKey();
            });
        }

        $this->syncer()->syncModelsInBulk($device, 'macs', $models);

        $this->assertCount(2, $seen);
        foreach ($models as $model) {
            $this->assertTrue($model->exists);
            $this->assertNotNull($model->getKey());
            $this->assertFalse($model->isDirty());
            $this->assertSame($model->mac_address, $model->fresh()->mac_address);
        }
        if (in_array($event, ['created', 'saved', 'wildcard'])) {
            $this->assertSame($models->pluck('id')->all(), $seen);
        } else {
            $this->assertSame(['aabbccddeeff', 'aabbccddeeff'], $device->macs()->pluck('mac_address')->all());
        }
    }

    public function testCreatingListenerCanStillCancelAnInsert(): void
    {
        $device = Device::factory()->create();
        $models = $this->arpRows(2);
        Ipv4Mac::creating(fn (Ipv4Mac $model) => $model->ipv4_address === '10.0.0.2' ? false : null);

        $this->syncer()->syncModelsInBulk($device, 'macs', $models);

        $this->assertSame(1, $device->macs()->count());
        $this->assertTrue($models[0]->exists);
        $this->assertFalse($models[1]->exists);
        $this->assertNull($models[1]->getKey());
    }

    public function testContainerCustomizedProgressObserverKeepsItsCallbacks(): void
    {
        $device = Device::factory()->create();
        $observer = new class extends ModuleModelObserver
        {
            /** @var list<int> */
            public array $ids = [];

            public function created($model): void
            {
                $this->ids[] = $model->getKey();
            }
        };
        app()->instance(ModuleModelObserver::class, $observer);
        Ipv4Mac::observe(ModuleModelObserver::class);
        $models = $this->arpRows(2);

        $this->syncer()->syncModelsInBulk($device, 'macs', $models);

        $this->assertSame($models->pluck('id')->all(), $observer->ids);
        $this->assertTrue($models->every->exists);
    }

    public function testModelsWithObserversKeepTheirCreationSideEffects(): void
    {
        $device = Device::factory()->create();
        LibrenmsConfig::set('storage_perc_warn', 73);
        $storage = Storage::factory()->make(['storage_perc_warn' => null]);
        $this->syncer()->syncModelsInBulk($device, 'storage', new Collection([$storage]));

        $this->assertTrue($storage->exists);
        $this->assertSame(73, $storage->fresh()->storage_perc_warn);
    }

    public function testThroughRelationsKeepTheirIntermediateForeignKeys(): void
    {
        $device = Device::factory()->create();
        $port = Port::factory()->for($device)->create();
        $address = Ipv4Address::factory()->make(['port_id' => $port->port_id]);
        $this->syncer()->syncModelsInBulk($device, 'ipv4', new Collection([$address]));

        $this->assertTrue($address->exists);
        $this->assertSame($port->port_id, $address->fresh()->port_id);
        $this->assertSame($address->getKey(), $device->ipv4()->sole()->getKey());
    }

    public function testRelationshipCreationAttributesStillApply(): void
    {
        $device = Device::factory()->create();
        $parent = new class extends Model
        {
            protected $table = 'devices';
            protected $primaryKey = 'device_id';

            /** @return HasMany<Ipv4Mac, $this> */
            public function macs(): HasMany
            {
                return $this->hasMany(Ipv4Mac::class, 'device_id')->withAttributes(['context_name' => 'relationship default']);
            }
        };
        $parent->setRawAttributes($device->getAttributes(), true);
        $parent->exists = true;
        $model = $this->arpRows(1)->first();
        unset($model->context_name);

        $this->syncer()->syncModelsInBulk($parent, 'macs', new Collection([$model]));

        $this->assertTrue($model->exists);
        $this->assertSame('relationship default', $model->fresh()->context_name);
    }

    public function testExplicitPrimaryKeysKeepTheSavePath(): void
    {
        $device = Device::factory()->create();
        $model = $this->arpRows(1)->first();
        $model->id = Ipv4Mac::max('id') + 50;
        $id = $model->id;

        $this->syncer()->syncModelsInBulk($device, 'macs', new Collection([$model]));

        $this->assertTrue($model->exists);
        $this->assertSame($id, $model->fresh()->id);
    }

    public function testBulkModelsUseTheirOwnConnectionAndPrefix(): void
    {
        config(['database.connections.bulk_secondary' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => 'secondary_']]);
        $other = DB::connection('bulk_secondary');
        $other->getSchemaBuilder()->create('ipv4_mac', function (Blueprint $table): void {
            $table->id();
            $table->integer('port_id');
            $table->integer('device_id');
            $table->string('mac_address');
            $table->string('ipv4_address');
            $table->string('context_name')->nullable();
        });
        $before = Ipv4Mac::count();
        $parent = new Device;
        $parent->setConnection('bulk_secondary');
        $parent->device_id = 123;
        $parent->exists = true;
        $models = $this->arpRows(2)->each->setConnection('bulk_secondary');

        $this->syncer()->syncModelsInBulk($parent, 'macs', $models);

        $this->assertSame(2, $other->table('ipv4_mac')->where('device_id', 123)->count());
        $this->assertSame($before, Ipv4Mac::count());
    }

    public function testBulkNeighborDiscoveryKeepsTimestamps(): void
    {
        $device = Device::factory()->create();
        $models = new Collection(array_map(fn (int $i) => new Ipv6Nd([
            'port_id' => 1, 'ipv6_address' => '2001:db8::' . dechex($i),
            'mac_address' => sprintf('%012x', $i), 'context_name' => '',
        ]), range(1, 1001)));
        $models->firstOrFail()->created_at = '2020-01-02 03:04:05';
        $os = Mockery::mock(OS::class, Ipv6NdDiscovery::class);
        $os->shouldReceive('getDevice')->andReturn($device);
        $os->shouldReceive('discoverIpv6Neighbor')->once()->andReturn($models);

        (new \LibreNMS\Modules\Ipv6Nd)->discover($os);

        $this->assertSame(1001, $device->nd()->count());
        $this->assertSame(0, $device->nd()->whereNull('updated_at')->count());
        $this->assertSame(0, $device->nd()->whereNull('created_at')->count());
        $timestamp = $device->nd()->where('ipv6_address', '2001:db8::1')->sole()->created_at;
        $this->assertNotNull($timestamp);
        $this->assertSame('2020-01-02 03:04:05', $timestamp->toDateTimeString());
    }

    public function testSkippedVrfContextsAndOtherDevicesAreKept(): void
    {
        $device = Device::factory()->create();
        $other = Device::factory()->create();
        Vrf::factory()->for($device)->create(['context_name' => 'skipped', 'context_enabled' => false]);
        $attributes = $this->arpAttributes(1);
        $skipped = $device->macs()->createQuietly(array_replace($attributes, ['context_name' => 'skipped']));
        $foreign = $other->macs()->createQuietly($attributes);
        $stale = $device->macs()->createQuietly(array_replace($attributes, ['ipv4_address' => '192.0.2.3']));

        $this->syncer()->syncVrfContextModelsInBulk($device, 'macs', $this->arpRows(2));

        $this->assertNotNull($skipped->fresh());
        $this->assertNotNull($foreign->fresh());
        $this->assertNull($stale->fresh());
        $this->assertSame(3, $device->macs()->count());
    }

    public function testProgressStillOutputsOnePlusPerInsertedRow(): void
    {
        $device = Device::factory()->create();
        $handler = new TestHandler;
        Log::extend('capture-progress', fn () => new MonologLogger('progress', [$handler]));
        config(['logging.channels.stdout' => ['driver' => 'capture-progress']]);
        Log::forgetChannel('stdout');
        Ipv4Mac::observe(ModuleModelObserver::class);

        $this->syncer()->syncModelsInBulk($device, 'macs', $this->arpRows(1001));

        $messages = array_column($handler->getRecords(), 'message');
        $this->assertSame(str_repeat('+', 1001), implode('', $messages));
    }

    private function syncer(): object
    {
        return new class
        {
            use SyncsModels {
                syncModels as public;
                syncModelsInBulk as public;
                syncVrfContextModelsInBulk as public;
            }
        };
    }

    /** @return Collection<int, Ipv4Mac> */
    private function arpRows(int $count): Collection
    {
        return new Collection(array_map(fn (int $i) => new Ipv4Mac($this->arpAttributes($i)), range(1, $count)));
    }

    /** @return array{port_id: int, ipv4_address: string, mac_address: string, context_name: string} */
    private function arpAttributes(int $i): array
    {
        return [
            'port_id' => 1,
            'ipv4_address' => long2ip(0x0a000000 + $i),
            'mac_address' => sprintf('%012x', $i),
            'context_name' => '',
        ];
    }
}
