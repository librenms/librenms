<?php
/*
 * LibreNMS
 *
 *   This file is part of LibreNMS.
 *
 * @package    librenms
 * @subpackage billing
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 */

ini_set('allow_url_fopen', 0);

$init_modules = ['web', 'auth'];
require realpath(__DIR__ . '/..') . '/includes/init.php';

$urlargs = [
    'type'          => 'bill_historicbits',
    'id'            => $_GET['bill_id'],
    'width'         => $_GET['x'],
    'height'        => $_GET['y'],
];
if (isset($_GET['bill_hist_id'])) {
    $urlargs['bill_hist_id'] = $_GET['bill_hist_id'];
} else {
    $urlargs['from'] = $_GET['from'];
    $urlargs['to'] = $_GET['to'];
}
if (isset($_GET['count'])) {
    $urlargs['reducefactor'] = $_GET['count'];
}
if (isset($_GET['95th'])) {
    $urlargs['95th'] = $_GET['95th'];
}
if (isset($_GET['ave'])) {
    $urlargs['ave'] = $_GET['ave'];
}

$url = Config::get('base_url') . 'graph.php?';
$i = 0;
foreach ($urlargs as $name => $value) {
    if ($i++ > 0) {
        $url .= '&';
    }
    $url .= "$name=$value";
}

header("Location: $url", false, 301);
exit;
