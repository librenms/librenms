<?php
/*
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2018 Aldemir Akpinar
 * @author     Aldemir Akpinar <aldemir.akpinar@gmail.com>
 */

$vm_query = "SELECT a.vmwVmDisplayName AS vmname, a.vmwVmState AS powerstat, a.device_id AS deviceid, b.hostname AS physicalsrv, b.sysname AS sysname, a.vmwVmGuestOS AS os, a.vmwVmMemSize AS memory, a.vmwVmCpus AS cpu FROM vminfo AS a  LEFT JOIN devices AS b ON  a.device_id = b.device_id";

if (isset($_POST['searchPhrase']) && !empty($_POST['searchPhrase'])) {
    #This is a bit ugly
    $vm_query .= " WHERE a.vmwVmDisplayName LIKE ? OR b.hostname LIKE ? OR a.vmwVmGuestOS LIKE ? OR b.sysname LIKE ?";
    $count_query = "SELECT COUNT(a.vmwVmDisplayName) FROM vminfo AS a LEFT JOIN devices AS b ON  a.device_id = b.device_id WHERE a.vmwVmDisplayName LIKE ? OR b.hostname LIKE ? OR a.vmwVmGuestOS LIKE ? OR b.sysname LIKE ?";
} else {
    $count_query = "SELECT COUNT(*) FROM vminfo ";
}

$order_by = '';
if (isset($_REQUEST['sort']) && is_array($_REQUEST['sort'])) {
    foreach ($_REQUEST['sort'] as $key => $value) {
        $order_by .= " $key $value";
    }
} else {
    $order_by = " vmname";
}

$vm_query .= " ORDER BY " . $order_by;

if (is_numeric($_POST['rowCount']) && is_numeric($_POST['current'])) {
    $rowcount = $_POST['rowCount'];
    $current = $_POST['current'];
    $vm_query .= " LIMIT ".$rowcount * ($current - 1).", ".$rowcount;
}

if (!empty($_POST['searchPhrase'])) {
    $searchphrase = '%'.mres($_POST['searchPhrase']).'%';
    $vm_arr = dbFetchRows($vm_query, array($searchphrase, $searchphrase, $searchphrase, $searchphrase));
    $rec_count = dbFetchCell($count_query, array($searchphrase, $searchphrase, $searchphrase, $searchphrase));
} else {
    $vm_arr = dbFetchRows($vm_query);
    $rec_count = dbFetchCell($count_query);
}

foreach ($vm_arr as $k => $v) {
    if (device_permitted($v['deviceid']) === false) {
        unset($vm_arr[$k]);
        $rec_count--;
    }
}


$status = array('current' => $current, 'rowCount' => $rowcount, 'rows' => $vm_arr, 'total' => $rec_count);

header('Content-Type: application/json');
echo _json_encode($status);
