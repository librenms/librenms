<?php

require 'includes/html/graphs/device/auth.inc.php';

if ($auth && is_numeric($_GET['mod']) && is_numeric($_GET['chan'])) {
    $entity = dbFetchRow('SELECT * FROM entPhysical WHERE device_id = ? AND entPhysicalIndex = ?', array($device['device_id'], $_GET['mod']));

    $title .= ' :: '.$entity['entPhysicalName'];
    $title .= ' :: Fabric '.$_GET['chan'];

    $graph_title = shorthost($device['hostname']).'::'.$entity['entPhysicalName'].'::Fabric'.$_GET['chan'];

    $rrd_filename = rrd_name($device['hostname'], array('c6kxbar', $_GET['mod'], $_GET['chan']));
}
