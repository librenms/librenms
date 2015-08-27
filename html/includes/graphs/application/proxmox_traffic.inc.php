<?php
require 'includes/graphs/common.inc.php';

$mysql_rrd = $config['rrd_dir'].'/proxmox/'.$vars['cluster'].'/'.$vars['vmid'].'_netif_'.$vars['port'].'.rrd';

if (is_file($mysql_rrd)) {
    $rrd_filename = $mysql_rrd;
}

$ds_in  = 'INOCTETS';
$ds_out = 'OUTOCTETS';

require 'includes/graphs/generic_data.inc.php';
