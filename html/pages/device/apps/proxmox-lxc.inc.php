<?php

echo '<table border="0" cellspacing="0" cellpadding="5" width="100%" class="table table-condensed"><tr class="tablehead"><th>VM ID</th><th>Server Name</th><th>Power Status</th><th>Cluster</th><th>VM PID</th><th>Current Memory</th><th>Max Memory</th><th>Memory Useage</th><th>CPU</th><th>Storage</th></tr>';
$i = '1';

foreach (dbFetchRows('SELECT `description`,`status`,`cluster`,`vmid`, `last_seen`, `vmpid`, `vmramcurr`, `vmrammax`, `vmramuse`, `vmcpu`, `vmstorage` FROM `proxmox` WHERE `device_id` = ? ORDER BY `vmid`', array($device['device_id'])) as $vm) {
    include 'includes/print-proxmox-lxc.inc.php';
    $i++;
}

echo '</table>';
