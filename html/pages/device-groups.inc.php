<?php
require_once 'includes/modal/new_device_group.inc.php';
require_once 'includes/modal/delete_device_group.inc.php';

$no_refresh = true;

echo '<div class="row"><div class="col-sm-12"><span id="message"></span></div></div>';
echo '<div class="table-responsive">';
echo '<table class="table table-condensed table-hover"><thead><tr>';
echo '<th>Name</th><th>Description</th><th>Pattern</th><th>Actions</th>';
echo '</tr></thead><tbody>';
foreach (GetDeviceGroups() as $group) {
    echo '<tr id="row_'.$group['id'].'">';
    echo '<td>'.$group['name'].'</td>';
    echo '<td>'.$group['desc'].'</td>';
    echo '<td>'.$group['pattern'].'</td>';
    echo '<td>';
        echo "<button type='button' class='btn btn-primary btn-sm' aria-label='Edit' data-toggle='modal' data-target='#create-group' data-group_id='".$group['id']."' name='edit-device-group'><span class='glyphicon glyphicon-pencil' aria-hidden='true'></span></button> ";
        echo "<button type='button' class='btn btn-danger btn-sm' aria-label='Delete' data-toggle='modal' data-target='#confirm-delete' data-group_id='".$group['id']."' name='delete-device-group'><span class='glyphicon glyphicon-trash' aria-hidden='true'></span></button>";
    echo '</td>';
    echo '</tr>';
}

echo '</tbody></table></div>';
echo "<button type='button' class='btn btn-primary btn-sm' aria-label='Add' data-toggle='modal' data-target='#create-group' data-group_id='' name='create-device-group'>Create new Group</button> ";
