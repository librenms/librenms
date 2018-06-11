<?php

use LibreNMS\Authentication\Auth;

$no_refresh = true;

?>
<div class="row">
    <div class="col-sm-12">
        <span id="message"></span>
    </div>
</div>
<?php

require_once 'includes/modal/edit_alert_transport.inc.php';
require_once 'includes/modal/edit_transport_group.inc.php';

?>

<div class="table-responsive">
    <table class="table table-hover table-condensed">
    <tr>
    <th>#</th>
    <th>Transport Name</th>
    <th>Transport Type</th>
    <th>Default</th>
    <th>Details</th>
    <th style="width:86px;">Action</th>
    </tr>
    <td colspan="6">
<?php
if (Auth::user()->hasGlobalAdmin()) {
    echo "<button type='button' class='btn btn-primary btn-sm' data-toggle='modal' data-target='#edit-alert-transport'><i class='fa fa-plus'></i> Create alert transport</button>";
}

echo "</td>";

// Iterate through each alert transport
$query = "SELECT `transport_id` AS `id`, `transport_name` AS `name`, `transport_type` AS `type`, `is_default`, `transport_config` AS `config` FROM `alert_transports`";
foreach (dbFetchRows($query) as $transport) {
    echo "<tr>";
    echo "<td><i>#".((int)$transport['id'])."</i></td>";
    echo "<td>".$transport['name']."</td>";
    echo "<td>".$transport['type']."</td>";
    if ($transport['is_default'] == true) {
        echo "<td>Yes</td>";
    } else {
        echo "<td>No</td>";
    }
    
    echo "<td class='col-sm-4'>";

    //Iterate through alert transport config details
    foreach (json_decode($transport['config']) as $key => $value) {
        echo "<i>".$key.": ".$value."<br /></i>";
    }
    echo "</td>";
    echo "<td>";

    // Add action buttons for admin users only
    if (Auth::user()->hasGlobalAdmin()) {
        echo "<div class='btn-group btn-group-sm' role='group'>";
        echo "<button type='button' class='btn btn-primary' data-toggle='modal' data-target='#edit-alert-transport' data-transport_id='".$transport['id']."' name='edit-alert-rule' data-container='body'><i class='fa fa-lg fa-pencil' aria-hidden='true'></i></button> ";
        echo "<button type='button' class='btn btn-danger' aria-label='Delete' data-toggle='modal' data-target='#delete-alert-transport' data-transport_id='".$transport['id']."' name='delete-alert-transport' data-container='body'><i class='fa fa-lg fa-trash' aria-hidden='true'></i></button>";
        echo "</div>";
    }
    echo "</td>";
    echo "</tr>\r\n";
}
?>
    </table>
</div>
<div class="table-responsive">
    <table class="table table-hover table-condensed">
    <tr>
    <th>#</th>
    <th>Transport Group</th>
    <th>Size</th>
    <th>Members</th>
    <th style="width:86px;">Action</th>
    </tr>
    <td colspan="5">
<?php
if (Auth::user()->hasGlobalAdmin()) {
    echo "<button type='button' class='btn btn-primary btn-sm' data-toggle='modal' data-target='#edit-transport-group'><i class='fa fa-plus'></i> Create transport group</button>";
}
echo "</td>";

//Iterate through alert groups
$query = "SELECT `transport_group_id` AS `id`, `transport_group_name` AS `name` FROM `alert_transport_groups`";
foreach (dbFetchRows($query) as $group) {
    echo "<tr>";
    echo "<td><i>#".((int)$group['id'])."</i></td>";
    echo "<td>".$group['name']."</td>";

    //List out the members of each group
    $query = "SELECT `transport_type`, `transport_name` FROM `transport_group_transport` AS `a` LEFT JOIN `alert_transports` AS `b` ON `a`.`transport_id`=`b`.`transport_id` WHERE `transport_group_id`=?";
    $members = dbFetchRows($query, [$group['id']]);
    echo "<td>".sizeof($members)."</td>";
    echo "<td>";
    foreach ($members as $member) {
        echo "<i>".ucfirst($member['transport_type']).": ".$member['transport_name']."<br /></i>";
    }
    echo "</td>";
    echo "<td>";
    if (Auth::user()->hasGlobalAdmin()) {
        echo "<div class='btn-group btn-group-sm' role='group'>";
        echo "<button type='button' class='btn btn-primary' data-toggle='modal' data-target='#edit-transport-group' data-group_id='".$group['id']."' data-container='body'><i class='fa fa-lg fa-pencil' aria-hidden='true'></i></button> ";
        echo "<button type='button' class='btn btn-danger' aria-label='Delete' data-toggle='modal' data-target='#delete-transport-group' data-group_id='".$group['id']."' data-container='body'><i class='fa fa-lg fa-trash' aria-hidden='true'></i></button>";
        echo "</div>";
    }
    echo "</td>";
    echo "</tr>";
}
?>
    </table>
</div>
