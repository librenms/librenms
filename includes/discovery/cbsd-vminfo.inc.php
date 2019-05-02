<?php

use LibreNMS\Exceptions\JsonAppException;

require_once 'includes/polling/functions.inc.php';

/*
 * Try to discover any Virtual Machines.
 */
if ($device['os'] == 'freebsd') {
    /*
     * Variable to hold the discovered Virtual Machines.
     */

    $vmlist = array();

    /*
     * CONSOLE: Start the VMware discovery process.
     */

    /*
     * Fetch information about Virtual Machines.
     */
    try {
        $cbsd=json_app_get($device, 'cbsd', 1);
    } catch (JsonAppException $e) {
        # This is just empty and exists to catch it not existing.
    }

    $cbsd_keys=array_keys($cbsd[data]);

    foreach ($cbsd_keys as $vm) {
        $vmwVmDisplayName = $cbsd{data}{$vm}{jname};
        $vmwVmGuestOS     = $cbsd{data}{$vm}{vm_os_type};
        $vmwVmMemSize     = $cbsd{data}{$vm}{vm_ram};
        $vmwVmState       = $cbsd{data}{$vm}{status};
        $vmwVmCpus        = $cbsd{data}{$vm}{vm_cpus};

        if ($vmwVmState == 'Off') {
            $vmwVmState="powered off";
        } elseif ($vmwVmState == 'On') {
            $vmwVmState="powered on";
        }

        /*
         * Check whether the Virtual Machine is already known for this host.
         */
        if (dbFetchCell("SELECT COUNT(id) FROM `vminfo` WHERE `device_id` = ? AND `vmwVmVMID` = ? AND vm_type='cbsd'", array($device['device_id'], $index)) == 0) {
            $vmid = dbInsert(array('device_id' => $device['device_id'], 'vm_type' => 'cbsd', 'vmwVmVMID' => $index, 'vmwVmDisplayName' => mres($vmwVmDisplayName), 'vmwVmGuestOS' => mres($vmwVmGuestOS), 'vmwVmMemSize' => mres($vmwVmMemSize), 'vmwVmCpus' => mres($vmwVmCpus), 'vmwVmState' => mres($vmwVmState)), 'vminfo');
            log_event(mres($vmwVmDisplayName) . " ($vmwVmMemSize GB / $vmwVmCpus vCPU) Discovered", $device, 'system', 3, $vmid);
            echo '+';
            // FIXME eventlog
        } else {
            echo '.';
        }

        /*
         * Save the discovered Virtual Machine.
         */

        $vmlist[] = array(
            vmwVmDisplayName=>$vmwVmDisplayName,
            vmwVmGuestOS=>$vmwVmGuestOS,
            vmwVmMemSize=>$vmwVmMemSize,
            vmwVmState=>$vmwVmState,
            vmwVmCpus=>$vmwVmCpus,
        );
    }

    /*
     * Get a list of all the known Virtual Machines for this host.
     */

    $sql = "SELECT id, vmwVmVMID, vmwVmDisplayName FROM vminfo WHERE device_id = '".$device['device_id']."' AND vm_type='cbsd'";

    foreach (dbFetchRows($sql) as $db_vm) {
        /*
         * Delete the Virtual Machines that are removed from the host.
         */

        if (!in_array($db_vm['vmwVmVMID'], $vmlist)) {
            dbDelete('vminfo', '`id` = ?', array($db_vm['id']));
            log_event(mres($db_vm['vmwVmDisplayName']) . ' Removed', $device, 'system', 4, $db_vm['vmwVmVMID']);
            echo '-';
            // FIXME eventlog
        }
    }

    /*
     * Finished discovering VMware information.
     */

    echo "\n";
}//end if
