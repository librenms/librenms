<?php
/**
 * print-alert-rules.inc.php
 *
 * LibreNMS print alert rules table
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2020 The LibreNMS Community
 * @author     Original Author <unknown>
 * @author     Joseph Tingiris <joseph.tingiris@gmail.com>
 */
if (! Auth::user()->hasGlobalAdmin()) {
    exit('ERROR: You need to be admin');
}

use LibreNMS\Alerting\QueryBuilderParser;
use LibreNMS\Enum\AlertState;

$no_refresh = true;

?>
<div class="row">
    <div class="col-sm-12">
        <span id="message"></span>
    </div>
</div>
<?php
if (isset($_POST['create-default'])) {
    $default_rules = array_filter(get_rules_from_json(), function ($rule) {
        return isset($rule['default']) && $rule['default'];
    });

    $default_extra = [
        'mute' => false,
        'count' => -1,
        'delay' => 300,
        'invert' => false,
        'interval' => 300,
    ];

    foreach ($default_rules as $add_rule) {
        $extra = $default_extra;
        if (isset($add_rule['extra'])) {
            $extra = array_replace($extra, json_decode($add_rule['extra'], true));
        }

        $qb = QueryBuilderParser::fromOld($add_rule['rule']);
        $insert = [
            'rule' => '',
            'builder' => json_encode($qb),
            'query' => $qb->toSql(),
            'severity' => 'critical',
            'extra' => json_encode($extra),
            'disabled' => 0,
            'name' => $add_rule['name'],
        ];

        dbInsert($insert, 'alert_rules');
    }
    unset($qb);
}

require_once 'includes/html/modal/new_alert_rule.inc.php';
require_once 'includes/html/modal/delete_alert_rule.inc.php'; // Also dies if !Auth::user()->hasGlobalAdmin()
require_once 'includes/html/modal/alert_rule_collection.inc.php'; // Also dies if !Auth::user()->hasGlobalAdmin()
require_once 'includes/html/modal/alert_rule_list.inc.php'; // Also dies if !Auth::user()->hasGlobalAdmin()

require_once 'includes/html/modal/edit_transport_group.inc.php';
require_once 'includes/html/modal/edit_alert_transport.inc.php';

echo '<form method="post" action="" id="result_form">';
echo csrf_field();
if (isset($_POST['results_amount']) && $_POST['results_amount'] > 0) {
    $results = $_POST['results'];
} else {
    $results = 50;
}

echo '<div class="table-responsive">';
echo '<div class="col pull-left">';
echo '<button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#create-alert" data-device_id="' . $device['device_id'] . '">Create new alert rule</button>';
echo '<i> - OR - </i>';
echo '<button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#search_rule_modal" data-device_id="' . $device['device_id'] . '">Create rule from collection</button>';
echo '</div>';

echo '<div class="col pull-right">';
echo '<select data-toggle="popover" data-placement="left" data-content="results per page" name="results" id="results" class="form-control input-sm" onChange="updateResults(this);">';
$result_options = [
    '10',
    '50',
    '100',
    '250',
    '500',
    '1000',
    '5000',
];
foreach ($result_options as $option) {
    echo "<option value='$option'";
    if ($results == $option) {
        echo ' selected';
    }
    echo ">$option</option>";
}
echo '</select>';
echo '</div>';

echo '</div>';

echo '<br>';

$param = [];
if (isset($device['device_id']) && $device['device_id'] > 0) {
    //device selected

    $global_rules = 'SELECT ar1.* FROM alert_rules AS ar1 WHERE ar1.id NOT IN (SELECT agm1.rule_id FROM alert_group_map AS agm1 UNION DISTINCT SELECT adm1.rule_id FROM alert_device_map AS adm1)';

    $device_rules = 'SELECT ar2.* FROM alert_rules AS ar2 WHERE ar2.id IN (SELECT adm2.rule_id FROM alert_device_map AS adm2 WHERE adm2.device_id=?)';
    $param[] = $device['device_id'];

    $device_group_rules = 'SELECT ar3.* FROM alert_rules AS ar3 WHERE ar3.id IN (SELECT agm3.rule_id FROM alert_group_map AS agm3 LEFT JOIN device_group_device AS dgd3 ON agm3.group_id=dgd3.device_group_id WHERE dgd3.device_id=?)';
    $param[] = $device['device_id'];

    $device_location_rules = 'SELECT ar4.* FROM alert_rules AS ar4 WHERE ar4.id IN (SELECT alm4.rule_id FROM alert_location_map AS alm4 LEFT JOIN devices AS d4 ON alm4.location_id=d4.location_id WHERE d4.device_id=?)';
    $param[] = $device['device_id'];

    $full_query = '(' . $global_rules . ') UNION DISTINCT (' . $device_rules . ') UNION DISTINCT (' . $device_group_rules . ') UNION DISTINCT (' . $device_location_rules . ')';
} else {
    // no device selected
    $full_query = 'SELECT alert_rules.* FROM alert_rules';
}

