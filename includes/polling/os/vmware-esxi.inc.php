<?php

use LibreNMS\Enum\PowerState;

/*
 * CONSOLE: Start the VMware discovery process.
 */

echo 'VMware VM: ';

/*
 * Get a list of all the known Virtual Machines for this host.
 */

$db_info_list = dbFetchRows('SELECT id, vmwVmVMID, vmwVmDisplayName, vmwVmGuestOS, vmwVmMemSize, vmwVmCpus, vmwVmState FROM vminfo WHERE device_id = ?', [$device['device_id']]);
$current_vminfo = snmpwalk_cache_multi_oid($device, 'vmwVmTable', [], '+VMWARE-ROOT-MIB:VMWARE-VMINFO-MIB', 'vmware');

foreach ($db_info_list as $db_info) {
    /*
     * Fetch the Virtual Machine information.
     *
     *  VMWARE-VMINFO-MIB::vmwVmDisplayName.224 = STRING: My First VM
     *  VMWARE-VMINFO-MIB::vmwVmGuestOS.224 = STRING: windows7Server64Guest
     *  VMWARE-VMINFO-MIB::vmwVmMemSize.224 = INTEGER: 8192 megabytes
     *  VMWARE-VMINFO-MIB::vmwVmState.224 = STRING: poweredOn
     *  VMWARE-VMINFO-MIB::vmwVmVMID.224 = INTEGER: 224
     *  VMWARE-VMINFO-MIB::vmwVmCpus.224 = INTEGER: 2
     */

    $vm_info = [];

    $vm_info['vmwVmDisplayName'] = $current_vminfo[$db_info['vmwVmVMID']]['vmwVmDisplayName'];
    $vm_info['vmwVmGuestOS'] = $current_vminfo[$db_info['vmwVmVMID']]['vmwVmGuestOS'];
    $vm_info['vmwVmMemSize'] = $current_vminfo[$db_info['vmwVmVMID']]['vmwVmMemSize'];
    $vm_info['vmwVmState'] = PowerState::STATES[$current_vminfo[$db_info['vmwVmVMID']]['vmwVmState']] ?? PowerState::UNKNOWN;
    $vm_info['vmwVmCpus'] = $current_vminfo[$db_info['vmwVmVMID']]['vmwVmCpus'];

    /*
     * VMware does not return an INTEGER but a STRING of the vmwVmMemSize. This bug
     * might be resolved by VMware in the future making this code absolete.
     */

    if (preg_match('/^([0-9]+) .*$/', $vm_info['vmwVmMemSize'], $matches)) {
        $vm_info['vmwVmMemSize'] = $matches[1];
    }

    /*
     * If VMware Tools is not running then don't overwrite the GuesOS with the error
     * message, but just leave it as it currently is.
     */
    if (stristr($vm_info['vmwVmGuestOS'], 'tools not running') !== false) {
        $vm_info['vmwVmGuestOS'] = $db_info['vmwVmGuestOS'];
    }

    /*
     * Process all the VMware Virtual Machine properties.
     */

    foreach ($vm_info as $property => $value) {
        /*
         * Check the property for any modifications.
         */

        if ($vm_info[$property] != $db_info[$property]) {
            // FIXME - this should loop building a query and then run the query after the loop (bad geert!)
            dbUpdate([$property => $vm_info[$property]], 'vminfo', '`id` = ?', [$db_info['id']]);
            if ($db_info['vmwVmDisplayName'] != null) {
                log_event($db_info['vmwVmDisplayName'] . ' (' . preg_replace('/^vmwVm/', '', $property) . ') -> ' . $vm_info[$property], $device, null, 3);
            }
        }
    }
}//end foreach

/*
 * Finished discovering VMware information.
 */

echo "\n";
