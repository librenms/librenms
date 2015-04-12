<?php

// FIXME should do the deletion etc in a common file perhaps? like for the sensors

/*
 * Try to discover any Virtual Machines.
 */

if (($device['os'] == "vmware") || ($device['os'] == "linux"))
{
  /*
   * Variable to hold the discovered Virtual Machines.
   */

  $vmw_vmlist = array();

  /*
   * CONSOLE: Start the VMware discovery process.
   */

  echo("VMware VM: ");

  /*
   * Fetch the list is Virtual Machines.
   *
   *  vmwVmVMID.224 = INTEGER: 224
   *  vmwVmVMID.416 = INTEGER: 416
   *  ...
   */

  $oids = trim(snmp_walk($device, "vmwVmUUID", "-Osq", "+VMWARE-ROOT-MIB:VMWARE-VMINFO-MIB", "+" . $config["install_dir"] . "/mibs/vmware:" . $config["install_dir"] . "/mibs"));
  $oids = str_replace("vmwVmUUID.", "", $oids);
  if ($oids != "")
  {
    $oids = explode("\n", $oids);

    foreach ($oids as $data)
    {
        $data = trim($data);
        list($oid,) = explode(" ", $data);
      /*
       * Fetch the Virtual Machine information.
       *
       *  vmwVmDisplayName.224 = STRING: My First VM
       *  vmwVmDisplayName.416 = STRING: My Second VM
       *  vmwVmGuestOS.224 = STRING: windows7Server64Guest
       *  vmwVmGuestOS.416 = STRING: winLonghornGuest
       *  vmwVmMemSize.224 = INTEGER: 8192 megabytes
       *  vmwVmMemSize.416 = INTEGER: 8192 megabytes
       *  vmwVmState.224 = STRING: poweredOn
       *  vmwVmState.416 = STRING: poweredOn
       *  vmwVmVMID.224 = INTEGER: 224
       *  vmwVmVMID.416 = INTEGER: 416
       *  vmwVmCpus.224 = INTEGER: 2
       *  vmwVmCpus.416 = INTEGER: 2
       */


      $vmwVmDisplayName = snmp_get($device, "vmwVmDisplayName." . $oid, "-Osqnv", "+VMWARE-ROOT-MIB:VMWARE-VMINFO-MIB", "+" . $config["install_dir"] . "/mibs/vmware:" . $config["install_dir"] . "/mibs");
      $vmwVmGuestOS   = snmp_get($device, "vmwVmGuestOS."   . $oid, "-Osqnv", "+VMWARE-ROOT-MIB:VMWARE-VMINFO-MIB", "+" . $config["install_dir"] . "/mibs/vmware:" . $config["install_dir"] . "/mibs");
      $vmwVmMemSize   = snmp_get($device, "vmwVmMemSize."   . $oid, "-Osqnv", "+VMWARE-ROOT-MIB:VMWARE-VMINFO-MIB", "+" . $config["install_dir"] . "/mibs/vmware:" . $config["install_dir"] . "/mibs");
      $vmwVmState     = snmp_get($device, "vmwVmState."     . $oid, "-Osqnv", "+VMWARE-ROOT-MIB:VMWARE-VMINFO-MIB", "+" . $config["install_dir"] . "/mibs/vmware:" . $config["install_dir"] . "/mibs");
      $vmwVmCpus    = snmp_get($device, "vmwVmCpus."    . $oid, "-Osqnv", "+VMWARE-ROOT-MIB:VMWARE-VMINFO-MIB", "+" . $config["install_dir"] . "/mibs/vmware:" . $config["install_dir"] . "/mibs");

      /*
       * VMware does not return an INTEGER but a STRING of the vmwVmMemSize. This bug
       * might be resolved by VMware in the future making this code obsolete.
       */

      if (preg_match("/^([0-9]+) .*$/", $vmwVmMemSize, $matches))
      {
        $vmwVmMemSize = $matches[1];
      }

      /*
       * Check whether the Virtual Machine is already known for this host.
       */

      if (dbFetchCell("SELECT COUNT(id) FROM `vminfo` WHERE `device_id` = ? AND `vmwVmVMID` = ? AND vm_type='vmware'",array($device['device_id'], $oid)) == 0)
      {
        dbInsert(array('device_id' => $device['device_id'], 'vm_type' => 'vmware', 'vmwVmVMID' => $oid,'vmwVmDisplayName' => mres($vmwVmDisplayName), 'vmwVmGuestOS' => mres($vmwVmGuestOS), 'vmwVmMemSize' => mres($vmwVmMemSize), 'vmwVmCpus' => mres($vmwVmCpus), 'vmwVmState' => mres($vmwVmState)), 'vminfo');
        echo("+");
        // FIXME eventlog
      } else {
        echo(".");
      }
      // FIXME update code!

      /*
       * Save the discovered Virtual Machine.
       */

      $vmw_vmlist[] = $oid;
    }
  }

  /*
   * Get a list of all the known Virtual Machines for this host.
   */

  $sql = "SELECT id, vmwVmVMID FROM vminfo WHERE device_id = '" . $device["device_id"] . "' AND vm_type='vmware'";

  foreach (dbFetchRows($sql) as $db_vm)
  {
    /*
     * Delete the Virtual Machines that are removed from the host.
     */

    if (!in_array($db_vm["vmwVmVMID"], $vmw_vmlist))
    {
      dbDelete('vminfo', '`id` = ?', array($db_vm['id']));
      echo("-");
      // FIXME eventlog
    }
  }

  /*
   * Finished discovering VMware information.
   */

  echo("\n");
}

?>
