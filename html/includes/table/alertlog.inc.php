<?php

$where = 1;

if (is_numeric($_POST['device_id'])) {
    $where  .= ' AND E.device_id = ?';
    $param[] = $_POST['device_id'];
}

if ($_POST['state'] >= 0) {
    $where .= ' AND `E`.`state` = ?';
    $param[] = mres($_POST['state']);
}

if ($_SESSION['userlevel'] >= '5') {
    $sql = " FROM `alert_log` AS E LEFT JOIN devices AS D ON E.device_id=D.device_id RIGHT JOIN alert_rules AS R ON E.rule_id=R.id WHERE $where";
} else {
    $sql     = " FROM `alert_log` AS E LEFT JOIN devices AS D ON E.device_id=D.device_id RIGHT JOIN alert_rules AS R ON E.rule_id=R.id RIGHT JOIN devices_perms AS P ON E.device_id = P.device_id WHERE $where AND P.user_id = ?";
    $param[] = array($_SESSION['user_id']);
}

if (isset($searchPhrase) && !empty($searchPhrase)) {
    $sql .= " AND (`D`.`hostname` LIKE '%$searchPhrase%' OR `E`.`time_logged` LIKE '%$searchPhrase%' OR `name` LIKE '%$searchPhrase%')";
}

$count_sql = "SELECT COUNT(`E`.`id`) $sql";
$total     = dbFetchCell($count_sql, $param);
if (empty($total)) {
    $total = 0;
}

if (!isset($sort) || empty($sort)) {
    $sort = 'time_logged DESC';
}

$sql .= " ORDER BY $sort";

if (isset($current)) {
    $limit_low  = (($current * $rowCount) - ($rowCount));
    $limit_high = $rowCount;
}

if ($rowCount != -1) {
    $sql .= " LIMIT $limit_low,$limit_high";
}

$sql = "SELECT D.device_id,name AS alert,state,time_logged,DATE_FORMAT(time_logged, '".$config['dateformat']['mysql']['compact']."') as humandate,details $sql";

$rulei = 0;
foreach (dbFetchRows($sql, $param) as $alertlog) {
    $dev          = device_by_id_cache($alertlog['device_id']);
    $fault_detail = alert_details($alertlog['details']);
    $alert_state  = $alertlog['state'];
    if ($alert_state == '0') {
        $glyph_icon  = 'ok';
        $glyph_color = 'green';
        $text        = 'Ok';
    } elseif ($alert_state == '1') {
        $glyph_icon  = 'remove';
        $glyph_color = 'red';
        $text        = 'Alert';
    } elseif ($alert_state == '2') {
        $glyph_icon  = 'info-sign';
        $glyph_color = 'lightgrey';
        $text        = 'Ack';
    } elseif ($alert_state == '3') {
        $glyph_icon  = 'arrow-down';
        $glyph_color = 'orange';
        $text        = 'Worse';
    } elseif ($alert_state == '4') {
        $glyph_icon  = 'arrow-up';
        $glyph_color = 'khaki';
        $text        = 'Better';
    }//end if
    $response[] = array(
        'id'          => $rulei++,
        'time_logged' => $alertlog['humandate'],
        'details'     => '<a class="glyphicon glyphicon-plus incident-toggle" style="display:none" data-toggle="collapse" data-target="#incident'.($rulei).'" data-parent="#alerts"></a>',
        'hostname'    => '<div class="incident">'.generate_device_link($dev, shorthost($dev['hostname'])).'<div id="incident'.($rulei).'" class="collapse">'.$fault_detail.'</div></div>',
        'alert'       => htmlspecialchars($alertlog['alert']),
        'status'      => "<b><span class='glyphicon glyphicon-".$glyph_icon."' style='color:".$glyph_color."'></span> $text</b>",
    );
}//end foreach

$output = array(
    'current'  => $current,
    'rowCount' => $rowCount,
    'rows'     => $response,
    'total'    => $total,
);
echo _json_encode($output);
