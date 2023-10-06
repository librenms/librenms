<?php

use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\Mac;

if ($device['os_group'] == 'cisco') {
    $acc_rows = dbFetchRows('SELECT *, A.poll_time AS poll_time FROM `mac_accounting` as A, `ports` AS I where A.port_id = I.port_id AND I.device_id = ?', [$device['device_id']]);

    if (! empty($acc_rows)) {
        $cip_oids = [
            'cipMacHCSwitchedBytes',
            'cipMacHCSwitchedPkts',
        ];

        $cip_response = SnmpQuery::walk([
            'CISCO-IP-STAT-MIB::cipMacHCSwitchedBytes',
            'CISCO-IP-STAT-MIB::cipMacHCSwitchedPkts',
        ]);
        if (! $cip_response->isValid()) {
            $cip_response = SnmpQuery::walk([
                'CISCO-IP-STAT-MIB::cipMacSwitchedBytes',
                'CISCO-IP-STAT-MIB::cipMacSwitchedPkts',
            ]);
        }

        // Normalize cip_array
        $cip_array = [];
        foreach ($cip_response->table(3) as $ifIndex => $port_data) {
            foreach ($port_data as $direction => $dir_data) {
                foreach ($dir_data as $mac => $mac_data) {
                    $mac = Mac::parse($mac)->hex();
                    $cip_array[$ifIndex][$mac]['cipMacHCSwitchedBytes'][$direction] = $mac_data['CISCO-IP-STAT-MIB::cipMacHCSwitchedBytes'] ?? $mac_data['CISCO-IP-STAT-MIB::cipMacSwitchedBytes'] ?? null;
                    $cip_array[$ifIndex][$mac]['cipMacHCSwitchedPkts'][$direction] = $mac_data['CISCO-IP-STAT-MIB::cipMacHCSwitchedPkts'] ?? $mac_data['CISCO-IP-STAT-MIB::cipMacSwitchedPkts'] ?? null;
                }
            }
        }

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

                $b_in = $cip_array[$ifIndex][$mac]['cipMacHCSwitchedBytes']['input'] ?? null;
                $b_out = $cip_array[$ifIndex][$mac]['cipMacHCSwitchedBytes']['output'] ?? null;
                $p_in = $cip_array[$ifIndex][$mac]['cipMacHCSwitchedPkts']['input'] ?? null;
                $p_out = $cip_array[$ifIndex][$mac]['cipMacHCSwitchedPkts']['output'] ?? null;

                $this_ma = &$cip_array[$ifIndex][$mac];

                // Update metrics
                foreach ($cip_oids as $oid) {
                    foreach (['input', 'output'] as $dir) {
                        $oid_dir = $oid . '_' . $dir;
                        $acc['update'][$oid_dir] = $this_ma[$oid][$dir] ?? null;
                        $acc['update'][$oid_dir . '_prev'] = $acc[$oid_dir];
                        $oid_prev = $oid_dir . '_prev';
                        if (isset($this_ma[$oid][$dir])) {
                            $oid_diff = ($this_ma[$oid][$dir] - $acc[$oid_dir]);
                            $oid_rate = ($oid_diff / $polled_period);
                            $acc['update'][$oid_dir . '_rate'] = $oid_rate;
                            $acc['update'][$oid_dir . '_delta'] = $oid_diff;
                            d_echo("\n $oid_dir ($oid_diff B) $oid_rate Bps $polled_period secs\n");
                        }
                    }
                }

                d_echo("\nDevice id: " . $acc['device_id'] . ' -> ' . $acc['ifDescr'] . "  $mac -> $b_in:$b_out:$p_in:$p_out ");

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
    $cip_response,
    $oid,
    $polled,
    $mac_entries,
    $acc_rows
);
