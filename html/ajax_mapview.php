<?php
session_start();
//availability-map mode view
if (isset($_REQUEST['map_view'])) {
    $_SESSION['map_view'] = $_REQUEST['map_view'];
    $map_view = array('map_view' => $_SESSION['map_view']);
    header('Content-type: text/plain');
    echo json_encode($map_view);
}

//availability-map device group view
if (isset($_REQUEST['group_view'])) {
    $_SESSION['group_view'] = $_REQUEST['group_view'];
    $group_view = array('group_view' => $_SESSION['group_view']);
    header('Content-type: text/plain');
    echo json_encode($group_view);
}
