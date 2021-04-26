<?php

use LibreNMS\Util\Number;

$pagetitle[] = 'Historical Usage';
// $detail      = (isset($vars['detail']) ? $vars['detail'] : "");
// $url         = $PHP_SELF."/bill/".$bill_id."/history/";
$i = 0;

$img['his'] = '<img src="graph.php?id=' . $bill_id;
$img['his'] .= '&amp;type=bill_historicmonthly';
$img['his'] .= '&amp;width=1190&amp;height=250';
$img['his'] .= '" style="margin: 15px 5px 25px 5px;" />';
?>

<h3>Historical Usage</h3>

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">Monthly Usage</h3>
    </div>
    <?php echo $img['his'] ?>
</div>

<?php

function showDetails($bill_id, $imgtype, $bill_hist_id)
{
    $res = '<img src="graph.php?id=' . $bill_id;

    if ($imgtype == 'bitrate') {
        $res .= '&amp;type=bill_historicbits';
    } else {
        $res .= '&amp;type=bill_historictransfer';
        $res .= '&amp;imgtype=' . $imgtype;
    }
    $res .= '&amp;width=1190&amp;height=250';
    if (is_numeric($bill_hist_id)) {
        $res .= '&amp;bill_hist_id=' . $bill_hist_id;
    }
    $res .= '" style="margin: 15px 5px 25px 5px;" />';

    return $res;
}//end showDetails()

// $url        = generate_url($vars, array('detail' => 'yes'));
$url = $PHP_SELF . '/bill/' . $bill_id . '/history/detail=all/';

echo '<table class="table table-striped">
    <thead>
    <tr style="font-weight: bold; ">
        <th width="7"></th>
        <th width="250">Period</th>
        <th>Type</th>
        <th>Allowed</th>
        <th>Inbound</th>
        <th>Outbound</th>
        <th>Total</th>
        <th>95th %ile</th>
        <th style="text-align: center;">Overusage</th>
        <th colspan="2" style="text-align: right;"><a href="' . \LibreNMS\Util\Url::generate($vars, ['detail' => 'all']) . '">
            <i class="fa fa-bar-chart fa-lg icon-theme" aria-hidden="true" title="Show details"></i> Show details</a>
        </th>
    </tr>
    </thead>
    <tbody>';

foreach (dbFetchRows('SELECT * FROM `bill_history` WHERE `bill_id` = ? ORDER BY `bill_datefrom` DESC LIMIT 24', [$bill_id]) as $history) {
    if (bill_permitted($history['bill_id'])) {
        unset($class);
        $datefrom = $history['bill_datefrom'];
        $dateto = $history['bill_dateto'];
        $type = $history['bill_type'];
        $percent = $history['bill_percent'];
        $dir_95th = $history['dir_95th'];
        $rate_95th = Number::formatSi($history['rate_95th'], 2, 3, 'bps');
        $total_data = Number::formatBase($history['traf_total'], \LibreNMS\Config::get('billing.base'), 2, 3, '');

        $background = \LibreNMS\Util\Colors::percentage($percent, null);

        if ($type == 'CDR') {
            $allowed = Number::formatSi($history['bill_allowed'], 2, 3, 'bps');
            $used = Number::formatSi($history['rate_95th'], 2, 3, 'bps');
            $in = Number::formatSi($history['rate_95th_in'], 2, 3, 'bps');
            $out = Number::formatSi($history['rate_95th_out'], 2, 3, 'bps');
            $overuse = (($history['bill_overuse'] <= 0) ? '-' : '<span style="color: #' . $background['left'] . '; font-weight: bold;">' . Number::formatSi($history['bill_overuse'], 2, 3, 'bps') . '</span>');
        } elseif ($type == 'Quota') {
            $allowed = Number::formatBase($history['bill_allowed'], \LibreNMS\Config::get('billing.base'), 2, 3, '');
            $used = Number::formatBase($history['total_data'], \LibreNMS\Config::get('billing.base'), 2, 3, '');
            $in = Number::formatBase($history['traf_in'], \LibreNMS\Config::get('billing.base'), 2, 3, '');
            $out = Number::formatBase($history['traf_out'], \LibreNMS\Config::get('billing.base'), 2, 3, '');
            $overuse = (($history['bill_overuse'] <= 0) ? '-' : '<span style="color: #' . $background['left'] . '; font-weight: bold;">' . Number::formatBase($history['bill_overuse'], \LibreNMS\Config::get('billing.base')) . '</span>');
        }

        $total_data = (($type == 'Quota') ? '<b>' . $total_data . '</b>' : $total_data);
        $rate_95th = (($type == 'CDR') ? '<b>' . $rate_95th . '</b>' : $rate_95th);

        $url = \LibreNMS\Util\Url::generate($vars, ['detail' => $history['bill_hist_id']]);

        echo '
            <tr>
                <td></td>
                <td><span style="font-weight: bold;" class="interface">' . strftime('%Y-%m-%d', strtotime($datefrom)) . ' to ' . strftime('%Y-%m-%d', strtotime($dateto)) . "</span></td>
                <td>$type</td>
                <td>$allowed</td>
                <td>$in</td>
                <td>$out</td>
                <td>$total_data</td>
                <td>$rate_95th</td>
                <td style=\"text-align: center;\">$overuse</td>
                <td width=\"250\">" . print_percentage_bar(250, 20, $percent, null, 'ffffff', $background['left'], $percent . '%', 'ffffff', $background['right']) . '</td>
                <td>
                    <a href="' . $url . '"><i class="fa fa-bar-chart fa-lg icon-theme" aria-hidden="true" title="Show details"></i></a>
                </td>
            </tr>';

        if ($vars['detail'] == $history['bill_hist_id'] || $vars['detail'] == 'all') {
            $img['bitrate'] = showDetails($bill_id, 'bitrate', $history['bill_hist_id']);
            $img['bw_day'] = showDetails($bill_id, 'day', $history['bill_hist_id']);
            $img['bw_hour'] = showDetails($bill_id, 'hour', $history['bill_hist_id']);
            echo '
                <tr style="background: #fff; border-top: 1px solid ' . $row_colour . '; border-bottom: 1px solid #ccc;">
                    <td colspan="11">
                    <!-- <b>Accuate Graph</b><br /> //-->
                    ' . $img['bitrate'] . '<br />
                    <!-- <b>Bandwidth Graph per day</b><br /> //-->
                    ' . $img['bw_day'] . '<br />
                    <!-- <b>Bandwidth Graph per hour</b><br /> //-->
                    ' . $img['bw_hour'] . '
                    </td>
                </tr>';
        }
    } //end if
}//end foreach

echo '</tbody>
</table>';
