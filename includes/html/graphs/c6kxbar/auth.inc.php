<?php

require 'includes/html/graphs/device/auth.inc.php';

if ($auth && is_numeric($vars['mod']) && is_numeric($vars['chan'])) {
    $entity = dbFetchRow('SELECT * FROM entPhysical WHERE device_id = ? AND entPhysicalIndex = ?', [$device['device_id'], $vars['mod']]);

    $title .= ' :: ' . $entity['entPhysicalName'];
    $title .= ' :: Fabric ' . $vars['chan'];

    $graph_title = shorthost($device['hostname']) . '::' . $entity['entPhysicalName'] . '::Fabric' . $vars['chan'];

    $rrd_filename = Rrd::name($device['hostname'], ['c6kxbar', $vars['mod'], $vars['chan']]);
}
