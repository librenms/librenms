<?php

require_once $config['install_dir'].'/includes/device-groups.inc.php';
require_once $config['install_dir'].'/includes/alerts.inc.php';

$where = 1;

$show_recovered = FALSE;

if (is_numeric($_POST['device_id']) && $_POST['device_id'] > 0) {
    $where .= ' AND `alerts`.`device_id`='.$_POST['device_id'];
}

if (is_numeric($_POST['acknowledged'])) {
    // I assume that if we are searching for acknowleged/not, we aren't interested in recovered
    $where .= " AND `alerts`.`state`".($_POST['acknowledged'] ? "=" : "!=").AlertState::ACKNOWLEDGED;
}

if (is_numeric($_POST['state'])) {
    $where .= " AND `alerts`.`state`=".$_POST['state'];
    if ($_POST['state'] === (string)AlertState::RECOVERED) {
        $show_recovered = TRUE;
    }
}

if (isset($_POST['min_severity'])) {
    if (is_numeric($_POST['min_severity'])) {
        $min_severity_id = $_POST['min_severity'];
    }
    else if (!empty($_POST['min_severity'])) {
        $min_severity_id = array_search($_POST['min_severity'], $alert_severities);
    }
    if (isset($min_severity_id)) {
        $where .= " AND `alert_rules`.`severity` >= ".$min_severity_id;
    }
}

if (is_numeric($_POST['group'])) {
    $group_pattern = dbFetchCell('SELECT `pattern` FROM `device_groups` WHERE id = '.$_POST['group']);
    $group_pattern = rtrim($group_pattern, '&&');
    $group_pattern = rtrim($group_pattern, '||');

    $device_id_sql = GenGroupSQL($group_pattern);
    if ($device_id_sql) {
        $where .= " AND devices.device_id IN ($device_id_sql)";
    }
}

if (!$show_recovered) {
    $where .= " AND `alerts`.`state`!=".AlertState::RECOVERED;
}

if (isset($searchPhrase) && !empty($searchPhrase)) {
    $where .= " AND (`timestamp` LIKE '%$searchPhrase%' OR `rule` LIKE '%$searchPhrase%' OR `name` LIKE '%$searchPhrase%' OR `hostname` LIKE '%$searchPhrase%')";
}

$sql = ' FROM `alerts` LEFT JOIN `devices` ON `alerts`.`device_id`=`devices`.`device_id`';

if (is_admin() === false && is_read() === false) {
    $sql    .= ' LEFT JOIN `devices_perms` AS `DP` ON `devices`.`device_id` = `DP`.`device_id`';
    $where  .= ' AND `DP`.`user_id`=?';
    $param[] = $_SESSION['user_id'];
}

$sql .= "  RIGHT JOIN `alert_rules` ON `alerts`.`rule_id`=`alert_rules`.`id` WHERE $where";

$count_sql = "SELECT COUNT(`alerts`.`id`) $sql";
$total     = dbFetchCell($count_sql, $param);
if (empty($total)) {
    $total = 0;
}

if (!isset($sort) || empty($sort)) {
    $sort = 'timestamp DESC';
}

$sql .= " ORDER BY $sort";

if (isset($current)) {
    $limit_low  = (($current * $rowCount) - ($rowCount));
    $limit_high = $rowCount;
}

if ($rowCount != -1) {
    $sql .= " LIMIT $limit_low,$limit_high";
}

$sql = "SELECT `alerts`.*, `devices`.`hostname` AS `hostname`, `devices`.`sysName` AS `sysName`,`alert_rules`.`rule` AS `rule`, `alert_rules`.`name` AS `name`, `alert_rules`.`severity` AS `severity` $sql";

$rulei  = 0;
$format = $_POST['format'];
foreach (dbFetchRows($sql, $param) as $alert) {
    $log          = dbFetchCell('SELECT details FROM alert_log WHERE rule_id = ? AND device_id = ? ORDER BY id DESC LIMIT 1', array($alert['rule_id'], $alert['device_id']));
    $fault_detail = alert_details($log);

    $ico   = 'ok';
    $col   = 'green';
    $extra = '';
    $msg   = '';
    if ((int) $alert['state'] === AlertState::RECOVERED) {
        $ico   = 'ok';
        $col   = 'green';
        $extra = 'success';
        $msg   = 'ok';
    }
    else if ((int) $alert['state'] === AlertState::ALERTED || (int) $alert['state'] === AlertState::WORSE || (int) $alert['state'] === AlertState::BETTER) {
        $ico   = 'volume-up';
        $col   = 'red';
        $extra = 'danger';
        $msg   = 'alert';
        if ((int) $alert['state'] === AlertState::WORSE) {
            $msg = 'worse';
        }
        else if ((int) $alert['state'] === AlertState::BETTER) {
            $msg = 'better';
        }
    }
    else if ((int) $alert['state'] === AlertState::ACKNOWLEDGED) {
        $ico   = 'volume-off';
        $col   = '#800080';
        $extra = 'warning';
        $msg   = 'muted';
    }//end if
    $alert_checked = '';
    $orig_ico      = $ico;
    $orig_col      = $col;
    $orig_class    = $extra;

    $severity = $alert['severity'];
    if ($alert['state'] == 3) {
        $severity .= ' <strong>+</strong>';
    }
    else if ($alert['state'] == 4) {
        $severity .= ' <strong>-</strong>';
    }

    $ack_ico = 'volume-up';
    $ack_col = 'success';
    if ($alert['state'] == AlertState::ACKNOWLEDGED) {
        $ack_ico = 'volume-off';
        $ack_col = 'danger';
    }

    $hostname = '
        <div class="incident">
        '.generate_device_link($alert).'
        <div id="incident'.($rulei + 1).'" class="collapse">'.$fault_detail.'</div>
        </div>';

    $response[] = array(
        'id'        => $rulei++,
        'rule'      => '<i title="'.htmlentities($alert['rule']).'"><a href="'.generate_url(array('page'=>'alert-rules')).'">'.htmlentities($alert['name']).'</a></i>',
        'details'   => '<a class="glyphicon glyphicon-plus incident-toggle" style="display:none" data-toggle="collapse" data-target="#incident'.($rulei).'" data-parent="#alerts"></a>',
        'hostname'  => $hostname,
        'timestamp' => ($alert['timestamp'] ? $alert['timestamp'] : 'N/A'),
        'severity'  => $severity,
        'ack_col'   => $ack_col,
        'state'     => $alert['state'],
        'alert_id'  => $alert['id'],
        'ack_ico'   => $ack_ico,
        'extra'     => $extra,
        'msg'       => $msg,
    );
}//end foreach

$output = array(
    'current'  => $current,
    'rowCount' => $rowCount,
    'rows'     => $response,
    'total'    => $total,
);
echo _json_encode($output);
