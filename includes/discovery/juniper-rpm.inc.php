<?php

use LibreNMS\Config;
use LibreNMS\Util\IP;

if (Config::get('enable_sla') && $device['os'] == 'junos') {
    $slas = snmp_walk($device, 'pingMIB.pingObjects.pingCtlTable.pingCtlEntry', '-OQUs', '+DISMAN-PING-MIB');

    $sla_table = [];
    foreach (explode("\n", $slas) as $sla) {
        $key_val = explode(' ', $sla, 3);

        $key = $key_val[0];
        $value = $key_val[2];

        $prop_id = explode('.', $key);

        $property = $prop_id[0];
        $owner = $prop_id[1];
        $test = $prop_id[2];

        $sla_table[$owner . '.' . $test][$property] = $value;
    }

    // Get existing SLAs
    $existing_slas = dbFetchColumn('SELECT `sla_id` FROM `slas` WHERE `device_id` = :device_id AND `deleted` = 0', ['device_id' => $device['device_id']]);

    $query_data = [
        'device_id' => $device['device_id'],
    ];
    $max_sla_nr = dbFetchCell('SELECT MAX(`sla_nr`) FROM `slas` WHERE `device_id` = :device_id', $query_data);
    $i = 1;

    foreach ($sla_table as $sla_key => $sla_config) {
        // To get right owner index and test name from $sla_table key
        $prop_id = explode('.', $sla_key);
        $owner = $prop_id[0];
        $test = $prop_id[1];

        $query_data = [
            'device_id' => $device['device_id'],
            'owner'     => $owner,
            'tag'       => $test,
        ];
        $sla_data = dbFetchRows('SELECT `sla_id`, `sla_nr` FROM `slas` WHERE `device_id` = :device_id AND `owner` = :owner AND `tag` = :tag', $query_data);
        $sla_id = $sla_data[0]['sla_id'];
        $sla_nr = $sla_data[0]['sla_nr'];

        $data = [
            'device_id' => $device['device_id'],
            'sla_nr'    => $sla_nr,
            'owner'     => $owner,
            'tag'       => $test,
            'rtt_type'  => $sla_config['pingCtlType'],
            'status'    => ($sla_config['pingCtlAdminStatus'] == 'enabled') ? 1 : 0,
            'opstatus'  => ($sla_config['pingCtlRowStatus'] == 'active') ? 0 : 2,
            'deleted'   => 0,
        ];

        // If it is a standard type delete ping preffix
        $data['rtt_type'] = str_replace('ping', '', $data['rtt_type']);

        // Retrieve Juniper type
        switch ($data['rtt_type']) {
            case 'enterprises.2636.3.7.2.1':
                $data['rtt_type'] = 'IcmpTimeStamp';
                break;

            case 'enterprises.2636.3.7.2.2':
                $data['rtt_type'] = 'HttpGet';
                break;

            case 'enterprises.2636.3.7.2.3':
                $data['rtt_type'] = 'HttpGetMetadata';
                break;

            case 'enterprises.2636.3.7.2.4':
                $data['rtt_type'] = 'DnsQuery';
                break;

            case 'enterprises.2636.3.7.2.5':
                $data['rtt_type'] = 'NtpQuery';
                break;
            case 'enterprises.2636.3.7.2.6':
                $data['rtt_type'] = 'UdpTimestamp';
                break;
        }

        if (! $sla_id) {
            $data['sla_nr'] = $max_sla_nr + $i;
            $sla_id = dbInsert($data, 'slas');
            $i++;
            echo '+';
        } else {
            // Remove from the list
            $existing_slas = array_diff($existing_slas, [$sla_id]);

            dbUpdate($data, 'slas', 'sla_id = ?', [$sla_id]);
            echo '.';
        }
    }//end foreach

    // Mark all remaining SLAs as deleted
    foreach ($existing_slas as $existing_sla) {
        dbUpdate(['deleted' => 1], 'slas', 'sla_id = ?', [$existing_sla]);
        echo '-';
    }

    echo "\n";
}
