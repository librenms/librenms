<?php

use LibreNMS\RRD\RrdDefinition;

if ($device['os_group'] == 'cisco') {
    $acc_rows = dbFetchRows('SELECT *, A.poll_time AS poll_time FROM `mac_accounting` as A, `ports` AS I where A.port_id = I.port_id AND I.device_id = ?', [$device['device_id']]);

    if (! empty($acc_rows)) {
        $cip_oids = [
            'cipMacHCSwitchedBytes',
            'cipMacHCSwitchedPkts',
        ];
        $cip_array = [];

        foreach (array_merge($cip_oids, ['cipMacSwitchedBytes', 'cipMacSwitchedPkts']) as $oid) {
            echo "$oid ";
            $cip_array = snmpwalk_cache_cip($device, $oid, $cip_array, 'CISCO-IP-STAT-MIB');
        }

        // Normalize cip_array
        $cip_array = array_map(function ($entries) {
            return array_map(function ($entry) {
                $new_entry = [];

                foreach (['Bytes', 'Pkts'] as $unit) {
                    $returned_oid = (array_key_exists('cipMacHCSwitched' . $unit, $entry)) ? 'cipMacHCSwitched' : 'cipMacSwitched';
                    $new_value = [];

                    foreach ($entry[$returned_oid . $unit] as $key => $value) {
                        $new_value[$key] = intval($value);
                    }

                    $new_entry['cipMacHCSwitched' . $unit] = $new_value;
                }

                return $new_entry;
            }, $entries);
        }, $cip_array);

        $polled = time();

        $mac_entries = 0;

        foreach ($acc_rows as $acc) {
            $device_id = $acc['device_id'];
            $ifIndex = $acc['ifIndex'];
            $mac = $acc['mac'];

            $polled_period = ($polled - $acc['poll_time']);

            if ($cip_array[$ifIndex][$mac]) {
                $acc['update']['poll_time'] = $polled;
                $acc['update']['poll_prev'] = $acc['poll_time'];
                $acc['update']['poll_period'] = $polled_period;

                $mac_entries++;

                $b_in = $cip_array[$ifIndex][$mac]['cipMacHCSwitchedBytes']['input'];
                $b_out = $cip_array[$ifIndex][$mac]['cipMacHCSwitchedBytes']['output'];
                $p_in = $cip_array[$ifIndex][$mac]['cipMacHCSwitchedPkts']['input'];
                $p_out = $cip_array[$ifIndex][$mac]['cipMacHCSwitchedPkts']['output'];

                $this_ma = &$cip_array[$ifIndex][$mac];

                // Update metrics
                foreach ($cip_oids as $oid) {
                    foreach (['input', 'output'] as $dir) {
                        $oid_dir = $oid . '_' . $dir;
                        $acc['update'][$oid_dir] = $this_ma[$oid][$dir];
                        $acc['update'][$oid_dir . '_prev'] = $acc[$oid_dir];
                        $oid_prev = $oid_dir . '_prev';
                        if ($this_ma[$oid][$dir]) {
                            $oid_diff = ($this_ma[$oid][$dir] - $acc[$oid_dir]);
                            $oid_rate = ($oid_diff / $polled_period);
                            $acc['update'][$oid_dir . '_rate'] = $oid_rate;
                            $acc['update'][$oid_dir . '_delta'] = $oid_diff;
                            d_echo("\n $oid_dir ($oid_diff B) $oid_rate Bps $polled_period secs\n");
                        }
                    }
                }

                d_echo("\n" . $acc['hostname'] . ' ' . $acc['ifDescr'] . "  $mac -> $b_in:$b_out:$p_in:$p_out ");

                $rrd_name = ['cip', $ifIndex, $mac];
                $rrd_def = RrdDefinition::make()
                    ->addDataset('IN', 'COUNTER', 0, 12500000000)
                    ->addDataset('OUT', 'COUNTER', 0, 12500000000)
                    ->addDataset('PIN', 'COUNTER', 0, 12500000000)
                    ->addDataset('POUT', 'COUNTER', 0, 12500000000);

                // FIXME - use memcached to make sure these values don't go backwards?
                $fields = [
                    'IN' => $b_in,
                    'OUT' => $b_out,
                    'PIN' => $p_in,
                    'POUT' => $p_out,
                ];

                $tags = compact('ifIndex', 'mac', 'rrd_name', 'rrd_def');
                data_update($device, 'cip', $tags, $fields);

                if ($acc['update']) {
                    // Do Updates
                    dbUpdate($acc['update'], 'mac_accounting', '`ma_id` = ?', [$acc['ma_id']]);
                } //end if
            }//end if
        }//end foreach

        unset($cip_array);

        if ($mac_entries) {
            echo " $mac_entries MAC accounting entries\n";
        }
    }
    echo "\n";
}//end if

unset(
    $cip_oids,
    $oid,
    $polled,
    $mac_entries,
    $acc_rows
);
