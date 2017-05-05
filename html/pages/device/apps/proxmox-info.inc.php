<?php

echo '<table border="0" cellspacing="0" cellpadding="5" width="100%" class="table table-condensed"><tr class="tablehead"><th>VM ID</th><th>Server Name</th><th>Power Status</th><th>Cluster</th><th>VM Type</th><th>CPUs</th><th>VM PID</th><th>Current Memory</th><th>Max Memory</th><th>Memory Usage</th><th>Current Disk</th><th>Max Disk</th><th>Disk Usage</th></tr>';
$i = '1';

foreach (dbFetchRows('SELECT `description`,`vmstatus`,`cluster`,`vmid`, `last_seen`, `vmpid`, `vmmem`, `vmmaxmem`, `vmmemuse`, `vmcpus`, `vmdisk`, `vmmaxdisk`, `vmdiskuse`, `vmuptime`, `vmtype` FROM `proxmox` WHERE `device_id` = ? ORDER BY `vmid`', array($device['device_id'])) as $vm) {
    include 'includes/print-proxmox-info.inc.php';
    $i++;
}

echo '</table>';
