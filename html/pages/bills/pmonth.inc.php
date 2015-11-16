<?php

$pagetitle[] = 'Previous Billing Period';
$i           = 0;

echo '<table class="table table-condensed">
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
    </tr>';

foreach (dbFetchRows('SELECT * FROM `bills` ORDER BY `bill_name`') as $bill) {
    if (bill_permitted($bill['bill_id'])) {
        $day_data = getDates($bill['bill_day']);
        $datefrom = $day_data['2'];
        $dateto   = $day_data['3'];
        foreach (dbFetchRows('SELECT * FROM `bill_history` WHERE `bill_id` = ? AND `bill_datefrom` = ? ORDER BY `bill_datefrom` LIMIT 1', array($bill['bill_id'], $datefrom, $dateto)) as $history) {
            unset($class);
            $type       = $history['bill_type'];
            $percent    = $history['bill_percent'];
            $dir_95th   = $history['dir_95th'];
            $rate_95th  = format_si($history['rate_95th']).'bps';
            $total_data = format_bytes_billing($history['traf_total']);

            $background = get_percentage_colours($percent);
            $row_colour = ((!is_integer($i / 2)) ? $list_colour_a : $list_colour_b);

            if ($type == 'CDR') {
                $allowed = format_si($history['bill_allowed']).'bps';
                $used    = format_si($history['rate_95th']).'bps';
                $in      = format_si($history['rate_95th_in']).'bps';
                $out     = format_si($history['rate_95th_out']).'bps';
                $overuse = (($history['bill_overuse'] <= 0) ? '-' : '<span style="color: #'.$background['left'].'; font-weight: bold;">'.format_si($history['bill_overuse']).'bps</span>');
            }
            else if ($type == 'Quota') {
                $allowed = format_bytes_billing($history['bill_allowed']);
                $used    = format_bytes_billing($history['total_data']);
                $in      = format_bytes_billing($history['traf_in']);
                $out     = format_bytes_billing($history['traf_out']);
                $overuse = (($history['bill_overuse'] <= 0) ? '-' : '<span style="color: #'.$background['left'].'; font-weight: bold;">'.format_bytes_billing($history['bill_overuse']).'</span>');
            }

            $total_data = (($type == 'Quota') ? '<b>'.$total_data.'</b>' : $total_data);
            $rate_95th  = (($type == 'CDR') ? '<b>'.$rate_95th.'</b>' : $rate_95th);

            echo "
                <tr style=\"background: $row_colour;\">
                <td><a href=\"".generate_url(array('page' => 'bill', 'bill_id' => $bill['bill_id'])).'"><span style="font-weight: bold;" class="interface">'.$bill['bill_name'].'</a></span><br />from '.strftime('%x', strtotime($datefrom)).' to '.strftime('%x', strtotime($dateto))."</td>
                <td>$type</td>
                <td>$allowed</td>
                <td>$in</td>
                <td>$out</td>
                <td>$total_data</td>
                <td>$rate_95th</td>
                <td style=\"text-align: center;\">$overuse</td>
                <td>".print_percentage_bar(250, 20, $percent, null, 'ffffff', $background['left'], $percent.'%', 'ffffff', $background['right']).'</td>
                </tr>';

            $i++;
        } //end foreach
    }//end if
}//end foreach

echo '</table>';
