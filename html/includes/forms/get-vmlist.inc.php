<?php
$vm_query = "SELECT a.vmwVmDisplayName AS vmname, a.vmwVmState AS powerstat, a.device_id AS deviceid, b.hostname AS physicalsrv, b.sysname AS sysname, a.vmwVmGuestOS AS os, a.vmwVmMemSize AS memory, a.vmwVmCpus AS cpu FROM vminfo AS a  LEFT JOIN devices AS b ON  a.device_id = b.device_id";

if (isset($_POST['searchPhrase']) && !empty($_POST['searchPhrase'])) {
    #This is a bit ugly
    $vm_query .= " WHERE vmname LIKE ? OR physicalsrv LIKE ? OR os LIKE ? OR sysname LIKE ?";
    $count_query = "SELECT COUNT(a.vmwVmDisplayName AS vmname, b.hostname AS physicalsrv, b.sysname AS sysname, a.vmwVmGuestOS AS os, a.vmwVmMemSize AS memory, a.vmwVmCpus AS cpu) FROM vminfo AS a  LEFT JOIN devices AS b ON  a.device_id = b.device_id WHERE vmname LIKE ? OR physicalsrv LIKE ? OR os LIKE ?  OR sysname LIKE ?";
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
} else {
    $vm_arr = dbFetchRows($vm_query);
}

$rec_count = dbFetchCell($count_query);

$status = array('current' => $current, 'rowCount' => $rowcount, 'rows' => $vm_arr, 'total' => $rec_count);

header('Content-Type: application/json');
echo _json_encode($status);
