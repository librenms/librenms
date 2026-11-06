<?php

namespace LibreNMS\Modules;

use App\Models\Device;
use App\Models\PortsFdb;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\DB\BulkTableSync;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\Traits\RequiresPollingMethod;

class FdbTable implements Module
{
    use RequiresPollingMethod;

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

        $rows = $os->discoverFdbTable()
            ->filter(fn (PortsFdb $fdb) => strlen((string) $fdb->mac_address) === 12);
        $sync = new BulkTableSync($device->portsFdb(), 'mac_address');
        // Retain history, refresh last-seen time, and preserve known ports when
        // an agent returns an incomplete bridge port table.
        $counts = $sync->sync($rows, Carbon::now()->toDateTimeString(), function (array $row, array $existing): array {
            $row['port_id'] = $row['port_id'] ?: $existing['port_id'];

            return $row;
        });
        Log::info("FDB: {$counts['inserted']} added, {$counts['updated']} updated, {$counts['unchanged']} unchanged, {$counts['deleted']} duplicates removed");
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
