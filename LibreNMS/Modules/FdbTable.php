<?php

namespace LibreNMS\Modules;

use App\Models\Device;
use App\Models\PortsFdb;
use App\Observers\ModuleModelObserver;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use LibreNMS\DB\SyncsModels;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\Traits\RequiresPollingMethod;

class FdbTable implements Module
{
    use RequiresPollingMethod;
    use SyncsModels;

    /**
     * @return string[]
     */
    public function dependencies(): array
    {
        return ['ports'];
    }

    public function pollingMethods(Device $device): array
    {
        return [PollingMethodType::Snmp];
    }

    public function shouldPoll(OS $os): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function discover(OS $os): void
    {
        $device = $os->getDevice();

        $discovered = $os->discoverFdbTable()
            ->filter(fn (PortsFdb $fdb) => strlen((string) $fdb->mac_address) === 12)
            ->keyBy->getCompositeKey();

        // Entries that are no longer learned are kept as history until ports_fdb_purge removes them
        $seen = $discovered->pluck('mac_address')->unique()->chunk(1000)
            ->flatMap(fn ($macs) => $device->portsFdb()->whereIn('mac_address', $macs)->get())
            ->filter(fn (PortsFdb $fdb) => $discovered->has($fdb->getCompositeKey()));

        // Agents may return an incomplete bridge port table, don't overwrite a previously known port
        foreach ($seen as $fdb) {
            $new = $discovered->get($fdb->getCompositeKey());
            $new->port_id = $new->port_id ?: $fdb->port_id;
        }

        ModuleModelObserver::observe(PortsFdb::class, 'FDB');
        $this->syncModels($device, 'portsFdb', $discovered, $seen);
        ModuleModelObserver::done();

        // updated_at is the last time the entry was seen
        PortsFdb::whereIntegerInRaw('ports_fdb_id', $seen->pluck('ports_fdb_id')->all())->update(['updated_at' => Carbon::now()]);
    }

    /**
     * @inheritDoc
     */
    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        // no polling
    }

    /**
     * @inheritDoc
     */
    public function dataExists(Device $device): bool
    {
        return $device->portsFdb()->exists();
    }

    /**
     * @inheritDoc
     */
    public function cleanup(Device $device): int
    {
        return $device->portsFdb()->delete();
    }

    /**
     * @return array{ports_fdb: Collection<int, PortsFdb>}|null
     */
    public function dump(Device $device, string $type): ?array
    {
        if ($type != 'discovery') {
            return null;
        }

        return [
            'ports_fdb' => $device->portsFdb()
                ->leftJoin('ports', 'ports_fdb.port_id', 'ports.port_id')
                ->orderBy('ports.ifIndex')->orderBy('ports_fdb.vlan')->orderBy('ports_fdb.mac_address')
                ->select(['ports_fdb.*', 'ports.ifIndex'])
                ->get()
                ->makeHidden(['ports_fdb_id', 'device_id', 'port_id', 'created_at', 'updated_at']),
        ];
    }
}
