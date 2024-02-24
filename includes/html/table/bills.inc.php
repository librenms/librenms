<?php

// Calculate filters
use LibreNMS\Billing;
use LibreNMS\Util\Number;

$prev = ! empty($vars['period']) && ($vars['period'] == 'prev');
$wheres = [];
$param = [];
if (isset($searchPhrase) && ! empty($searchPhrase)) {
    $wheres[] = 'bills.bill_name LIKE ?';
    $param[] = "%$searchPhrase%";
}
if (! empty($vars['bill_type'])) {
    if ($prev) {
        $wheres[] = 'bill_history.bill_type = ?';
    } else {
        $wheres[] = 'bill_type = ?';
    }
    $param[] = $vars['bill_type'];
}
if (! empty($vars['state'])) {
    if ($vars['state'] === 'under') {
        if ($prev) {
            $wheres[] = "((bill_history.bill_type = 'cdr' AND bill_history.rate_95th <= bill_history.bill_allowed) OR (bill_history.bill_type = 'quota' AND bill_history.traf_total <= bill_history.bill_allowed))";
        } else {
            $wheres[] = "((bill_type = 'cdr' AND rate_95th <= bill_cdr) OR (bill_type = 'quota' AND total_data <= bill_quota))";
        }
    } elseif ($vars['state'] === 'over') {
        if ($prev) {
            $wheres[] = "((bill_history.bill_type = 'cdr' AND bill_history.rate_95th > bill_history.bill_allowed) OR (bill_history.bill_type = 'quota' AND bill_history.traf_total > bill_allowed))";
        } else {
            $wheres[] = "((bill_type = 'cdr' AND rate_95th > bill_cdr) OR (bill_type = 'quota' AND total_data > bill_quota))";
        }
    }
}

if ($prev) {
    $select = 'SELECT bills.bill_name, bills.bill_notes, bill_history.*, bill_history.traf_total as total_data, bill_history.traf_in as total_data_in, bill_history.traf_out as total_data_out ';
    $query = 'FROM `bills`
        INNER JOIN (SELECT bill_id, MAX(bill_hist_id) AS bill_hist_id FROM bill_history WHERE bill_dateto < NOW() AND bill_dateto > subdate(NOW(), 40) GROUP BY bill_id) qLastBills ON bills.bill_id = qLastBills.bill_id
        INNER JOIN bill_history ON qLastBills.bill_hist_id = bill_history.bill_hist_id
';
} else {
    $select = "SELECT bills.*,
        IF(bills.bill_type = 'CDR', bill_cdr, bill_quota) AS bill_allowed
    ";
    $query = "FROM `bills`\n";
}

// Permissions check
if (! Auth::user()->hasGlobalRead()) {
    $query .= ' INNER JOIN `bill_perms` AS `BP` ON `bills`.`bill_id` = `BP`.`bill_id` ';
    $wheres[] = '`BP`.`user_id`=?';
    $param[] = Auth::id();
}

if (sizeof($wheres) > 0) {
    $query .= 'WHERE ' . implode(' AND ', $wheres) . "\n";
}
$orderby = 'ORDER BY bills.bill_name';

$total = dbFetchCell("SELECT COUNT(bills.bill_id) $query", $param);

$sql = "$select
$query";

if (! isset($sort) || empty($sort)) {
    $sort = 'bills.bill_name';
}

$sql .= "\nORDER BY $sort";

if (isset($current)) {
    $limit_low = (($current * $rowCount) - $rowCount);
    $limit_high = $rowCount;
}

if ($rowCount != -1) {
    $sql .= " LIMIT $limit_low,$limit_high";
}