$full_query .= ' ORDER BY name ASC';

$rule_list = dbFetchRows($full_query, $param);
$count = count($rule_list);

if (isset($_POST['page_number']) && $_POST['page_number'] > 0 && $_POST['page_number'] <= $count) {
    $page_number = $_POST['page_number'];
} else {
    $page_number = 1;
}

$start = (($page_number - 1) * $results);

?>
<div class="table-responsive">
<table id="alert-rules-table" class="table table-condensed table-hover table-striped">
<thead>
    <tr>
        <th data-column-id="Type">Type<th>
        <th data-column-id="Name">Name</th>
        <th data-column-id="Devices">Devices<th>
        <th data-column-id="Transports">Transports<th>
        <th data-column-id="Extra">Extra</th>
        <th data-column-id="Rule">Rule</th>
        <th data-column-id="Severity">Severity</th>
        <th data-column-id="Status">Status</th>
        <th data-column-id="Enabled">Enabled</th>
        <th data-column-id="Action" style="width:86px;">Action</th>
    </tr>
</thead>
<tbody>
<?php

$index = 0;
foreach ($rule_list as $rule) {
    $index++;

    if ($index < $start) {
        continue;
    }
    if ($index > $start + $results) {
        break;
    }

    $sub = dbFetchRows('SELECT * FROM alerts WHERE rule_id = ? ORDER BY `state` DESC, `id` DESC LIMIT 1', [$rule['id']]);
    $severity = dbFetchCell('SELECT severity FROM alert_rules where id = ?', [$rule['id']]);
    $ico = 'check';
    $col = 'success';
    $extra = '';
    $status_msg = '';
    if (sizeof($sub) == 1) {
        $sub = $sub[0];
        if ((int) $sub['state'] === AlertState::CLEAR) {
            $ico = 'check';
            $col = 'success';
            $status_msg = 'All devices matching ' . $rule['name'] . '  are OK';
        }
        if ((int) $sub['state'] === AlertState::ACTIVE || (int) $sub['state'] === AlertState::ACKNOWLEDGED) {
            $alert_style = alert_layout($severity);
            $ico = $alert_style['icon'];
            $col = $alert_style['icon_color'];
            $extra = $alert_style['background_color'];
            $status_msg = 'Some devices matching ' . $rule['name'] . ' are currently alerting';
        }
    }

    $alert_checked = '';
    $orig_ico = $ico;
    $orig_col = $col;
    $orig_class = $extra;
    if ($rule['disabled']) {
        $ico = 'pause';
        $col = '';
        $extra = 'active';
        $status_msg = $rule['name'] . ' is OFF';
    } else {
        $alert_checked = 'checked';
    }

    $rule_extra = json_decode($rule['extra'], true);

    $device_count = dbFetchCell('SELECT COUNT(*) FROM alert_device_map WHERE rule_id=?', [$rule['id']]);
    $group_count = dbFetchCell('SELECT COUNT(*) FROM alert_group_map WHERE rule_id=?', [$rule['id']]);
    $location_count = dbFetchCell('SELECT COUNT(*) FROM alert_location_map WHERE rule_id=?', [$rule['id']]);

    $popover_msg_parts = [];

    $icon_indicator = 'fa fa-globe fa-fw text-success';

    if ($device_count) {
        $popover_msg_parts[] = 'Device';
        $icon_indicator = 'fa fa-server fa-fw text-primary';
    }
    if ($group_count) {
        $popover_msg_parts[] = 'Group';
        $icon_indicator = 'fa fa-th fa-fw text-primary';
    }
    if ($location_count) {
        $popover_msg_parts[] = 'Location';
        $icon_indicator = 'fa fa-th fa-fw text-primary';
    }

    if (count($popover_msg_parts)) {
        $popover_msg = implode(', ', $popover_msg_parts);
    } else {
        $popover_msg = 'Global';
    }
    $popover_msg .= ' alert Rule #' . $rule['id'];

    echo "<tr class='" . $extra . "' id='rule_id_" . $rule['id'] . "'>";

    // Type

    echo "<td colspan=\"2\"><div data-toggle='popover' data-placement='top' data-content=\"$popover_msg\" class=\"$icon_indicator\"></div></td>";

    // Name

    echo '<td>' . $rule['name'] . '</td>';

    // Devices (and Groups)

    if ($rule['invert_map'] == 0) {
        $groups_msg = 'Only devices in this group.';
        $devices_msg = 'Only this device.';
        $except_device_or_group = null;
    }

    if ($rule['invert_map'] == 1) {
        $devices_msg = 'All devices EXCEPT this device. ';
        $groups_msg = 'All devices EXCEPT this group.';
        $except_device_or_group = '<strong><em>EXCEPT</em></strong> ';
    }

    $popover_position = 'right';

    $locations = null;
    if ($location_count) {
        $location_query = 'SELECT locations.location, locations.id FROM alert_location_map, locations WHERE alert_location_map.rule_id=? and alert_location_map.location_id = locations.id ORDER BY location';
        $location_maps = dbFetchRows($location_query, [$rule['id']]);
        foreach ($location_maps as $location_map) {
            $locations .= $except_device_or_group . '<a href="' . url('devices/location=' . $location_map['id']) . '" data-container="body" data-toggle="popover" data-placement="' . $popover_position . '" data-content="View Devices for Location" target="_blank">' . $location_map['location'] . '</a><br>';
        }
    }

    $groups = null;
    if ($group_count) {
        $group_query = 'SELECT device_groups.name, device_groups.id FROM alert_group_map, device_groups WHERE alert_group_map.rule_id=? and alert_group_map.group_id = device_groups.id ORDER BY name';
        $group_maps = dbFetchRows($group_query, [$rule['id']]);
        foreach ($group_maps as $group_map) {
            $groups .= $except_device_or_group . '<a href="' . url('device-groups/' . $group_map['id'] . '/edit') . '" data-container="body" data-toggle="popover" data-placement="' . $popover_position . ' data-content="' . $group_map['name'] . '" title="' . $groups_msg . '" target="_blank">' . $group_map['name'] . '</a><br>';
        }
    }

    $devices = null;
    if ($device_count) {
        $device_query = 'SELECT devices.device_id,devices.hostname FROM alert_device_map, devices WHERE alert_device_map.rule_id=? and alert_device_map.device_id = devices.device_id ORDER BY hostname';
        $device_maps = dbFetchRows($device_query, [$rule['id']]);
        foreach ($device_maps as $device_map) {
            $devices .= $except_device_or_group . '<a href="' . url('device/device=' . $device_map['device_id'] . '/tab=edit/') . '" data-container="body" data-toggle="popover" data-placement="' . $popover_position . '" data-content="' . $device_map['hostname'] . '" title="' . $devices_msg . '" target="_blank">' . $device_map['hostname'] . '</a><br>';
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
    if (! $devices && ! $groups && ! $locations) {
        // All Devices
        echo '<a href="' . url('devices') . '" data-container="body" data-toggle="popover" data-placement=" . $popover_position . " data-content="View All Devices" target="_blank">All Devices</a><br>';
    }

    echo '</td>';

    // Transports
    $transport_count = dbFetchCell('SELECT COUNT(*) FROM alert_transport_map WHERE rule_id=?', [$rule['id']]);

    $transports_popover = 'right';

    $transports = null;
    if ($transport_count) {
        $transport_maps = dbFetchRows('SELECT transport_or_group_id,target_type FROM alert_transport_map WHERE alert_transport_map.rule_id=? ORDER BY target_type', [$rule['id']]);
        foreach ($transport_maps as $transport_map) {
            $transport_name = null;
            if ($transport_map['target_type'] == 'group') {
                $transport_name = dbFetchCell('SELECT transport_group_name FROM alert_transport_groups WHERE transport_group_id=?', [$transport_map['transport_or_group_id']]);
                $transport_edit = "<a href='' data-toggle='modal' data-target='#edit-transport-group' data-group_id='" . $transport_map['transport_or_group_id'] . "' data-container='body' data-toggle='popover' data-placement='$transports_popover' data-content='Edit transport group  $transport_name'>" . $transport_name . '</a>';
            }
            if ($transport_map['target_type'] == 'single') {
                $transport_name = dbFetchCell('SELECT transport_name FROM alert_transports WHERE transport_id=?', [$transport_map['transport_or_group_id']]);
                $transport_edit = "<a href='' data-toggle='modal' data-target='#edit-alert-transport' data-transport_id='" . $transport_map['transport_or_group_id'] . "' data-container='body' data-toggle='popover' data-placement='$transports_popover' data-content='Edit transport $transport_name'>" . $transport_name . '</a>';
            }
            $transports .= $transport_edit . '<br>';
        }
    }

    if (! $transport_count || ! $transports) {
        $default_transports = dbFetchRows('SELECT transport_id, transport_name FROM alert_transports WHERE is_default=1 ORDER BY transport_name', []);
        foreach ($default_transports as $default_transport) {
            $transport_edit = "<a href='' data-toggle='modal' data-target='#edit-alert-transport' data-transport_id='" . $default_transport['transport_id'] . "' data-container='body' data-toggle='popover' data-placement='$transports_popover' data-content='Edit default transport " . $default_transport['transport_name'] . "'>" . $default_transport['transport_name'] . '</a>';
            $transports .= $transport_edit . '<br>';
        }
    }

    if (! $transports) {
        $transports = 'none';
    }

    echo "<td colspan='2'>$transports</td>";

    // Extra

    echo '<td><small>Max: ' . $rule_extra['count'] . '<br />Delay: ' . $rule_extra['delay'] . '<br />Interval: ' . $rule_extra['interval'] . '</small></td>';

    // Rule

    echo "<td class='col-sm-4'>";
    if ($rule_extra['invert'] === true) {
        echo '<strong><em>Inverted</em></strong> ';
    }

    if (empty($rule['builder'])) {
        $rule_display = $rule['rule'];
    } elseif ($rule_extra['options']['override_query'] === 'on') {
        $rule_display = 'Custom SQL Query';
    } else {
        $rule_display = QueryBuilderParser::fromJson($rule['builder'])->toSql(false);
    }
    echo '<i>' . htmlentities($rule_display) . '</i></td>';

    // Severity
    echo '<td>' . ($rule['severity'] == 'ok' ? strtoupper($rule['severity']) : ucwords($rule['severity'])) . '</td>';

    // Status

    $status_popover = 'top';

    echo "<td><span data-toggle='popover' data-placement='$status_popover' data-content='$status_msg' id='alert-rule-" . $rule['id'] . "' class='fa fa-fw fa-2x fa-" . $ico . ' text-' . $col . "'></span> ";
    if ($rule_extra['mute'] === true) {
        echo "<div data-toggle='popover' data-content='Alerts for " . $rule['name'] . " are muted' class='fa fa-fw fa-2x fa-volume-off text-primary' aria-hidden='true'></div>";
    }
    if ($sub['state'] == AlertState::ACKNOWLEDGED) {
        echo "<div data-toggle='popover' data-content='Some Alerts for " . $rule['name'] . " are acknowledged' class='fa fa-fw fa-2x fa-sticky-note text-info' aria-hidden='true'></div>";
    }
    echo '</td>';
    // Enabled

    $enabled_popover = 'top';

    echo '<td>';
    if ($rule['disabled']) {
        $enabled_msg = $rule['name'] . ' is OFF';
    }
    if (! $rule['disabled']) {
        $enabled_msg = $rule['name'] . ' is ON';
    }

    echo "<div id='on-off-checkbox-" . $rule['id'] . "' data-toggle='popover' data-placement='$enabled_popover' data-content='" . $enabled_msg . "' class='btn-group btn-group-sm' role='group'>";
    echo "<input id='" . $rule['id'] . "' type='checkbox' name='alert-rule' data-orig_class='" . $orig_class . "' data-orig_colour='" . $orig_col . "' data-orig_state='" . $orig_ico . "' data-alert_id='" . $rule['id'] . "' data-alert_name='" . $rule['name'] . "' data-alert_status='" . $status_msg . "' " . $alert_checked . " data-size='small' data-toggle='modal'>";
    echo '</div>';
    echo '</td>';

    // Action

    $action_popover = 'left';

    echo '<td>';
    echo "<div class='btn-group btn-group-sm' role='group'>";
    echo "<button type='button' class='btn btn-primary' data-toggle='modal' data-placement='$action_popover' data-target='#create-alert' data-rule_id='" . $rule['id'] . "' name='edit-alert-rule' data-content='Edit alert rule " . $rule['name'] . "' data-container='body'><i class='fa fa-lg fa-pencil' aria-hidden='true'></i></button> ";
    echo "<button type='button' class='btn btn-danger' aria-label='Delete' data-placement='$action_popover' data-toggle='modal' data-target='#confirm-delete' data-alert_id='" . $rule['id'] . "' data-alert_name='" . $rule['name'] . "' name='delete-alert-rule' data-content='Delete alert rule " . $rule['name'] . "' data-container='body'><i class='fa fa-lg fa-trash' aria-hidden='true'></i></button>";
    echo '</td>';

    echo "</tr>\r\n";
}//end foreach

?>
</tbody>
</table>
</div>
<?php
// Pagination

if (($count % $results) > 0) {
    echo '<div class="table-responsive">';
    echo '<div class="col pull-left">';
    echo generate_pagination($count, $results, $page_number);
    echo '</div>';
    echo '<div class="col pull-right">';
    $showing_start = ($page_number * $results) - $results + 1;
    $showing_end = $page_number * $results;
    if ($showing_end > $count) {
        $showing_end = $count;
    }
    echo "<p class=\"pagination\">Showing $showing_start to $showing_end of $count alert rules</p>";
    echo '</div>';
    echo '</div>';
}

echo '<input type="hidden" name="page_number" id="page_number" value="' . $page_number . '">
    <input type="hidden" name="results_amount" id="results_amount" value="' . $results . '">
    </form>';

if ($count < 1) {
    echo '<div class="row">
        <div class="col-sm-12">
        <form role="form" method="post">
        ' . csrf_field() . '
        <p class="text-center">
        <button type="submit" class="btn btn-success btn-lg" id="create-default" name="create-default"><i class="fa fa-plus"></i> Click here to create the default alert rules!</button>
        </p>
        </form>
        </div>
        </div>';
}
?>
<script>
$("[data-toggle='modal'], [data-toggle='popover']").popover({
    trigger: 'hover'
});
$('#ack-alert').on("click", function(e) {
    event.preventDefault();
    var alert_id = $(this).data("alert_id");
    $.ajax({
        type: "POST",
            url: "ajax_form.php",
            data: { type: "ack-alert", alert_id: alert_id },
            success: function(msg){
                $("#message").html('<div class="alert alert-info">'+msg+'</div>');
                if(msg.indexOf("ERROR:") <= -1) {
                    setTimeout(function() {
                        location.reload(1);
                    }, 1000);
                }
            },
                error: function(){
                    $("#message").html('<div class="alert alert-info">An error occurred acking this alert.</div>');
                }
    });
});

$("[name='alert-rule']").bootstrapSwitch('offColor','danger');
$('input[name="alert-rule"]').on('switchChange.bootstrapSwitch',  function(event, state) {
    event.preventDefault();
    var $this = $(this);
    var alert_id = $(this).data("alert_id");
    var alert_name = $(this).data("alert_name");
    var alert_status = $(this).data("alert_status");
    var orig_state = $(this).data("orig_state");
    var orig_colour = $(this).data("orig_colour");
    var orig_class = $(this).data("orig_class");
    $.ajax({
        type: 'POST',
            url: 'ajax_form.php',
            data: { type: "update-alert-rule", alert_id: alert_id, state: state },
            dataType: "html",
            success: function(msg) {
                if(msg.indexOf("ERROR:") <= -1) {
                    if(state) {
                        $('#alert-rule-'+alert_id).removeClass('fa-pause');
                        $('#alert-rule-'+alert_id).addClass('fa-'+orig_state);
                        $('#alert-rule-'+alert_id).removeClass('text-default');
                        $('#alert-rule-'+alert_id).addClass('text-'+orig_colour);
                        $('#alert-rule-'+alert_id).attr('data-content', alert_status);
                        $('#on-off-checkbox-'+alert_id).attr('data-content', alert_name+' is ON');
                        $('#rule_id_'+alert_id).removeClass('active');
                        $('#rule_id_'+alert_id).addClass(orig_class);
                    } else {
                        $('#alert-rule-'+alert_id).removeClass('fa-'+orig_state);
                        $('#alert-rule-'+alert_id).addClass('fa-pause');
                        $('#alert-rule-'+alert_id).removeClass('text-'+orig_colour);
                        $('#alert-rule-'+alert_id).addClass('text-default');
                        $('#alert-rule-'+alert_id).attr('data-content', alert_name+' is OFF');
                        $('#on-off-checkbox-'+alert_id).attr('data-content', alert_name+' is OFF');
                        $('#rule_id_'+alert_id).removeClass('warning');
                        $('#rule_id_'+alert_id).addClass('active');
                    }
                } else {
                    $("#message").html('<div class="alert alert-info">'+msg+'</div>');
                    $('#'+alert_id).bootstrapSwitch('toggleState',true );
                }
            },
                error: function() {
                    $("#message").html('<div class="alert alert-info">This alert could not be updated.</div>');
                    $('#'+alert_id).bootstrapSwitch('toggleState',true );
                }
    });
});

function updateResults(results) {
    $('#results_amount').val(results.value);
    $('#page_number').val(1);
    $('#result_form').trigger( "submit" );
}

function changePage(page,e) {
    e.preventDefault();
    $('#page_number').val(page);
    $('#result_form').trigger( "submit" );
}
</script>
