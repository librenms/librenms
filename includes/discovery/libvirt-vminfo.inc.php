<?php

# FIXME should do the deletion etc in a common file perhaps? like for the sensors

# Try to discover Libvirt Virtual Machines.

if ($config['enable_libvirt'] == '1' && $device['os'] == "linux" )
{
  $libvirt_vmlist = array();

  echo("Libvirt VM: ");

  # FIXME should support other methods here too (tls etc), and other hypervisors, like xen, too!
  $method = 'qemu+ssh';
  
  # Fetch virtual machine list
  unset($domlist);
  exec($config['virsh'] . ' -c '.$method.'://' . $device['hostname'] . '/system list',$domlist);

  foreach ($domlist as $dom)
  {
    list($dom_id,) = explode(' ',trim($dom),2);
    
    if (is_numeric($dom_id))
    {
      # Fetch the Virtual Machine information.
      unset($vm_info_array);
      exec($config['virsh'] . ' -c '.$method.'://' . $device['hostname'] . '/system dumpxml ' . $dom_id,$vm_info_array);

      # <domain type='kvm' id='3'>
      #   <name>moo.example.com</name>
      #   <uuid>48cf6378-6fd5-4610-0611-63dd4b31cfd6</uuid>
      #   <memory>1048576</memory>
      #   <currentMemory>1048576</currentMemory>
      #   <vcpu>8</vcpu>
      #   <os>
      #     <type arch='x86_64' machine='pc-0.12'>hvm</type>
      #     <boot dev='hd'/>
      #   </os>
      #   <features>
      #     <acpi/>
      #   (...)
      
      # Convert array to string
      unset($vm_info_xml);
      foreach ($vm_info_array as $line) { $vm_info_xml .= $line; }
    
      $xml = simplexml_load_string('<?xml version="1.0"?> ' . $vm_info_xml);
      if ($debug) { print_r($xml); }

      $vmwVmDisplayName = $xml->name;
      $vmwVmGuestOS   = ''; # libvirt does not supply this
      $vmwVmMemSize   = $xml->currentMemory / 1024;
      exec($config['virsh'] . ' -c '.$method.'://' . $device['hostname'] . '/system domstate ' . $dom_id,$vm_state);
      $vmwVmState = ucfirst($vm_state[0]);
      $vmwVmCpus = $xml->vcpu;

      # Check whether the Virtual Machine is already known for this host.
      $result = mysql_query("SELECT * FROM vminfo WHERE device_id = '" . $device["device_id"] . "' AND vmwVmVMID = '" . $dom_id . "' AND vm_type='libvirt'");
      if (mysql_num_rows($result) == 0)
      {
        mysql_query("INSERT INTO vminfo (device_id, vm_type, vmwVmVMID, vmwVmDisplayName, vmwVmGuestOS, vmwVmMemSize, vmwVmCpus, vmwVmState) VALUES (" . $device["device_id"] . ", 'libvirt', 
            '" . $dom_id . "', '" . mres($vmwVmDisplayName) . "', '" . mres($vmwVmGuestOS) . "', '" . $vmwVmMemSize . "', '" . $vmwVmCpus . "', '" . mres($vmwVmState) . "')");
        echo("+");
        # FIXME eventlog
      } else {
        $row = mysql_fetch_assoc($result);
        if ($row['vmwVmState'] != $vmwVmState
         || $row['vmwVmDisplayName'] != $vmwVmDisplayName
         || $row['vmwVmCpus'] != $vmwVmCpus
         || $row['vmwVmGuestOS'] != $vmwVmGuestOS
         || $row['vmwVmMemSize'] != $vmwVmMemSize)
        {
          mysql_query("UPDATE vminfo SET vmwVmState='" . mres($vmwVmState) . "', vmwVmGuestOS='" . mres($vmwVmGuestOS) . "', vmwVmDisplayName='". mres($vmwVmDisplayName) . "', 
              vmwVmMemSize='" . mres($vmwVmMemSize) . "', vmwVmCpus='" . mres($vmwVmCpus) . "' WHERE device_id='" . $device["device_id"] . "' AND vm_type='libvirt' AND vmwVmVMID='" . $dom_id . "'");
          echo("U");
          # FIXME eventlog
        }
        else
        {
          echo(".");
        }
      }

      # Save the discovered Virtual Machine.
      $libvirt_vmlist[] = $dom_id;
    }
  }

  # Get a list of all the known Virtual Machines for this host.
  $db_vm_list = mysql_query("SELECT id, vmwVmVMID FROM vminfo WHERE device_id = '" . $device["device_id"] . "' AND vm_type='libvirt'");

  while ($db_vm = mysql_fetch_assoc($db_vm_list))
  {
    # Delete the Virtual Machines that are removed from the host.

    if (!in_array($db_vm["vmwVmVMID"], $libvirt_vmlist))
    {
      mysql_query("DELETE FROM vminfo WHERE id = '" . $db_vm["id"] . "'");
      echo("-");
      # FIXME eventlog
    }
  }

  echo("\n");
}

?>