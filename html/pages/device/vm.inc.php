<?php

echo('<table border="0" cellspacing="0" cellpadding="5" width="100%" class="sortable"><tr class="tablehead"><th>Server Name</th><th>Power Status</th><th>Operating System</th><th>Memory</th><th>CPU</th></tr>');

$i = "1";
$vm_query = mysql_query("SELECT id, vmwVmVMID, vmwVmDisplayName, vmwVmGuestOS, vmwVmMemSize, vmwVmCpus, vmwVmState FROM vmware_vminfo WHERE device_id = '".mres($_GET['id'])."' ORDER BY vmwVmDisplayName");

while ($vm = mysql_fetch_array($vm_query))
{
  include("includes/print-vm.inc.php");
  $i++;
}

echo("</table>");

?>
