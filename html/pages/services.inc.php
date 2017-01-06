<?php

$pagetitle[] = 'Services';

print_optionbar_start();

require_once 'includes/modal/new_service.inc.php';
require_once 'includes/modal/delete_service.inc.php';

echo "<span style='font-weight: bold;'>Services</span> &#187; ";

$menu_options = array(
    'basic'   => 'Basic',
);
if (!$vars['view']) {
    $vars['view'] = 'basic';
}

$status_options = array(
    'all'       => 'All',
    'ok'        => 'Ok',
    'warning'   => 'Warning',
    'critical'  => 'Critical',
);
if (!$vars['state']) {
    $vars['state'] = 'all';
}

// The menu option - on the left
$sep = '';
foreach ($menu_options as $option => $text) {
    if (empty($vars['view'])) {
        $vars['view'] = $option;
    }

    echo $sep;
    if ($vars['view'] == $option) {
        echo "<span class='pagemenu-selected'>";
    }

    echo generate_link($text, $vars, array('view' => $option));
    if ($vars['view'] == $option) {
        echo '</span>';
    }

    $sep = ' | ';
}
unset($sep);

// The status option - on the right
echo '<div class="pull-right">';
$sep = '';
foreach ($status_options as $option => $text) {
    if (empty($vars['state'])) {
        $vars['state'] = $option;
    }

    echo $sep;
    if ($vars['state'] == $option) {
        echo "<span class='pagemenu-selected'>";
    }

    echo generate_link($text, $vars, array('state' => $option));
    if ($vars['state'] == $option) {
        echo '</span>';
    }

    $sep = ' | ';
}
unset($sep);
echo '</div>';
print_optionbar_end();

$sql_param = array();
if (isset($vars['state'])) {
    if ($vars['state'] == 'ok') {
        $state = '0';
    } elseif ($vars['state'] == 'critical') {
        $state = '2';
    } elseif ($vars['state'] == 'warning') {
        $state = '1';
    }
}
if (isset($state)) {
    $where      .= " AND service_status= ? AND service_disabled='0' AND `service_ignore`='0'";
    $sql_param[] = $state;
}

?>
<div class="row col-sm-12"><span id="message"></span></div>
<table class="table table-hover table-condensed table-striped">
    <tr>
        <th>Device</th>
        <th>Service</th>
        <th>Changed</th>
        <th>Message</th>
        <th>Description</th>
        <th>&nbsp;</th>
    </tr>
<?php
if ($_SESSION['userlevel'] >= '5') {
    $host_sql = 'SELECT * FROM devices AS D, services AS S WHERE D.device_id = S.device_id ORDER BY D.hostname';
    $host_par = array();
} else {
    $host_sql = 'SELECT * FROM devices AS D, services AS S, devices_perms AS P WHERE D.device_id = S.device_id AND D.device_id = P.device_id AND P.user_id = ? ORDER BY D.hostname';
    $host_par = array($_SESSION['user_id']);
}

$shift = 1;
foreach (dbFetchRows($host_sql, $host_par) as $device) {
    $device_id       = $device['device_id'];
    $device_hostname = $device['hostname'];
    $devlink = generate_device_link($device, null, array('tab' => 'services'));
    if ($shift == 1) {
        array_unshift($sql_param, $device_id);
        $shift = 0;
    } else {
        $sql_param[0] = $device_id;
    }

    foreach (dbFetchRows("SELECT * FROM `services` WHERE `device_id` = ? $where", $sql_param) as $service) {
        if ($service['service_status'] == '2') {
            $status = "<span class='red'><b>".$service['service_type']."</b></span>";
        } elseif ($service['service_status'] == '0') {
            $status = "<span class='green'><b>".$service['service_type']."</b></span>";
        } else {
            $status = "<span class='grey'><b>".$service['service_type']."</b></span>";
        }
?>
    <tr id="row_<?php echo $service['service_id']?>">
        <td><?php echo $devlink?></td>
        <td><?php echo $status?></td>
        <td><?php echo formatUptime(time() - $service['service_changed'])?></td>
        <td><span class='box-desc'><?php echo nl2br(display($service['service_message']))?></span></td>
        <td><span class='box-desc'><?php echo nl2br(display($service['service_desc']))?></span></td>
        <td>
            <button type='button' class='btn btn-primary btn-sm' aria-label='Edit' data-toggle='modal' data-target='#create-service' data-service_id='<?php echo $service['service_id']?>' name='edit-service'><i class='fa fa-pencil' aria-hidden='true'></i></button>
            <button type='button' class='btn btn-danger btn-sm' aria-label='Delete' data-toggle='modal' data-target='#confirm-delete' data-service_id='<?php echo $service['service_id']?>' name='delete-service'><i class='fa fa-trash' aria-hidden='true'></i></button>
        </td>
    </tr>
<?php
    }//end foreach

    unset($samehost);
}//end foreach
?>
</table>
