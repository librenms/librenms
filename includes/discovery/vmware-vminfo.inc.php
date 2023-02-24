<?php

use LibreNMS\Enum\PowerState;

// FIXME should do the deletion etc in a common file perhaps? like for the sensors
/*
 * Try to discover any Virtual Machines.
 */

if (($device['os'] == 'vmware-esxi') || ($device['os'] == 'linux')) {
    /*
     * Variable to hold the discovered Virtual Machines.
     */

    $vmw_vmlist = [];

    /*
     * CONSOLE: Start the VMware discovery process.
     */

    /*
     * Fetch information about Virtual Machines.
     */

    $oids = snmpwalk_cache_multi_oid($device, 'vmwVmTable', [], '+VMWARE-ROOT-MIB:VMWARE-VMINFO-MIB', 'vmware');

    foreach ($oids as $index => $entry) {
        $vmwVmDisplayName = $entry['vmwVmDisplayName'];
        $vmwVmGuestOS = $entry['vmwVmGuestOS'];
        $vmwVmMemSize = $entry['vmwVmMemSize'];
        $vmwVmState = PowerState::STATES[strtolower($entry['vmwVmState'])] ?? PowerState::UNKNOWN;
        $vmwVmCpus = $entry['vmwVmCpus'];

        /*
         * VMware does not return an INTEGER but a STRING of the vmwVmMemSize. This bug
         * might be resolved by VMware in the future making this code obsolete.
         */
        if (preg_match('/^([0-9]+) .*$/', $vmwVmMemSize, $matches)) {
            $vmwVmMemSize = $matches[1];
        }

        /*
         * Check whether the Virtual Machine is already known for this host.
         */
        if (dbFetchCell("SELECT COUNT(id) FROM `vminfo` WHERE `device_id` = ? AND `vmwVmVMID` = ? AND vm_type='vmware'", [$device['device_id'], $index]) == 0) {
            $vmid = dbInsert(['device_id' => $device['device_id'], 'vm_type' => 'vmware', 'vmwVmVMID' => $index, 'vmwVmDisplayName' => $vmwVmDisplayName, 'vmwVmGuestOS' => $vmwVmGuestOS, 'vmwVmMemSize' => $vmwVmMemSize, 'vmwVmCpus' => $vmwVmCpus, 'vmwVmState' => $vmwVmState], 'vminfo');
            log_event($vmwVmDisplayName . " ($vmwVmMemSize GB / $vmwVmCpus vCPU) Discovered", $device, 'system', 3, $vmid);
            echo '+';
        // FIXME eventlog
        } else {
            echo '.';
        }

        /*
         * Save the discovered Virtual Machine.
         */

        $vmw_vmlist[] = $index;
    }

    /*
     * Get a list of all the known Virtual Machines for this host.
     */

    $sql = "SELECT id, vmwVmVMID, vmwVmDisplayName FROM vminfo WHERE device_id = '" . $device['device_id'] . "' AND vm_type='vmware'";

    foreach (dbFetchRows($sql) as $db_vm) {
        /*
         * Delete the Virtual Machines that are removed from the host.
         */

        if (! in_array($db_vm['vmwVmVMID'], $vmw_vmlist)) {
            dbDelete('vminfo', '`id` = ?', [$db_vm['id']]);
            log_event($db_vm['vmwVmDisplayName'] . ' Removed', $device, 'system', 4, $db_vm['vmwVmVMID']);
            echo '-';
            // FIXME eventlog
        }
    }

    /*
     * Finished discovering VMware information.
     */

    echo "\n";
}//end if
