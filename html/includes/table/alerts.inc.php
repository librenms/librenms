<?php

$where = 1;

if (is_numeric($_POST['device_id']) && $_POST['device_id'] > 0) {
    $where .= ' AND `alerts`.`device_id`='.$_POST['device_id'];
}

if (isset($searchPhrase) && !empty($searchPhrase)) {
    $sql .= " AND (`timestamp` LIKE '%?%' OR `rule` LIKE '%?%' OR `name` LIKE '%?%' OR `hostname` LIKE '%?%')";
    $param[] = array($searchPhrase,$searchPhrase,$searchPhrase,$serchPhrase);
}

$sql = " FROM `alerts` LEFT JOIN `devices` ON `alerts`.`device_id`=`devices`.`device_id` RIGHT JOIN alert_rules ON alerts.rule_id=alert_rules.id WHERE $where AND `state` IN (1,2,3,4) $sql";

$count_sql = "SELECT COUNT(`alerts`.`id`) $sql";
$total = dbFetchCell($count_sql,$param);

if (!isset($sort) || empty($sort)) {
    $sort = 'timestamp DESC';
}

$sql .= " ORDER BY $sort";

if (isset($current)) {
    $limit_low = ($current * $rowCount) - ($rowCount);
    $limit_high = $rowCount;
}

if ($rowCount != -1) {
    $sql .= " LIMIT $limit_low,$limit_high";
}

$sql = "SELECT `alerts`.*, `devices`.`hostname` AS `hostname`,`alert_rules`.`rule` AS `rule`, `alert_rules`.`name` AS `name`, `alert_rules`.`severity` AS `severity` $sql";

$rulei = 0;
foreach (dbFetchRows($sql,$param) as $alert) {
    $log = dbFetchCell("SELECT details FROM alert_log WHERE rule_id = ? AND device_id = ? ORDER BY id DESC LIMIT 1", array($alert['rule_id'],$alert['device_id']));
    $log_detail = json_decode(gzuncompress($log),true);
    foreach ( $log_detail['rule'] as $tmp_alerts ) {
        foreach ($tmp_alerts as $k=>$v) {
            if (!empty($v) && $k != 'device_id' && (stristr($k,'id') || stristr($k,'desc')) && substr_count($k,'_') <= 1) {
                $fault_detail .= $k.' => '.$v."\n ";
            }
        }
        $fault_detail .= "\n";
    }

    $ico = "ok";
    $col = "green";
    $extra = "";
    if ( (int) $alert['state'] === 0 ) {
        $ico = "ok";
        $col = "green";
    } elseif ( (int) $alert['state'] === 1 ) {
        $ico = "volume-up";
        $col = "red";
        $extra = "danger";
    } elseif ( (int) $alert['state'] === 2 ) {
        $ico = "volume-off";
        $col = "#800080";
        $extra = "warning";
    }
    $alert_checked = '';
    $orig_ico = $ico;
    $orig_col = $col;
    $orig_class = $extra;

    $severity = $alert['severity'];
    if ($alert['state'] == 3) {
        $severity .= " <strong>+</strong>";
    } elseif ($alert['state'] == 4) {
        $severity .= " <strong>-</strong>";
    }

    if ($_SESSION['userlevel'] >= '10') {
        $ack_ico = 'volume-up';
        $ack_col = 'success';
        if(in_array($alert['state'],array(2,3,4))) {
            $ack_ico = 'volume-off';
            $ack_col = 'danger';
        }
    }

    $response[] = array('id'=>"<i>#".$rulei++."</i>",
                        'rule'=>"<i title=\"".htmlentities($alert['rule'])."\">".htmlentities($alert['name'])."</i></td>",
                        'hostname'=>"<a href=\"device/device=".$alert['device_id']."\"><i title='".htmlentities($fault_detail)."'>".$alert['hostname']."</i></a></td>",
                        'timestamp'=>($alert['timestamp'] ? $alert['timestamp'] : "N/A"),
                        'severity'=>$severity,
                        'ack_col'=>$ack_col,
                        'state'=>$alert['state'],
                        'alert_id'=>$alert['id'],
                        'ack_ico'=>$ack_ico,
                        'extra'=>$extra);

}

$output = array('current'=>$current,'rowCount'=>$rowCount,'rows'=>$response,'total'=>$total);
echo _json_encode($output);
