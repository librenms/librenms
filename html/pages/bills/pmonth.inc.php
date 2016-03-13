<?php

$no_refresh = true;
$pagetitle[] = 'Previous Billing Period';

echo '<table class="table table-condensed table-striped">
    <thead>
    <tr>
    <th>Billing name</th>
    <th>Type</th>
    <th>Allowed</th>
    <th>Inbound</th>
    <th>Outbound</th>
    <th>Total</th>
    <th>95 percentile</th>
    <th>Overusage</th>
    <th></th>
    </tr>
    </thead>
    <tbody>';
    
$wheres = array();
$params = array();

if (!empty($_GET['search'])) {
    $wheres[] = 'bills.bill_name LIKE ?';
    $params[] = '%'.$_GET['search'].'%';
}
if (!empty($_GET['bill_type'])) {
    $wheres[] = 'bill_history.bill_type = ?';
    $params[] = $_GET['bill_type'];
}
if ($_GET['state'] === 'under') {
    $wheres[] = 'bill_history.bill_overuse = 0';
} else if ($_GET['state'] === 'over') {
    $wheres[] = 'bill_history.bill_overuse > 0';
}
    
$query = 'SELECT bills.bill_name, bill_history.*
FROM `bills`
    INNER JOIN (SELECT bill_id, MAX(bill_hist_id) AS bill_hist_id FROM bill_history WHERE bill_dateto < NOW() AND bill_dateto > subdate(NOW(), 40) GROUP BY bill_id) qLastBills ON bills.bill_id = qLastBills.bill_id
    INNER JOIN bill_history ON qLastBills.bill_hist_id = bill_history.bill_hist_id
';
if (sizeof($wheres) > 0) {
    $query .= 'WHERE ' . implode(' AND ', $wheres) . "\n";
}
$query .= 'ORDER BY bills.bill_name';

foreach (dbFetchRows($query, $params) as $bill) {
    if (bill_permitted($bill['bill_id'])) {
        $datefrom = $bill['bill_datefrom'];
        $dateto   = $bill['bill_dateto'];

        unset($class);
        $type       = $bill['bill_type'];
        $percent    = $bill['bill_percent'];
        $dir_95th   = $bill['dir_95th'];
        $rate_95th  = format_si($bill['rate_95th']).'bps';
        $total_data = format_bytes_billing($bill['traf_total']);

        $background = get_percentage_colours($percent);

        if ($type == 'CDR') {
            $allowed = format_si($bill['bill_allowed']).'bps';
            $used    = format_si($bill['rate_95th']).'bps';
            $in      = format_si($bill['rate_95th_in']).'bps';
            $out     = format_si($bill['rate_95th_out']).'bps';
            $overuse = (($bill['bill_overuse'] <= 0) ? '-' : '<span style="color: #'.$background['left'].'; font-weight: bold;">'.format_si($bill['bill_overuse']).'bps</span>');
        }
        else if ($type == 'Quota') {
            $allowed = format_bytes_billing($bill['bill_allowed']);
            $used    = format_bytes_billing($bill['total_data']);
            $in      = format_bytes_billing($bill['traf_in']);
            $out     = format_bytes_billing($bill['traf_out']);
            $overuse = (($bill['bill_overuse'] <= 0) ? '-' : '<span style="color: #'.$background['left'].'; font-weight: bold;">'.format_bytes_billing($bill['bill_overuse']).'</span>');
        }

        $total_data = (($type == 'Quota') ? '<b>'.$total_data.'</b>' : $total_data);
        $rate_95th  = (($type == 'CDR') ? '<b>'.$rate_95th.'</b>' : $rate_95th);

        echo "
            <tr>
            <td><a href=\"".generate_url(array('page' => 'bill', 'bill_id' => $bill['bill_id'], 'view' => 'history', detail => $bill['bill_hist_id'])).'"><span style="font-weight: bold;" class="interface">'.$bill['bill_name'].'</a></span><br />from '.strftime('%x', strtotime($datefrom)).' to '.strftime('%x', strtotime($dateto))."</td>
            <td>$type</td>
            <td>$allowed</td>
            <td>$in</td>
            <td>$out</td>
            <td>$total_data</td>
            <td>$rate_95th</td>
            <td style=\"text-align: center;\">$overuse</td>
            <td>".print_percentage_bar(250, 20, $percent, null, 'ffffff', $background['left'], $percent.'%', 'ffffff', $background['right']).'</td>
            </tr>';

    }//end if
}//end foreach

echo '</tbody>
</table>';