foreach (dbFetchRows($sql, $param) as $bill) {
    if ($prev) {
        $datefrom = $bill['bill_datefrom'];
        $dateto = $bill['bill_dateto'];
    } else {
        $day_data = Billing::getDates($bill['bill_day'], 0);
        $datefrom = $day_data['0'];
        $dateto = $day_data['1'];
    }
    $rate_95th = Number::formatSi($bill['rate_95th'], 2, 3, '') . 'bps';
    $dir_95th = $bill['dir_95th'];
    $total_data = Billing::formatBytes($bill['total_data']);
    $rate_average = $bill['rate_average'];
    $url = \LibreNMS\Util\Url::generate(['page' => 'bill', 'bill_id' => $bill['bill_id']]);
    $used95th = Number::formatSi($bill['rate_95th'], 2, 3, '') . 'bps';
    $notes = htmlentities($bill['bill_notes']);

    if ($prev) {
        $percent = $bill['bill_percent'];
        $overuse = $bill['bill_overuse'];
    } else {
    }

    if (strtolower($bill['bill_type']) == 'cdr') {
        $type = 'CDR';
        $allowed = Number::formatSi($bill['bill_allowed'], 2, 3, '') . 'bps';
        $in = Number::formatSi($bill['rate_95th_in'], 2, 3, '') . 'bps';
        $out = Number::formatSi($bill['rate_95th_out'], 2, 3, '') . 'bps';
        if (! $prev) {
            $percent = Number::calculatePercent($bill['rate_95th'], $bill['bill_allowed']);
            $overuse = ($bill['rate_95th'] - $bill['bill_allowed']);
        }

        $overuse_formatted = Number::formatSi($overuse, 2, 3, '') . 'bps';
        $used = $rate_95th;
        $tmp_used = $bill['rate_95th'];
        $rate_95th = "<b>$rate_95th</b>";
    } elseif (strtolower($bill['bill_type']) == 'quota') {
        $type = 'Quota';
        $allowed = Billing::formatBytes($bill['bill_allowed']);
        if (! empty($prev)) {
            $in = Billing::formatBytes($bill['traf_in']);
            $out = Billing::formatBytes($bill['traf_out']);
        } else {
            $in = Billing::formatBytes($bill['total_data_in']);
            $out = Billing::formatBytes($bill['total_data_out']);
        }
        if (! $prev) {
            $percent = Number::calculatePercent($bill['total_data'], $bill['bill_allowed']);
            $overuse = ($bill['total_data'] - $bill['bill_allowed']);
        }

        $overuse_formatted = Billing::formatBytes($overuse);
        $used = $total_data;
        $tmp_used = $bill['total_data'];
        $total_data = "<b>$total_data</b>";
    }

    $background = \LibreNMS\Util\Color::percentage($percent, null);
    $right_background = $background['right'];
    $left_background = $background['left'];
    $overuse_formatted = (($overuse <= 0) ? '-' : "<span style='color: #${background['left']}; font-weight: bold;'>$overuse_formatted</span>");

    $bill_name = "<a href='$url'><span style='font-weight: bold;' class='interface'>" . htmlentities($bill['bill_name']) . '</span></a><br />' .
                    date('Y-m-d', strtotime($datefrom)) . ' to ' . date('Y-m-d', strtotime($dateto));
    $bar = print_percentage_bar(250, 20, $percent, null, 'ffffff', $background['left'], $percent . '%', 'ffffff', $background['right']);
    $actions = '';

    if (! $prev && Auth::user()->hasGlobalAdmin()) {
        $actions .= "<a href='" . \LibreNMS\Util\Url::generate(['page' => 'bill', 'bill_id' => $bill['bill_id'], 'view' => 'edit']) .
            "'><i class='fa fa-pencil fa-lg icon-theme' title='Edit' aria-hidden='true'></i> Edit</a> ";
    }
    if (strtolower($bill['bill_type']) == 'cdr') {
        $predicted = Number::formatSi(Billing::getPredictedUsage($bill['bill_day'], $tmp_used), 2, 3, '') . 'bps';
    } elseif (strtolower($bill['bill_type']) == 'quota') {
        $predicted = Billing::formatBytes(Billing::getPredictedUsage($bill['bill_day'], $tmp_used));
    }

    $response[] = [
        'bill_name' => $bill_name,
        'notes' => $notes,
        'bill_type' => $type,
        'bill_allowed' => $allowed,
        'total_data_in' => $in,
        'total_data_out' => $out,
        'total_data' => $total_data,
        'rate_95th' => $rate_95th,
        'used' => $used,
        'overusage' => $overuse_formatted,
        'predicted' => $predicted,
        'graph' => $bar,
        'actions' => $actions,
    ];
}

$output = ['current' => $current, 'rowCount' => $rowCount, 'rows' => $response, 'total' => $total];
echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
