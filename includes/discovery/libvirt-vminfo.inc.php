<?php

use LibreNMS\Config;

// FIXME should do the deletion etc in a common file perhaps? like for the sensors
// Try to discover Libvirt Virtual Machines.
if (Config::get('enable_libvirt') && $device['os'] == 'linux') {
    $libvirt_vmlist = array();

    $ssh_ok = 0;

    $userHostname = $device['hostname'];
    if (Config::has('libvirt_username')) {
        $userHostname = Config::get('libvirt_username').'@'.$userHostname;
    }

    foreach (Config::get('libvirt_protocols') as $method) {
        if (str_contains($method, 'qemu')) {
            $uri = $method.'://'.$userHostname.'/system';
        } else {
            $uri = $method.'://'.$userHostname;
        }

        if (str_contains($method, 'ssh') && !$ssh_ok) {
            // Check if we are using SSH if we can log in without password - without blocking the discovery
            // Also automatically add the host key so discovery doesn't block on the yes/no question, and run echo so we don't get stuck in a remote shell ;-)
            exec('ssh -o "StrictHostKeyChecking no" -o "PreferredAuthentications publickey" -o "IdentitiesOnly yes" '.$userHostname.' echo -e', $out, $ret);
            if ($ret != 255) {
                $ssh_ok = 1;
            }
        }

        if ($ssh_ok || !str_contains($method, 'ssh')) {
            // Fetch virtual machine list
            unset($domlist);
            exec(Config::get('virsh').' -rc '.$uri.' list --uuid --all', $domlist);

            foreach ($domlist as $dom) {
                $dom_id = trim($dom);
                $dom_info = array();

                if (strlen($dom_id) == 36) {
                    // Fetch the Virtual Machine information.
                    unset($vm_info_array);
                    exec(Config::get('virsh').' -rc '.$uri.' dominfo '.$dom_id, $vm_info_array);
                    foreach ($vm_info_array as $line) {
                        list($field, $value) = explode(':', $line, 2);
                        $field = preg_replace('/[\s\(\)]/', '', $field);
                        $value = trim($value);
                        $dom_info[$field] = $value;
                    }
                    d_echo($dom_info);

                    // Convert memory size to MiB
                    list($mem_size, $mem_unit) = explode(' ', $dom_info['Maxmemory'], 2);
                    switch ($mem_unit) {
                        case 'T':
                        case 'TiB':
                            $mem_size = $mem_size * 1048576;
                            break;
                        case 'TB':
                            $mem_size = $mem_size * 1000000;
                            break;
                        case 'G':
                        case 'GiB':
                            $mem_size = $mem_size * 1024;
                            break;
                        case 'GB':
                            $mem_size = $mem_size * 1000;
                            break;
                        case 'M':
                        case 'MiB':
                            break;
                        case 'MB':
                            $mem_size = $mem_size * 1000000 / 1048576;
                            break;
                        case 'KB':
                            $mem_size = $mem_size / 1000;
                            break;
                        case 'b':
                        case 'bytes':
                            $mem_size = $mem_size / 1048576;
                            break;
                        default:
                            // KiB or k or no value
                            $mem_size = $mem_size / 1024;
                            break;
                    }

                    $db_data = array(
                        'vmwVmDisplayName' => mres($dom_info['Name']),
                        'vmwVmGuestOS'     => '',
                        'vmwVmState'       => mres($dom_info['State']),
                        'vmwVmCpus'        => mres($dom_info['CPUs']),
                        'vmwVmMemSize'     => mres($mem_size)
                    );


                    // Check whether the Virtual Machine is already known for this host.
                    $result = dbFetchRow("SELECT * FROM `vminfo` WHERE `device_id` = ? AND `vmwVmVMID` = ? AND `vm_type` = 'libvirt'", array($device['device_id'], $dom_id));
                    if (count($result['device_id']) == 0) {
                        $db_data['device_id'] = $device['device_id'];
                        $db_data['vm_type'] = 'libvirt';
                        $db_data['vmwVmVMID'] = $dom_id;
                        $inserted_id = dbInsert($db_data, 'vminfo');
                        echo '+';
                        log_event('Virtual Machine added: '.$db_data['vmwVmDisplayName'].' ('.$db_data['vmwVmMemSize'].' MB)', $device, 'vm', 3, $inserted_id);
                    } else {
                        $updated = false;
                        foreach (array('State', 'DisplayName', 'Cpus', 'GuestOS', 'MemSize') as $field) {
                            if ($result['vmwVm'.$field] != $db_data['vmwVm'.$field]) {
                                $updated = true;
                                log_event("Virtual Machine: $field: ".$result['vmwVm'.$field]." -> ".$db_data['vmwVm'.$field], $device, 'vm', 3);
                            }
                        }
                        if ($updated) {
                            dbUpdate(db_data, 'vminfo', "device_id=? AND vm_type='libvirt' AND vmwVmVMID=?", array($device['device_id'], $dom_id));
                            echo 'U';
                        } else {
                            echo '.';
                        }
                    }

                    // Save the discovered Virtual Machine.
                    $libvirt_vmlist[] = $dom_id;
                }//end if
            }//end foreach
        }//end if

        // If we found VMs, don't cycle the other protocols anymore.
        if (count($libvirt_vmlist)) {
            break;
        }
    }//end foreach

    // Get a list of all the known Virtual Machines for this host.
    $sql = "SELECT id, vmwVmVMID, vmwVmDisplayName FROM vminfo WHERE device_id = '".$device['device_id']."' AND vm_type='libvirt'";

    foreach (dbFetchRows($sql) as $db_vm) {
        // Delete the Virtual Machines that are removed from the host.
        if (!in_array($db_vm['vmwVmVMID'], $libvirt_vmlist)) {
            dbDelete('vminfo', '`id` = ?', array($db_vm['id']));
            echo '-';
            log_event('Virtual Machine removed: ' . $db_vm['vmwVmDisplayName'], $device, 'vm', 4, $db_vm['id']);
        }
    }

    echo "\n";
}//end if
