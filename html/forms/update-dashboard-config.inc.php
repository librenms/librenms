<?php

$status  = 'error';
$message = 'Error updating user dashboard config';

$data = json_decode($_POST['data'],true);
$sub_type = mres($_POST['sub_type']);
$tile_id = mres($_POST['tile_id']);

if ($sub_type == 'remove' && is_numeric($tile_id)) {
    if ($tile_id == 0 || dbDelete('dashboard_items','`user_id`=? AND `dashboard_item_id`=?', array($_SESSION['user_id'],$tile_id))) {
        $status = 'ok';
        $message = '';
    }
}
elseif ($sub_type == 'add' && is_numeric($tile_id)) {
    $tile = dbFetchRow('SELECT * FROM `tiles` WHERE `tile_id`=?', array($tile_id));
    if (is_array($tile)) {
        list($x,$y) = explode(',',$tile['base_dimensions']);
        $item_id = dbInsert(array('user_id'=>$_SESSION['user_id'],'widget_id'=>$tile_id,'title'=>$tile['tile_title'],'content'=>$tile['tile'],'size_x'=>$x,'size_y'=>$y),'dashboard_items');
        if (is_numeric($item_id)) {
            $extra = array('item_id'=>$item_id,'title'=>$tile['tile_title'],'content'=>$tile['tile'],'size_x'=>$x,'size_y'=>$y);
            $status = 'ok';
            $message = '';
        }
    }
}
else {
    $status = 'ok';
    $message = '';

    foreach ($data as $line) {
        if (is_array($line)) {
            $update = array('col'=>$line['col'],'row'=>$line['row'],'size_x'=>$line['size_x'],'size_y'=>$line['size_y']);
            dbUpdate($update, 'dashboard_items', '`dashboard_item_id`=?', array($line['id']));
        }
    }
}

$response = array(
    'status'        => $status,
    'message'       => $message,
    'extra'         => $extra,
);
echo _json_encode($response);
