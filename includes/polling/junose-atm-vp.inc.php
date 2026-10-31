<?php

use App\Facades\DeviceCache;
use App\Models\JuniAtmVp;
use Illuminate\Support\Facades\Log;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\ProgressList;

$vp_rows = JuniAtmVp::with('port:ifIndex')
    ->whereHas('port', fn ($q) => $q->whereBelongsTo(DeviceCache::getPrimary()))
    ->get();

if ($vp_rows->isNotEmpty()) {
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
        app('Datastore')->put($device, 'atm-vp', $tags, $fields);
    }

    $progress->end();

    unset($vp_cache, $rrd_def);
}
