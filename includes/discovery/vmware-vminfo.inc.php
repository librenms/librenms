<?php

# FIXME should do the deletion etc in a common file perhaps? like for the sensors

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
   *  VMWARE-VMINFO-MIB::vmwVmVMID.224 = INTEGER: 224
   *  VMWARE-VMINFO-MIB::vmwVmVMID.416 = INTEGER: 416
   *  ...
   */

  $oids = snmp_walk($device, "VMWARE-VMINFO-MIB::vmwVmVMID", "-Osqnv", "+VMWARE-ROOT-MIB:VMWARE-VMINFO-MIB", "+" . $config["install_dir"] . "/mibs/vmware:" . $config["install_dir"] . "/mibs");
  if ($oids != "")
  {
    $oids = explode("\n", $oids);

    foreach ($oids as $oid)
    {
      /*
       * Fetch the Virtual Machine information.
       *
       *  VMWARE-VMINFO-MIB::vmwVmDisplayName.224 = STRING: My First VM
       *  VMWARE-VMINFO-MIB::vmwVmDisplayName.416 = STRING: My Second VM
       *  VMWARE-VMINFO-MIB::vmwVmGuestOS.224 = STRING: windows7Server64Guest
       *  VMWARE-VMINFO-MIB::vmwVmGuestOS.416 = STRING: winLonghornGuest
       *  VMWARE-VMINFO-MIB::vmwVmMemSize.224 = INTEGER: 8192 megabytes
       *  VMWARE-VMINFO-MIB::vmwVmMemSize.416 = INTEGER: 8192 megabytes
       *  VMWARE-VMINFO-MIB::vmwVmState.224 = STRING: poweredOn
       *  VMWARE-VMINFO-MIB::vmwVmState.416 = STRING: poweredOn
       *  VMWARE-VMINFO-MIB::vmwVmVMID.224 = INTEGER: 224
       *  VMWARE-VMINFO-MIB::vmwVmVMID.416 = INTEGER: 416
       *  VMWARE-VMINFO-MIB::vmwVmCpus.224 = INTEGER: 2
       *  VMWARE-VMINFO-MIB::vmwVmCpus.416 = INTEGER: 2
       */

      $vmwVmDisplayName = snmp_get($device, "VMWARE-VMINFO-MIB::vmwVmDisplayName." . $oid, "-Osqnv", "+VMWARE-ROOT-MIB:VMWARE-VMINFO-MIB", "+" . $config["install_dir"] . "/mibs/vmware:" . $config["install_dir"] . "/mibs");
      $vmwVmGuestOS   = snmp_get($device, "VMWARE-VMINFO-MIB::vmwVmGuestOS."   . $oid, "-Osqnv", "+VMWARE-ROOT-MIB:VMWARE-VMINFO-MIB", "+" . $config["install_dir"] . "/mibs/vmware:" . $config["install_dir"] . "/mibs");
      $vmwVmMemSize   = snmp_get($device, "VMWARE-VMINFO-MIB::vmwVmMemSize."   . $oid, "-Osqnv", "+VMWARE-ROOT-MIB:VMWARE-VMINFO-MIB", "+" . $config["install_dir"] . "/mibs/vmware:" . $config["install_dir"] . "/mibs");
      $vmwVmState     = snmp_get($device, "VMWARE-VMINFO-MIB::vmwVmState."     . $oid, "-Osqnv", "+VMWARE-ROOT-MIB:VMWARE-VMINFO-MIB", "+" . $config["install_dir"] . "/mibs/vmware:" . $config["install_dir"] . "/mibs");
      $vmwVmCpus    = snmp_get($device, "VMWARE-VMINFO-MIB::vmwVmCpus."    . $oid, "-Osqnv", "+VMWARE-ROOT-MIB:VMWARE-VMINFO-MIB", "+" . $config["install_dir"] . "/mibs/vmware:" . $config["install_dir"] . "/mibs");

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

      if (mysql_result(mysql_query("SELECT COUNT(id) FROM vminfo WHERE device_id = '" . $device["device_id"] . "' AND vmwVmVMID = '" . $oid . "' AND vm_type='vmware'"), 0) == 0)
      {
        mysql_query("INSERT INTO vminfo (device_id, vm_type, vmwVmVMID, vmwVmDisplayName, vmwVmGuestOS, vmwVmMemSize, vmwVmCpus, vmwVmState) VALUES (" . $device["device_id"] . ",'vmware', " . $oid . ", '" . mres($vmwVmDisplayName) . "', '" . mres($vmwVmGuestOS) . "', " . $vmwVmMemSize . ", " . $vmwVmCpus . ", '" . mres($vmwVmState) . "')");
        echo("+");
        # FIXME eventlog
      } else {
        echo(".");
      }
      # FIXME update code!

      /*
       * Save the discovered Virtual Machine.
       */

      $vmw_vmlist[] = $oid;
    }
  }

  /*
   * Get a list of all the known Virtual Machines for this host.
   */

  $db_vm_list = mysql_query("SELECT id, vmwVmVMID FROM vminfo WHERE device_id = '" . $device["device_id"] . "' AND vm_type='vmware'");

  while ($db_vm = mysql_fetch_assoc($db_vm_list))
  {
    /*
     * Delete the Virtual Machines that are removed from the host.
     */

    if (!in_array($db_vm["vmwVmVMID"], $vmw_vmlist))
    {
      mysql_query("DELETE FROM vminfo WHERE id = '" . $db_vm["id"] . "'");
      echo("-");
      # FIXME eventlog
    }
  }

  /*
   * Finished discovering VMware information.
   */

  echo("\n");
}

?>