<?php

namespace LibreNMS\Modules;

use App\Facades\LibrenmsConfig;
use App\Facades\PortCache;
use App\Models\Device;
use App\Models\Eventlog;
use App\Models\JuniAtmVp;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use LibreNMS\Enum\PollingMethodType;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Traits\RequiresPollingMethod;
use LibreNMS\Util\ProgressList;
use SnmpQuery;

class JunoseAtmVp implements Module
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

    /**
     * @inheritDoc
     */
    public function shouldDiscover(OS $os): bool
    {
        return $this->isJunoseAtmVpEnabled($os) && $this->hasAvailableMethod($os);
    }

    /**
     * @inheritDoc
     */
    public function shouldPoll(OS $os): bool
    {
        return $this->isJunoseAtmVpEnabled($os) && $this->hasAvailableMethod($os);
    }

    /**
     * @inheritDoc
     */
    public function discover(OS $os): void
    {
        $device = $os->getDevice();

        $vp_array = SnmpQuery::mibs(['Juniper-UNI-ATM-MIB'])->hideMib()->cache()->walk('juniAtmVpStatsInCells')->valuesByIndex();
        $valid_vp = [];

        $progress = ProgressList::start('ATM VPs');
        foreach ($vp_array as $index => $entry) {
            $parts = explode('.', (string) $index);
            if (count($parts) !== 2) {
                continue;
            }

            [$ifIndex, $vp_id] = $parts;

            $port_id = PortCache::getIdFromIfIndex($ifIndex, $device->device_id);

            if ($port_id && is_numeric($vp_id)) {
                Log::debug("Discover Juniper ATM VP: $port_id, $vp_id");

                if (! JuniAtmVp::where('port_id', $port_id)->where('vp_id', $vp_id)->exists()) {
                    $vp = JuniAtmVp::create(['port_id' => $port_id, 'vp_id' => $vp_id, 'vp_descr' => null]);
                    Eventlog::log("Juniper ATM VP Added: port $port_id vp $vp_id", $device->device_id, 'juniAtmVp', Severity::Notice, $vp->id);
                    $progress->mark('+');
                } else {
                    $progress->mark('.');
                }

                $valid_vp[$port_id][$vp_id] = 1;
            }
        }
        $progress->end();

        // Remove ATM vps which weren't redetected here
        $device->juniAtmVps()
            ->get()
            ->reject(fn (JuniAtmVp $vp) => ! empty($valid_vp[$vp->port_id][$vp->vp_id]))
            ->each(function (JuniAtmVp $vp): void {
                Log::debug("Removing stale Juniper ATM VP: port {$vp->port_id} vp {$vp->vp_id}");
                $vp->delete();
            });
    }

    /**
     * @inheritDoc
     */
    public function poll(OS $os, DataStorageInterface $datastore): void
    {
        $vp_rows = $os->getDevice()->juniAtmVps()->with('port:ifIndex')->get();

        if ($vp_rows->isEmpty()) {
            return;
        }

        $vp_cache = SnmpQuery::mibs(['Juniper-UNI-ATM-MIB'])->hideMib()->cache()->walk([
            'juniAtmVpStatsInCells',
            'juniAtmVpStatsInPackets',
            'juniAtmVpStatsInPacketOctets',
            'juniAtmVpStatsInPacketErrors',
            'juniAtmVpStatsOutCells',
            'juniAtmVpStatsOutPackets',
            'juniAtmVpStatsOutPacketOctets',
            'juniAtmVpStatsOutPacketErrors',
        ])->valuesByIndex();

        $rrd_def = RrdDefinition::make()
            ->addDataset('incells', 'DERIVE', 0, 125000000000)
            ->addDataset('outcells', 'DERIVE', 0, 125000000000)
            ->addDataset('inpackets', 'DERIVE', 0, 125000000000)
            ->addDataset('outpackets', 'DERIVE', 0, 125000000000)
            ->addDataset('inpacketoctets', 'DERIVE', 0, 125000000000)
            ->addDataset('outpacketoctets', 'DERIVE', 0, 125000000000)
            ->addDataset('inpacketerrors', 'DERIVE', 0, 125000000000)
            ->addDataset('outpacketerrors', 'DERIVE', 0, 125000000000);

        $progress = ProgressList::start('ATM VPs');
        foreach ($vp_rows as $vp) {
            $progress->mark('.');

            $ifIndex = $vp->port->ifIndex;
            $vp_id = $vp->vp_id;
            $oid = "$ifIndex.$vp_id";

            Log::debug("ATM VP $oid");

            $t_vp = $vp_cache[$oid] ?? [];

            $rrd_name = ['vp', $ifIndex, $vp_id];

            $fields = [
                'incells' => $t_vp['juniAtmVpStatsInCells'] ?? null,
                'outcells' => $t_vp['juniAtmVpStatsOutCells'] ?? null,
                'inpackets' => $t_vp['juniAtmVpStatsInPackets'] ?? null,
                'outpackets' => $t_vp['juniAtmVpStatsOutPackets'] ?? null,
                'inpacketoctets' => $t_vp['juniAtmVpStatsInPacketOctets'] ?? null,
                'outpacketoctets' => $t_vp['juniAtmVpStatsOutPacketOctets'] ?? null,
                'inpacketerrors' => $t_vp['juniAtmVpStatsInPacketErrors'] ?? null,
                'outpacketerrors' => $t_vp['juniAtmVpStatsOutPacketErrors'] ?? null,
            ];

            $tags = ['ifIndex' => $ifIndex, 'vp_id' => $vp_id, 'rrd_name' => $rrd_name, 'rrd_def' => $rrd_def];
            $datastore->put($os->getDeviceArray(), 'atm-vp', $tags, $fields);
        }
        $progress->end();
    }

    /**
     * @inheritDoc
     */
    public function dataExists(Device $device): bool
    {
        return $device->juniAtmVps()->exists();
    }

    /**
     * @inheritDoc
     */
    public function cleanup(Device $device): int
    {
        return $device->juniAtmVps()->delete();
    }

    /**
     * @return array{juniAtmVp: Collection<int, JuniAtmVp>}|null
     */
    public function dump(Device $device, string $type): ?array
    {
        if ($type == 'polling') {
            return null;
        }

        return [
            'juniAtmVp' => $device->juniAtmVps()->get()
                ->sort(fn (JuniAtmVp $a, JuniAtmVp $b) => $a->port_id <=> $b->port_id ?: $a->vp_id <=> $b->vp_id)
                ->values()
                ->makeHidden(['id', 'juniAtmVp_id']),
        ];
    }

    private function isJunoseAtmVpEnabled(OS $os): bool
    {
        return $os->getDevice()->os === 'junose' && (bool) LibrenmsConfig::get('enable_ports_junoseatmvp', false);
    }
}
