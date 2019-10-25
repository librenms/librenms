<?php

$init_modules = ['web'];
require __DIR__ . '/../includes/init.php';

//availability-map mode view
if (isset($_REQUEST['map_view'])) {
    Session::put('map_view', $_REQUEST['map_view']);
    $map_view = ['map_view' => Session::get('map_view')];
    header('Content-type: text/plain');
    echo json_encode($map_view);
}

//availability-map device group view
if (isset($_REQUEST['group_view'])) {
    Session::put('group_view', $_REQUEST['group_view']);
    $group_view = ['group_view' => Session::get('group_view')];
    header('Content-type: text/plain');
    echo json_encode($group_view);
}
