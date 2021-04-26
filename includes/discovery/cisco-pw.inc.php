<?php

use LibreNMS\Config;

if (Config::get('enable_pseudowires') && $device['os_group'] == 'cisco') {
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

    foreach ($pws as $pw_id => $pw) {
        // Added By Oirbsiu
        // To correct Interface names that use escaped '/' e.g. GigabitEthernet0_4_0_12
        // and translate the underscore back to a slash - e.g. GigabitEthernet0/4/0/12
        // Thank you @murrant
        $pw['cpwVcName'] = preg_replace('/(?<=\d)_(?=\d)/', '/', $pw['cpwVcName']);
        // END

        [$cpw_remote_id] = explode(':', $pw['cpwVcMplsPeerLdpID']);
        $cpw_remote_device = dbFetchCell('SELECT device_id from ipv4_addresses AS A, ports AS I WHERE A.ipv4_address=? AND A.port_id=I.port_id', [$cpw_remote_id]);
        $if_id = dbFetchCell('SELECT port_id from ports WHERE `ifDescr`=? AND `device_id`=?', [$pw['cpwVcName'], $device['device_id']]);
        if (! empty($pws_db[$pw['cpwVcID']])) {
            $pseudowire_id = $pws_db[$pw['cpwVcID']];
            echo '.';
        } else {
            $pseudowire_id = dbInsert(
                [
                    'device_id'      => $device['device_id'],
                    'port_id'        => $if_id,
                    'peer_device_id' => $cpw_remote_device,
                    'peer_ldp_id'    => $cpw_remote_id,
                    'cpwVcID'        => $pw['cpwVcID'],
                    'cpwOid'         => $pw_id,
                    'pw_type'        => $pw['cpwVcType'],
                    'pw_descr'       => $pw['cpwVcDescr'],
                    'pw_psntype'     => $pw['cpwVcPsnType'],
                ],
                'pseudowires'
            );
            echo '+';
        }

        $device['pws'][$pw['cpwVcID']] = $pseudowire_id;
    }//end foreach

    // Cycle the list of pseudowires we cached earlier and make sure we saw them again.
    foreach ($pws_db as $pw_id => $pseudowire_id) {
        if (empty($device['pws'][$pw_id])) {
            dbDelete('pseudowires', '`pseudowire_id` = ?', [$pseudowire_id]);
        }
    }

    echo "\n";
} //end if

unset($pws_db, $pws_db_raw, $pw_db);
