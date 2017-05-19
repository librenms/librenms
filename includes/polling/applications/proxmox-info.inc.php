<?php
//ini_set('display_errors', 'On');
//error_reporting(E_ALL);

//use LibreNMS\RRD\RrdDefinition;

/**
 * Check if a Proxmox VM exists
 *
 * @param  integer $i        VM ID
 * @param  string  $c        Clustername
 * @param  array   $pmxcache Reference to the Proxmox VM Cache
 * @return boolean true if the VM exists, false if it doesn't
 */
function proxmox_info_vm_exists($i, $c, &$pmxcache)
{

    if (isset($pmxcache[$c][$i]) && $pmxcache[$c][$i] > 0) {
        return true;
    }
    if ($row = dbFetchRow("SELECT id FROM proxmox WHERE vmid = ? AND cluster = ?", array($i, $c))) {
        $pmxcache[$c][$i] = (integer) $row['id'];
        return true;
    }

    return false;
}

$name = 'proxmox-info';
$app_id = $app['app_id'];
if (isset($config['enable_proxmox']) && $config['enable_proxmox'] && !empty($agent_data['app'][$name])) {
    $proxmox = $agent_data['app'][$name];
} elseif (isset($config['enable_proxmox']) && $config['enable_proxmox']) {
    $options = '-O qv';
    $oid     = '.1.3.6.1.4.1.8072.1.3.2.3.1.2.12.112.114.111.120.109.111.120.45.105.110.102.111';
    $proxmox = snmp_get($device, $oid, $options);
    $proxmox = preg_replace('/^.+\n/', '', $proxmox);
    $proxmox = str_replace("<<<app-proxmox-info>>>\n", '', $proxmox);
}

if ($proxmox) {
    update_application($app, $proxmox);
    $pmxlines = explode("\n", $proxmox);
    $pmxcluster = array_shift($pmxlines);
    dbUpdate(
        array('device_id' => $device['device_id'], 'app_type' => $name, 'app_instance' => $pmxcluster),
        'applications',
        '`device_id` = ? AND `app_type` = ?',
        array($device['device_id'], "proxmox")
    );
    $vmsInDatabase = dbFetchRows("SELECT vmid FROM proxmox WHERE cluster = ?", array($pmxcluster));
    print_r($vmsInDatabase);

    if (count($pmxlines) > 0) {
        $pmxcache = array();

        foreach ($pmxlines as $vm) {
            $vm = str_replace('"', '', $vm);
            //list($vmid, $vmport, $vmpin, $vmpout, $vmdesc) = explode(' ', $vm, 5);
            list($vmid, $vmstatus, $vmdesc, $vmtype, $vmcpus, $vmuptime, $vmpid, $vmmem, $vmmaxmem, $vmdisk, $vmmaxdisk) = explode(' ', $vm, 11);
            print "Proxmox ($pmxcluster): $vmdesc: $vmstatus/$vmmem/$vmcpu/$vmstorage\n";

            foreach ($vmsInDatabase as $key => $vmInDB) {
                if ($vmInDB['vmid'] == $vmid) {
                    print "UNSET KEY: $key";
                    unset($vmsInDatabase[$key]);
                }
            }

            //$tags = compact('name', 'app_id', 'pmxcluster', 'vmid', 'vmport', 'rrd_proxmox_name', 'rrd_def');
            //data_update($device, 'app', $tags, $fields);

            $vmmem = intval($vmmem);
            $vmmaxmem = intval($vmmaxmem);
            $vmdisk = intval($vmdisk);
            $vmmaxdisk = intval($vmmaxdisk);

            $vmmemuse = round(($vmmem / $vmmaxmem) * 100, 2);
            $vmdiskuse = round(($vmdisk / $vmmaxdisk) * 100, 2);

            print "VMUSE: $vmmemuse $vmdiskuse";

            if (proxmox_info_vm_exists($vmid, $pmxcluster, $pmxcache) === true) {
                dbUpdate(
                    array(
                    'device_id' => $device['device_id'],
                    'last_seen' => array('NOW()'),
                    'description' => $vmdesc,
                    'vmid' => $vmid,
                    'vmstatus' => $vmstatus,
                    'vmtype' => $vmtype,
                    'vmcpus' => $vmcpus,
                    'vmuptime' => $vmuptime,
                    'vmpid' => $vmpid,
                    'vmmem' => $vmmem,
                    'vmmaxmem' => $vmmaxmem,
                    'vmdisk' => $vmdisk,
                    'vmmaxdisk' => $vmmaxdisk,
                    'vmmemuse' => $vmmemuse,
                    'vmdiskuse' => $vmdiskuse,
                    ),
                    "proxmox",
                    '`vmid` = ? AND `cluster` = ?',
                    array($vmid, $pmxcluster)
                );
            } else {
                $pmxcache[$pmxcluster][$vmid] = dbInsert(
                    array(
                    'cluster' => $pmxcluster,
                    'vmid' => $vmid,
                    'description' => $vmdesc,
                    'device_id' => $device['device_id'],
                    'vmstatus' => $vmstatus,
                    'vmtype' => $vmtype,
                    'vmcpus' => $vmcpus,
                    'vmuptime' => $vmuptime,
                    'vmpid' => $vmpid,
                    'vmmem' => $vmmem,
                    'vmmaxmem' => $vmmaxmem,
                    'vmdisk' => $vmdisk,
                    'vmmaxdisk' => $vmmaxdisk,
                    'vmmemuse' => $vmmemuse,
                    'vmdiskuse' => $vmdiskuse,
                    ),
                    "proxmox"
                );
            }
        }
        print_r($vmsInDatabase);
        if (count($vmsInDatabase) > 0) {
            foreach ($vmsInDatabase as $key => $vmInDB) {
                print "DELETE KEY: $key";
                dbDelete('proxmox', 'vmid = ?', array($vmInDB['vmid']));
            }
        }
    }
}

unset($pmxlines, $pmxcluster, $proxmox, $pmxcache);
