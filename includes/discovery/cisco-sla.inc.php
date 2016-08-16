<?php

if ($config['enable_sla'] && $device['os_group'] == 'cisco') {

    $slas = snmp_walk($device, 'ciscoRttMonMIB.ciscoRttMonObjects.rttMonCtrl', '-Osq', '+CISCO-RTTMON-MIB');

    $sla_table = array();
    foreach (explode("\n", $slas) as $sla) {
        $key_val = explode(' ', $sla, 2);
        if (count($key_val) != 2) {
            $key_val[] = '';
        }

        $key   = $key_val[0];
        $value = $key_val[1];

        $prop_id = explode('.', $key);
        if ((count($prop_id) != 2) || !ctype_digit($prop_id[1])) {
            continue;
        }

        $property = $prop_id[0];
        $id       = intval($prop_id[1]);

        $sla_table[$id][$property] = trim($value);
    }

    // var_dump($sla_table);
    // Get existing SLAs
    $existing_slas = dbFetchColumn('SELECT `sla_id` FROM `slas` WHERE `device_id` = :device_id AND `deleted` = 0', array('device_id' => $device['device_id']));

    foreach ($sla_table as $sla_nr => $sla_config) {
    $query_data = array(
                   'device_id' => $device['device_id'],
                   'sla_nr'    => $sla_nr,
                  );
        $sla_id = dbFetchCell('SELECT `sla_id` FROM `slas` WHERE `device_id` = :device_id AND `sla_nr` = :sla_nr', $query_data);

        $data = array(
                 'device_id' => $device['device_id'],
                 'sla_nr'    => $sla_nr,
                 'owner'     => $sla_config['rttMonCtrlAdminOwner'],
                 'tag'       => $sla_config['rttMonCtrlAdminTag'],
                 'rtt_type'  => $sla_config['rttMonCtrlAdminRttType'],
                 'status'    => ($sla_config['rttMonCtrlAdminStatus'] == 'active') ? 1 : 0,
                 'opstatus'  => ($sla_config['rttMonLatestRttOperSense'] == 'ok') ? 0 : 2,
                 'deleted'   => 0,
                );

        // Some fallbacks for when the tag is empty
        if (!$data['tag']) {
            switch ($data['rtt_type']) {
                case 'http':
                    $data['tag'] = $sla_config['rttMonEchoAdminURL'];
                break;

                case 'dns':
                    $data['tag'] = $sla_config['rttMonEchoAdminTargetAddressString'];
                break;

                case 'echo':
                    $parts = explode(' ', $sla_config['rttMonEchoAdminTargetAddress']);
                    if (count($parts) == 4) {
                        // IPv4
                        $data['tag'] = implode('.', array_map('hexdec', $parts));
                    }
                    else if (count($parts) == 16) {
                        // IPv6
                        $data['tag'] = $parts[0].$parts[1].':'.$parts[2].$parts[3].':'.$parts[4].$parts[5].':'.$parts[6].$parts[7].':'.$parts[8].$parts[9].':'.$parts[10].$parts[11].':'.$parts[12].$parts[13].':'.$parts[14].$parts[15];
                        $data['tag'] = preg_replace('/:0*([0-9])/', ':$1', $data['tag']);
                    }
                break;

                case 'jitter':
                    $data['tag'] = $sla_config['rttMonEchoAdminCodecType'] ." (". preg_replace('/milliseconds/', 'ms', $sla_config['rttMonEchoAdminCodecInterval']) .")";
                    break;
            }//end switch
        }//end if

        if (!$sla_id) {
            $sla_id = dbInsert($data, 'slas');
            echo '+';
        }
        else {
            // Remove from the list
            $existing_slas = array_diff($existing_slas, array($sla_id));

            dbUpdate($data, 'slas', '`sla_id` = :sla_id', array('sla_id' => $sla_id));
            echo '.';
        }
    }//end foreach

    // Mark all remaining SLAs as deleted
    foreach ($existing_slas as $existing_sla) {
        dbUpdate(array('deleted' => 1), 'slas', '`sla_id` = :sla_id', array('sla_id' => $existing_sla));
        echo '-';
    }

    echo "\n";
}
