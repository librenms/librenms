<?php

use LibreNMS\RRD\RrdDefinition;

if (! function_exists('proxmox_port_exists')) {
    /**
     * Check if a port on a Proxmox VM exists
     * @param string $p Port name
     * @param string $c Clustername
     * @param int $i VM ID
     * @return int|bool The port-ID if the port exists, false if it doesn't exist
     */
    function proxmox_port_exists($i, $c, $p)
    {
        if ($row = dbFetchRow('SELECT pmp.id FROM proxmox_ports pmp, proxmox pm WHERE pm.id = pmp.vm_id AND pmp.port = ? AND pm.cluster = ? AND pm.vmid = ?', [$p, $c, $i])) {
            return $row['id'];
        }

        return false;
    }
}

if (! function_exists('proxmox_vm_exists')) {
    /**
     * Check if a Proxmox VM exists
     * @param int $i VM ID
     * @param string $c Clustername
     * @param array $pmxcache Reference to the Proxmox VM Cache
     * @return bool true if the VM exists, false if it doesn't
     */
    function proxmox_vm_exists($i, $c, &$pmxcache)
    {
        if (isset($pmxcache[$c][$i]) && $pmxcache[$c][$i] > 0) {
            return true;
        }
        if ($row = dbFetchRow('SELECT id FROM proxmox WHERE vmid = ? AND cluster = ?', [$i, $c])) {
            $pmxcache[$c][$i] = (int) $row['id'];

            return true;
        }

        return false;
    }
}

$name = 'proxmox';
$app_id = $app['app_id'];

if (\LibreNMS\Config::get('enable_proxmox') && ! empty($agent_data['app'][$name])) {
    $proxmox = $agent_data['app'][$name];
} elseif (\LibreNMS\Config::get('enable_proxmox')) {
    $options = '-Oqv';
    $oid = '.1.3.6.1.4.1.8072.1.3.2.3.1.2.7.112.114.111.120.109.111.120';
    $proxmox = snmp_get($device, $oid, $options);
    $proxmox = preg_replace('/^.+\n/', '', $proxmox);
    $proxmox = str_replace("<<<app-proxmox>>>\n", '', $proxmox);
}

if ($proxmox) {
    $pmxlines = explode("\n", $proxmox);
    $pmxcluster = array_shift($pmxlines);
    dbUpdate(
        ['device_id' => $device['device_id'], 'app_type' => $name, 'app_instance' => $pmxcluster],
        'applications',
        '`device_id` = ? AND `app_type` = ?',
        [$device['device_id'], $name]
    );

    $metrics = [];
    if (count($pmxlines) > 0) {
        $pmxcache = [];

        foreach ($pmxlines as $vm) {
            $vm = str_replace('"', '', $vm);
            [$vmid, $vmport, $vmpin, $vmpout, $vmdesc] = explode('/', $vm, 5);
            echo "Proxmox ($pmxcluster): $vmdesc: $vmpin/$vmpout/$vmport\n";

            $rrd_proxmox_name = [
                'pmxcluster' => $pmxcluster,
                'vmid' => $vmid,
                'vmport' => $vmport,
            ];
            $rrd_def = RrdDefinition::make()
                ->addDataset('INOCTETS', 'DERIVE', 0, 12500000000)
                ->addDataset('OUTOCTETS', 'DERIVE', 0, 12500000000);
            $fields = [
                'INOCTETS' => $vmpin,
                'OUTOCTETS' => $vmpout,
            ];

            $proxmox_metric_prefix = "pmxcluster{$pmxcluster}_vmid{$vmid}_vmport$vmport";
            $metrics[$proxmox_metric_prefix] = $fields;
            $tags = compact('name', 'app_id', 'pmxcluster', 'vmid', 'vmport', 'rrd_proxmox_name', 'rrd_def');
            data_update($device, 'app', $tags, $fields);

            if (proxmox_vm_exists($vmid, $pmxcluster, $pmxcache) === true) {
                dbUpdate([
                    'device_id' => $device['device_id'],
                    'last_seen' => ['NOW()'],
                    'description' => $vmdesc,
                ], $name, '`vmid` = ? AND `cluster` = ?', [$vmid, $pmxcluster]);
            } else {
                $pmxcache[$pmxcluster][$vmid] = dbInsert([
                    'cluster' => $pmxcluster,
                    'vmid' => $vmid,
                    'description' => $vmdesc,
                    'device_id' => $device['device_id'],
                ], $name);
            }

            if ($portid = proxmox_port_exists($vmid, $pmxcluster, $vmport) !== false) {
                dbUpdate(
                    ['last_seen' => ['NOW()']],
                    'proxmox_ports',
                    '`vm_id` = ? AND `port` = ?',
                    [$pmxcache[$pmxcluster][$vmid], $vmport]
                );
            } else {
                dbInsert(['vm_id' => $pmxcache[$pmxcluster][$vmid], 'port' => $vmport], 'proxmox_ports');
            }
        }
    }

    update_application($app, $proxmox, $metrics);
}

unset($pmxlines, $pmxcluster, $pmxcdir, $proxmox, $pmxcache);
