<?php

use App\Facades\LibrenmsConfig;
use LibreNMS\Util\ProgressList;

if (LibrenmsConfig::get('enable_pseudowires') && $device['os_group'] == 'cisco') {
    $pws_db = [];
    // Pre-cache the existing state of pseudowires for this device from the database
    $pws_db_raw = dbFetchRows('SELECT * FROM `pseudowires` WHERE `device_id` = ?', [$device['device_id']]);
    foreach ($pws_db_raw as $pw_db) {
        $pws_db[$pw_db['cpwVcID']] = $pw_db['pseudowire_id'];
    }

    $pws = snmpwalk_cache_oid($device, 'cpwVcID', [], 'CISCO-IETF-PW-MPLS-MIB');
    $pws = snmpwalk_cache_oid($device, 'cpwVcName', $pws, 'CISCO-IETF-PW-MPLS-MIB');
    $pws = snmpwalk_cache_oid($device, 'cpwVcType', $pws, 'CISCO-IETF-PW-MPLS-MIB');
    $pws = snmpwalk_cache_oid($device, 'cpwVcPsnType', $pws, 'CISCO-IETF-PW-MPLS-MIB');
    $pws = snmpwalk_cache_oid($device, 'cpwVcDescr', $pws, 'CISCO-IETF-PW-MPLS-MIB');

    // For MPLS pseudowires
    $pws = snmpwalk_cache_oid($device, 'cpwVcMplsPeerLdpID', $pws, 'CISCO-IETF-PW-MPLS-MIB');

    // cpwVcID is how existing rows are matched, but it can repeat (e.g. VPLS), so only refresh unique ones
    $vc_id_counts = array_count_values(array_column($pws, 'cpwVcID'));

    $progress = ProgressList::start('Pseudowires');
    foreach ($pws as $pw_id => $pw) {
        // Added By Oirbsiu
        // To correct Interface names that use escaped '/' e.g. GigabitEthernet0_4_0_12
        // and translate the underscore back to a slash - e.g. GigabitEthernet0/4/0/12
        // Thank you @murrant
        $pw['cpwVcName'] = preg_replace('/(?<=\d)_(?=\d)/', '/', (string) $pw['cpwVcName']);
        // END

        [$cpw_remote_ip] = explode(':', (string) $pw['cpwVcMplsPeerLdpID']);
        $cpw_remote_id = $cpw_remote_ip;

        if (\LibreNMS\Util\IPv4::isValid($cpw_remote_ip)) {
            //If cpwVcMplsPeerLdpID is in the IP form, then convert it to number to store it in DB to avoid failing
            $cpw_remote_id = ip2long($cpw_remote_ip);
        }

        // lookup must use the dotted IP, ipv4_addresses stores addresses as strings
        // only resolve the peer when the address belongs to exactly one device
        $peer_device_ids = \App\Models\Ipv4Address::join('ports', 'ipv4_addresses.port_id', '=', 'ports.port_id')
            ->where('ipv4_addresses.ipv4_address', $cpw_remote_ip)
            ->where('ports.deleted', 0)
            ->distinct()->limit(2)->pluck('ports.device_id');
        $cpw_remote_device = $peer_device_ids->count() == 1 ? $peer_device_ids->first() : null;
        $if_id = \App\Models\Port::where('device_id', $device['device_id'])
            ->where('ifDescr', $pw['cpwVcName'])
            ->isNotDeleted()
            ->value('port_id');
        if (! empty($pws_db[$pw['cpwVcID']])) {
            $pseudowire_id = $pws_db[$pw['cpwVcID']];
            if ($vc_id_counts[$pw['cpwVcID']] == 1) {
                // refresh peer and port, columns are NOT NULL so store 0 for an unresolved peer
                // keep the existing port if it can't be resolved, the tab hides PWs without one
                $updates = ['peer_device_id' => $cpw_remote_device ?? 0];
                if ($if_id !== null) {
                    $updates['port_id'] = $if_id;
                }
                \App\Models\Pseudowire::where('pseudowire_id', $pseudowire_id)->update($updates);
            }
            $progress->mark('.');
        } else {
            $pseudowire_id = dbInsert(
                [
                    'device_id' => $device['device_id'],
                    'port_id' => $if_id,
                    'peer_device_id' => $cpw_remote_device,
                    'peer_ldp_id' => $cpw_remote_id,
                    'cpwVcID' => $pw['cpwVcID'],
                    'cpwOid' => $pw_id,
                    'pw_type' => $pw['cpwVcType'],
                    'pw_descr' => $pw['cpwVcDescr'],
                    'pw_psntype' => $pw['cpwVcPsnType'],
                ],
                'pseudowires'
            );
            $progress->mark('+');
        }

        $device['pws'][$pw['cpwVcID']] = $pseudowire_id;
    }//end foreach

    // Cycle the list of pseudowires we cached earlier and make sure we saw them again.
    foreach ($pws_db as $pw_id => $pseudowire_id) {
        if (empty($device['pws'][$pw_id])) {
            \App\Models\Pseudowire::where('pseudowire_id', $pseudowire_id)->delete();
        }
    }

    $progress->end();
} //end if

unset($pws_db, $pws_db_raw, $pw_db);
