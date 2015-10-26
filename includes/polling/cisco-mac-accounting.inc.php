<?php

// FIXME -- we're walking, so we can discover here too.
if ($device['os_group'] == 'cisco') {
    $cip_oids = array(
        'cipMacHCSwitchedBytes',
        'cipMacHCSwitchedPkts',
    );
    echo 'Cisco MAC - Caching OID: ';
    $cip_array = array();

    foreach ($cip_oids as $oid) {
        echo "$oid ";
        $cip_array = snmpwalk_cache_cip($device, $oid, $cip_array, 'CISCO-IP-STAT-MIB');
    }

    $polled = time();

    $mac_entries = 0;

    $acc_rows = dbFetchRows('SELECT *, A.poll_time AS poll_time FROM `mac_accounting` as A, `ports` AS I where A.port_id = I.port_id AND I.device_id = ?', array($device['device_id']));

    foreach ($acc_rows as $acc) {
        $device_id = $acc['device_id'];
        $ifIndex   = $acc['ifIndex'];
        $mac       = $acc['mac'];

        $polled_period = ($polled - $acc['poll_time']);

        if ($cip_array[$ifIndex][$mac]) {
            $acc['update']['poll_time']   = $polled;
            $acc['update']['poll_prev']   = $acc['poll_time'];
            $acc['update']['poll_period'] = $polled_period;

            $mac_entries++;

            $b_in  = $cip_array[$ifIndex][$mac]['cipMacHCSwitchedBytes']['input'];
            $b_out = $cip_array[$ifIndex][$mac]['cipMacHCSwitchedBytes']['output'];
            $p_in  = $cip_array[$ifIndex][$mac]['cipMacHCSwitchedPkts']['input'];
            $p_out = $cip_array[$ifIndex][$mac]['cipMacHCSwitchedPkts']['output'];

            $this_ma = &$cip_array[$ifIndex][$mac];

            // Update metrics
            foreach ($cip_oids as $oid) {
                foreach (array('input', 'output') as $dir) {
                    $oid_dir                 = $oid.'_'.$dir;
                    $acc['update'][$oid_dir] = $this_ma[$oid][$dir];
                    $acc['update'][$oid_dir.'_prev'] = $acc[$oid_dir];
                    $oid_prev = $oid_dir.'_prev';
                    if ($this_ma[$oid][$dir]) {
                        $oid_diff = ($this_ma[$oid][$dir] - $acc[$oid_dir]);
                        $oid_rate = ($oid_diff / $polled_period);
                        $acc['update'][$oid_dir.'_rate']  = $oid_rate;
                        $acc['update'][$oid_dir.'_delta'] = $oid_diff;
                        d_echo("\n $oid_dir ($oid_diff B) $oid_rate Bps $polled_period secs\n");
                    }
                }
            }

            d_echo("\n".$acc['hostname'].' '.$acc['ifDescr']."  $mac -> $b_in:$b_out:$p_in:$p_out ");

            $rrdfile = $config['rrd_dir'].'/'.$device['hostname'].'/'.safename('cip-'.$acc['ifIndex'].'-'.$acc['mac'].'.rrd');

            if (!is_file($rrdfile)) {
                rrdtool_create(
                    $rrdfile,
                    'DS:IN:COUNTER:600:0:12500000000 
                    DS:OUT:COUNTER:600:0:12500000000 
                    DS:PIN:COUNTER:600:0:12500000000 
                    DS:POUT:COUNTER:600:0:12500000000 '.$config['rrd_rra']
                );
            }

            // FIXME - use memcached to make sure these values don't go backwards?
            $fields = array(
                'IN'   => $b_in,
                'OUT'  => $b_out,
                'PIN'  => $p_in,
                'POUT' => $p_out,
            );
            rrdtool_update($rrdfile, $fields);

            $tags = array('ifIndex' => $acc['ifIndex'], 'mac' => $acc['mac']);
            influx_update($device,'cip',$tags,$fields);

            if ($acc['update']) {
                // Do Updates
                dbUpdate($acc['update'], 'mac_accounting', '`ma_id` = ?', array($acc['ma_id']));
            } //end if
        }//end if
    }//end foreach

    unset($cip_array);

    if ($mac_entries) {
        echo " $mac_entries MAC accounting entries\n";
    }

    echo "\n";
}//end if
