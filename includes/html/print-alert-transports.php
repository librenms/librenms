<?php

$no_refresh = true;

?>
<div class="row">
    <div class="col-sm-12">
        <span id="message"></span>
    </div>
</div>
<?php

require_once 'includes/html/modal/edit_alert_transport.inc.php';
require_once 'includes/html/modal/edit_transport_group.inc.php';

?>

<div class="table-responsive">
    <table class="table table-hover table-condensed">
    <tr>
        <th>#</th>
        <th>Transport Name</th>
        <th>Transport Type</th>
        <th colspan='2'>Devices</th>
        <th>Default</th>
        <th>Time range</th>
        <th>Details</th>
        <th style="width:126px;">Action</th>
    </tr>
    <td colspan="6">
<?php
if (Auth::user()->hasGlobalAdmin()) {
    echo "<button type='button' class='btn btn-primary btn-sm' data-toggle='modal' data-target='#edit-alert-transport'><i class='fa fa-plus'></i> Create alert transport</button>";
}

echo "</td>";

// Iterate through each alert transport
$query = "SELECT `transport_id` AS `id`, `transport_name` AS `name`, `transport_type` AS `type`, `timerange`, `invert_map`, `is_default`, `transport_config` AS `config` FROM `alert_transports`";
foreach (dbFetchRows($query) as $transport) {
    echo "<tr id=\"alert-transport-{$transport['id']}\">";
    echo "<td><i>#".((int)$transport['id'])."</i></td>";
    echo "<td>".$transport['name']."</td>";
    echo "<td>".$transport['type']."</td>";
    // Devices (and Groups)

    if ($transport['invert_map'] == 0) {
        $groups_msg = 'Only devices in this group.';
        $devices_msg = 'Only this device.';
        $except_device_or_group = null;
    }

    if ($transport['invert_map'] == 1) {
        $devices_msg = 'All devices EXCEPT this device. ';
        $groups_msg = 'All devices EXCEPT this group.';
        $except_device_or_group = '<strong><em>EXCEPT</em></strong> ';
    }

    $device_count = dbFetchCell('SELECT COUNT(*) FROM transport_device_map WHERE transport_id=?', [$transport['id']]);
    $group_count = dbFetchCell('SELECT COUNT(*) FROM transport_group_map WHERE transport_id=?', [$transport['id']]);
    $location_count = dbFetchCell('SELECT COUNT(*) FROM transport_location_map WHERE transport_id=?', [$transport['id']]);

    $popover_position = 'right';

    $locations = null;
    if ($location_count) {
        $location_query = 'SELECT locations.location, locations.id FROM transport_location_map, locations WHERE transport_location_map.transport_id=? and transport_location_map.location_id = locations.id ORDER BY location';
        $location_maps = dbFetchRows($location_query, [$transport['id']]);
        foreach ($location_maps as $location_map) {
            $locations .= "$except_device_or_group<a href=\"/devices\/location=".$location_map['id']."\" data-container='body' data-toggle='popover' data-placement='$popover_position' data-content='View Devices for Location' target=\"_blank\">".$location_map['location']."</a><br>";
        }
    }

    $groups = null;
    if ($group_count) {
        $group_query = 'SELECT device_groups.name, device_groups.id FROM transport_group_map, device_groups WHERE transport_group_map.transport_id=? and transport_group_map.group_id = device_groups.id ORDER BY name';
        $group_maps = dbFetchRows($group_query, [$transport['id']]);
        foreach ($group_maps as $group_map) {
            $groups .= "$except_device_or_group<a href=\"/device-groups/" . $group_map['id'] . "/edit\" data-container='body' data-toggle='popover' data-placement='$popover_position' data-content='" . $group_map['name'] . "' title='$groups_msg' target=\"_blank\">" . $group_map['name'] . "</a><br>";
        }
    }

    $devices = null;
    if ($device_count) {
        $device_query = 'SELECT devices.device_id,devices.hostname FROM transport_device_map, devices WHERE transport_device_map.transport_id=? and transport_device_map.device_id = devices.device_id ORDER BY hostname';
        $device_maps = dbFetchRows($device_query, [$transport['id']]);
        foreach ($device_maps as $device_map) {
            $devices .= "$except_device_or_group<a href=\"/device/device=" . $device_map['device_id'] . "/tab=edit/\" data-container='body' data-toggle='popover' data-placement='$popover_position' data-content='" . $device_map['hostname'] . "' title='$devices_msg' target=\"_blank\">" . $device_map['hostname'] . "</a><br>";
        }
    }

    echo "<td colspan='2'>";
    if ($locations) {
        echo $locations;
    }
    if ($groups) {
        echo $groups;
    }
    if ($devices) {
        echo $devices;
    }
    if (!$devices && !$groups && !$locations) {
        // All Devices
        echo "<a href=\"/devices\" data-container='body' data-toggle='popover' data-placement='$popover_position' data-content='View All Devices' target=\"_blank\">All Devices</a><br>";
    }

    echo "</td>";

    if ($transport['is_default'] == true) {
        echo "<td>Yes</td>";
    } else {
        echo "<td>No</td>";
    }
    if ($transport['timerange'] == true) {
        echo "<td>Yes</td>";
    } else {
        echo "<td>No</td>";
    }

    echo "<td class='col-sm-4'>";

    // Iterate through transport config template to display config details
    $class = 'LibreNMS\\Alert\\Transport\\'.ucfirst($transport['type']);
    if (!method_exists($class, 'configTemplate')) {
        //skip
        continue;
    }
    $tmp = call_user_func($class.'::configTemplate');
    $transport_config = json_decode($transport['config'], true);

    foreach ($tmp['config'] as $item) {
        if ($item['type'] == 'oauth') {
            continue;
        }

        $val = $transport_config[$item['name']];
        if ($item['type'] == 'password') {
            $val = '<b>&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;</b>';
        }
        // Match value to key name for select inputs
        if ($item['type'] == 'select') {
            $val = array_search($val, $item['options']);
        }

        echo "<i>".$item['title'].": ".$val."<br/></i>";
    }

    echo "</td>";
    echo "<td>";

    // Add action buttons for admin users only
    if (Auth::user()->hasGlobalAdmin()) {
        echo "<div class='btn-group btn-group-sm' role='group'>";
        echo "<button type='button' class='btn btn-primary' data-toggle='modal' data-target='#edit-alert-transport' data-transport_id='".$transport['id']."' name='edit-alert-rule' data-container='body' data-toggle='popover' data-content='Edit transport'><i class='fa fa-lg fa-pencil' aria-hidden='true'></i></button> ";
        echo "<button type='button' class='btn btn-danger' aria-label='Delete' data-toggle='modal' data-target='#delete-alert-transport' data-transport_id='".$transport['id']."' name='delete-alert-transport' data-container='body' data-toggle='popover' data-content='Delete transport'><i class='fa fa-lg fa-trash' aria-hidden='true'></i></button>";
        echo "<button type='button' class='btn btn-warning' data-transport_id='".$transport['id']."' data-transport='{$transport['type']}' name='test-transport' id='test-transport' data-toggle='popover' data-content='Test transport'><i class='fa fa-lg fa-check' aria-hidden='true'></i></button> ";
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
    echo "<tr id=\"alert-transport-group-{$group['id']}\">";
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
        echo "<button type='button' class='btn btn-primary' data-toggle='modal' data-target='#edit-transport-group' data-group_id='".$group['id']."' data-container='body' data-toggle='popover' data-content='Edit transport group'><i class='fa fa-lg fa-pencil' aria-hidden='true'></i></button> ";
        echo "<button type='button' class='btn btn-danger' aria-label='Delete' data-toggle='modal' data-target='#delete-transport-group' data-group_id='".$group['id']."' data-container='body' data-toggle='popover' data-content='Delete transport group'><i class='fa fa-lg fa-trash' aria-hidden='true'></i></button>";
        echo "</div>";
    }
    echo "</td>";
    echo "</tr>";
}
?>
    </table>
</div>

<script>
    $("button#test-transport").click(function() {
        var $this = $(this);
        var transport_id = $this.data("transport_id");
        var transport = $this.data("transport");
        $.ajax({
            type: 'POST',
            url: 'ajax_form.php',
            data: { type: "test-transport", transport_id: transport_id },
            dataType: "json",
            success: function(data){
                if (data.status === 'ok') {
                    toastr.success('Test to ' + transport + ' ok');
                } else {
                    toastr.error('Test to ' + transport + ' failed<br />' + data.message);
                }
            },
            error: function(){
                toastr.error('Test to ' + transport + ' failed - general error');
            }
        });
    });

    $("[data-toggle='popover']").popover({
        trigger: 'hover',
        placement: 'top'
    });
</script>
