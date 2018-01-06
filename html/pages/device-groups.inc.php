<?php
require_once 'includes/modal/new_device_group.inc.php';
require_once 'includes/modal/delete_device_group.inc.php';

echo '<div class="container-fluid">';
echo '<div class="row">';
echo '<div class="panel panel-default">';
echo '<div class="panel-heading">';
echo '<span style="font-weight: bold;">Device groups</span>';
echo '<div class="pull-right">';
echo '<span style="font-weight:bold;">Actions &#187;</span>';
echo '&nbsp;<a href="" data-toggle="modal" data-target="#create-group" data-group_id="" name="create-device-group">New group</a>';
echo '</div>';
echo '</div>';
echo '<div>';

echo '<div style="margin:10px 10px 0px 10px;" id="message"></div>';

$no_refresh = true;
$group_count_check = array_filter(GetDeviceGroups());
if (!empty($group_count_check)) {
    echo '<table class="table table-hover table-condensed table-striped">';
    echo '<thead>';
    echo '<th>Name</th>';
    echo '<th>Description</th>';
    echo '<th>Pattern</th>';
    echo '<th>Actions</th>';
    echo '</thead>';
    echo '<tbody>';

    foreach (GetDeviceGroups() as $group) {
        echo '<tr id="row_' . $group['id'] . '">';
        echo '<td>' . $group['name'] . '</td>';
        echo '<td>' . $group['desc'] . '</td>';
        echo '<td>' . formatDeviceGroupPattern($group['pattern'], json_decode($group['params'])) . '</td>';
        echo '<td>';
        echo "<button type='button' class='btn btn-primary btn-sm' aria-label='Edit' data-toggle='modal' data-target='#create-group' data-group_id='" . $group['id'] . "' name='edit-device-group'";

        if (!is_null($group['params'])) {
            echo " disabled title='LibreNMS V2 device groups cannot be edited in LibreNMS V1'";
        }

        echo "><i class='fa fa-pencil' aria-hidden='true'></i></button> ";
        echo "<button type='button' class='btn btn-danger btn-sm' aria-label='Delete' data-toggle='modal' data-target='#confirm-delete' data-group_id='" . $group['id'] . "' name='delete-device-group'><i class='fa fa-trash' aria-hidden='true'></i></button>";
        echo '</td>';
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
} else {
    echo '<div style="text-align:center;margin:0px 0px 10px 0px;">Looks like no groups have been created. Click on <b>New group</b> to create one.</div>';
}

echo '</div>';
