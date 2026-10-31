<?php

// We're discovering this MIB
// snmpwalk -v2c -c <community> <hostname> -M mibs/junose/ -m Juniper-UNI-ATM-MIB juniAtmVpStatsEntry
// JunOSe ATM vps
use App\Facades\DeviceCache;
use App\Facades\LibrenmsConfig;
use App\Facades\PortCache;
use App\Models\Eventlog;
use App\Models\JuniAtmVp;
use Illuminate\Support\Facades\Log;
use LibreNMS\Enum\Severity;
use LibreNMS\Util\ProgressList;

if ($device['os'] == 'junose' && LibrenmsConfig::get('enable_ports_junoseatmvp')) {
    $vp_array = SnmpQuery::mibs(['Juniper-UNI-ATM-MIB'])->hideMib()->cache()->walk('juniAtmVpStatsInCells')->valuesByIndex();
    $valid_vp = [];

    $progress = ProgressList::start('ATM VPs');
    foreach ($vp_array as $index => $entry) {
        [$ifIndex, $vp_id] = explode('.', (string) $index);

        $port_id = PortCache::getIdFromIfIndex($ifIndex, $device['device_id']);

        if ($port_id && is_numeric($vp_id)) {
            Log::debug("Discover Juniper ATM VP: $port_id, $vp_id");

            if (! JuniAtmVp::where('port_id', $port_id)->where('vp_id', $vp_id)->exists()) {
                $vp = JuniAtmVp::create(['port_id' => $port_id, 'vp_id' => $vp_id, 'vp_descr' => null]);
                Eventlog::log("Juniper ATM VP Added: port $port_id vp $vp_id", $device['device_id'], 'juniAtmVp', Severity::Notice, $vp->id);
                $progress->mark('+');
            } else {
                $progress->mark('.');
            }

            $valid_vp[$port_id][$vp_id] = 1;
        }
    }
    $progress->end();

    // Remove ATM vps which weren't redetected here
    JuniAtmVp::whereHas('port', fn ($q) => $q->whereBelongsTo(DeviceCache::getPrimary()))
        ->get()
        ->reject(fn ($vp) => ! empty($valid_vp[$vp->port_id][$vp->vp_id]))
        ->each(function (JuniAtmVp $vp): void {
            Log::debug("Removing stale Juniper ATM VP: port {$vp->port_id} vp {$vp->vp_id}");
            $vp->delete();
        });
}
