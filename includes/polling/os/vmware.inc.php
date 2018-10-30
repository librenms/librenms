<?php

/*
 * Fetch the VMware product version.
 *
 *  VMWARE-SYSTEM-MIB::vmwProdName.0 = STRING: VMware ESXi
 *  VMWARE-SYSTEM-MIB::vmwProdVersion.0 = STRING: 4.1.0
 *  VMWARE-SYSTEM-MIB::vmwProdBuild.0 = STRING: 348481
 *
 *  version:   ESXi 4.1.0
 *  features:  build-348481
 */

$data     = snmp_get_multi($device, 'VMWARE-SYSTEM-MIB::vmwProdName.0 VMWARE-SYSTEM-MIB::vmwProdVersion.0 VMWARE-SYSTEM-MIB::vmwProdBuild.0', '-OQUs', '+VMWARE-ROOT-MIB:VMWARE-SYSTEM-MIB:VMWARE-VMINFO-MIB', 'vmware');

$hardware_snmp = snmp_get($device, 'entPhysicalDescr.1', '-OsvQU', 'ENTITY-MIB');

if (preg_match('/VMware-vCenter-Server-Appliance/', $data[0]['vmwProdBuild'])) {
    preg_match('/^(?>\S+\s){1,2}/', $device['sysDescr'], $ver);
    $version = $ver[0];

    preg_match('/(\d){7}/', $device['sysDescr'], $feat);
    $features = 'build-'.$feat[0];

    preg_match('/^(?>\S+\s*){1,4}/', $hardware_snmp, $hard);
    $hardware = rtrim($hard[0]);
} else {
    $version  = preg_replace('/^VMware /', '', $data[0]['vmwProdName']).' '.$data[0]['vmwProdVersion'];
    $features = 'build-'.$data[0]['vmwProdBuild'];
    $hardware = $hardware_snmp;
}

$serial   = snmp_get($device, 'entPhysicalSerialNum.1', '-OsvQU', 'ENTITY-MIB');

# Clean up Generic hardware descriptions
$hardware = rewrite_generic_hardware($hardware);

/*
 * CONSOLE: Start the VMware discovery process.
 */

echo 'VMware VM: ';

/*
 * Get a list of all the known Virtual Machines for this host.
 */

$db_info_list = dbFetchRows('SELECT id, vmwVmVMID, vmwVmDisplayName, vmwVmGuestOS, vmwVmMemSize, vmwVmCpus, vmwVmState FROM vminfo WHERE device_id = ?', array($device['device_id']));
$current_vminfo = snmpwalk_cache_multi_oid($device, 'vmwVmTable', array(), '+VMWARE-ROOT-MIB:VMWARE-VMINFO-MIB', 'vmware');

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

    $vm_info = array();

    $vm_info['vmwVmDisplayName'] = $current_vminfo[$db_info['vmwVmVMID']]['vmwVmDisplayName'];
    $vm_info['vmwVmGuestOS']     = $current_vminfo[$db_info['vmwVmVMID']]['vmwVmGuestOS'];
    $vm_info['vmwVmMemSize']     = $current_vminfo[$db_info['vmwVmVMID']]['vmwVmMemSize'];
    $vm_info['vmwVmState']       = $current_vminfo[$db_info['vmwVmVMID']]['vmwVmState'];
    $vm_info['vmwVmCpus']        = $current_vminfo[$db_info['vmwVmVMID']]['vmwVmCpus'];

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
            dbUpdate(array($property => $vm_info[$property]), 'vminfo', '`id` = ?', array($db_info['id']));
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
