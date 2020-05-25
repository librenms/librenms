<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2017 Aldemir Akpinar <https://github.com/aldemira>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 * @package    LibreNMS
 * @subpackage webui
 * @link       http://librenms.org
 * @copyright  2018 Aldemir Akpinar
 * @author     Aldemir Akpinar <aldemir.akpinar@gmail.com>
 */

$vm_query = "SELECT v.vmwVmDisplayName AS vmname, v.vmwVmState AS powerstat, v.device_id AS deviceid, d.hostname AS physicalsrv, d.sysname AS sysname, v.vmwVmGuestOS AS os, v.vmwVmMemSize AS memory, v.vmwVmCpus AS cpu FROM vminfo AS v LEFT JOIN devices AS d ON v.device_id = d.device_id";

$param = [];
if (!Auth::user()->hasGlobalRead()) {
    $vm_query .= ' LEFT JOIN devices_perms AS DP ON d.device_id = DP.device_id';
    $uidwhere = ' AND DP.user_id = ?';
    $uid = [Auth::id()];
} else {
    $uidwhere = '';
    $uid = [];
}

if (isset($vars['searchPhrase']) && !empty($vars['searchPhrase'])) {
    $vm_query .= " WHERE v.vmwVmDisplayName LIKE ? OR d.hostname LIKE ? OR v.vmwVmGuestOS LIKE ? OR d.sysname LIKE ?" . $uidwhere;
    $count_query = "SELECT COUNT(v.vmwVmDisplayName) FROM vminfo AS v LEFT JOIN devices AS d ON  v.device_id = d.device_id WHERE v.vmwVmDisplayName LIKE ? OR d.hostname LIKE ? OR v.vmwVmGuestOS LIKE ? OR d.sysname LIKE ?" . $uidwhere;
    $searchphrase = '%' . $vars['searchPhrase'] . '%';
    array_push($param, $searchphrase, $searchphrase, $searchphrase, $searchphrase, $uid);
} else {
    $count_query = "SELECT COUNT(*) FROM vminfo ";
}

$order_by = '';
if (isset($vars['sort']) && is_array($vars['sort'])) {
    foreach ($vars['sort'] as $key => $value) {
        $order_by .= " $key $value";
    }
} else {
    $order_by = " vmname";
}

$vm_query .= " ORDER BY " . $order_by;

if (is_numeric($vars['rowCount']) && is_numeric($vars['current'])) {
    $rowcount = $vars['rowCount'];
    $current = $vars['current'];
    $vm_query .= " LIMIT ".$rowcount * ($current - 1).", ".$rowcount;
}


if (isset($vars['searchPhrase']) && !empty($vars['searchPhrase'])) {
    $vm_arr = dbFetchRows($vm_query, $param);
    $rec_count = dbFetchCell($count_query, $param);
} else {
    $vm_arr = dbFetchRows($vm_query);
    $rec_count = dbFetchCell($count_query);
}

$status = ['current' => $current, 'rowCount' => $rowcount, 'rows' => $vm_arr, 'total' => $rec_count];

header('Content-Type: application/json');
echo _json_encode($status);
