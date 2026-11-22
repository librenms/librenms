<?php

use App\Facades\LibrenmsConfig;
use App\Models\Port;
use App\Models\Pseudowire;
use LibreNMS\Util\IPv4;
use LibreNMS\Util\ProgressList;

if (LibrenmsConfig::get('enable_pseudowires') && $device['os_group'] == 'cisco') {
    // Pre-cache the existing state of pseudowires for this device from the database, keyed by cpwVcIndex.
    // cpwVcID is not unique: the same VC ID can be used towards different peers.
    $pws_db = Pseudowire::where('device_id', $device['device_id'])->get()->keyBy('cpwOid');
    $port_ids = Port::where('device_id', $device['device_id'])->pluck('port_id', 'ifDescr');

    // Both tables are indexed by cpwVcIndex
    $pws = SnmpQuery::enumStrings()->hideMib()->walk([
        'CISCO-IETF-PW-MIB::cpwVcID',
        'CISCO-IETF-PW-MIB::cpwVcName',
        'CISCO-IETF-PW-MIB::cpwVcType',
        'CISCO-IETF-PW-MIB::cpwVcPsnType',
        'CISCO-IETF-PW-MIB::cpwVcDescr',
        'CISCO-IETF-PW-MPLS-MIB::cpwVcMplsPeerLdpID',
    ])->throwIfConnectionError()->table(1); // a timeout must not be treated as "no pseudowires" (would delete or zero rows)

    $progress = ProgressList::start('Pseudowires');
    $seen_ids = [];

    foreach ($pws as $pw_oid => $pw) {
        // Interface names may use '_' in place of '/' (e.g. GigabitEthernet0_4_0_12 -> GigabitEthernet0/4/0/12)
        $if_name = preg_replace('/(?<=\d)_(?=\d)/', '/', (string) ($pw['cpwVcName'] ?? ''));

        // cpwVcMplsPeerLdpID is "ip:label-space"; store the IP as a number
        [$peer_ip] = explode(':', (string) ($pw['cpwVcMplsPeerLdpID'] ?? ''));
        $is_ip = IPv4::isValid($peer_ip);
        $peer_ldp_id = $is_ip ? ip2long($peer_ip) : (int) $peer_ip;
        $peer_device_id = $is_ip
            ? Port::whereHas('ipv4', fn ($q) => $q->where('ipv4_address', $peer_ip))->value('device_id')
            : null;

        $attributes = [
            'device_id' => $device['device_id'],
            'port_id' => $port_ids[$if_name] ?? 0,
            'peer_device_id' => $peer_device_id ?? 0,
            'peer_ldp_id' => $peer_ldp_id,
            'cpwVcID' => $pw['cpwVcID'] ?? 0,
            'cpwOid' => $pw_oid,
            'pw_type' => $pw['cpwVcType'] ?? '',
            'pw_descr' => $pw['cpwVcDescr'] ?? '',
            'pw_psntype' => $pw['cpwVcPsnType'] ?? '',
        ];

        $pseudowire = $pws_db->get($pw_oid);
        if ($pseudowire) {
            if (! isset($pw['cpwVcMplsPeerLdpID'])) {
                // peer column missing from the walk: keep the stored peer instead of zeroing it
                unset($attributes['peer_ldp_id'], $attributes['peer_device_id']);
            } elseif ($peer_device_id === null && $pseudowire->peer_ldp_id == $peer_ldp_id) {
                // same peer, but its device is not (or no longer) known: keep the stored association
                unset($attributes['peer_device_id']);
            }

            $pseudowire->fill($attributes)->save();
            $progress->mark($pseudowire->wasChanged() ? 'U' : '.');
        } else {
            $pseudowire = Pseudowire::create($attributes + ['pw_local_mtu' => 0, 'pw_peer_mtu' => 0]);
            $progress->mark('+');
        }

        $seen_ids[] = $pseudowire->pseudowire_id;
    }

    // Remove pseudowires that are no longer present on the device
    $deleted = Pseudowire::where('device_id', $device['device_id'])->whereNotIn('pseudowire_id', $seen_ids)->delete();
    if ($deleted) {
        $progress->mark(str_repeat('-', $deleted));
    }

    $progress->end();
}

unset($pws_db, $port_ids, $pws, $pw, $pw_oid, $if_name, $peer_ip, $is_ip, $peer_ldp_id, $peer_device_id, $seen_ids, $pseudowire, $attributes);
