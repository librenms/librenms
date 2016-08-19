<?php

$pagetitle[] = 'Historical Usage';
// $detail      = (isset($vars['detail']) ? $vars['detail'] : "");
// $url         = $PHP_SELF."/bill/".$bill_id."/history/";
$i = 0;

$img['his']  = '<img src="bandwidth-graph.php?bill_id='.$bill_id;
$img['his'] .= '&amp;type=historical';
$img['his'] .= '&amp;x=1190&amp;y=250';
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

function showDetails($bill_id, $imgtype, $bill_hist_id, $bittype = 'Quota')
{
    if ($imgtype == 'bitrate') {
        $res = '<img src="billing-graph.php?bill_id='.$bill_id;
        if ($bittype == 'Quota') {
            $res .= '&amp;ave=yes';
        } elseif ($bittype == 'CDR') {
            $res .= '&amp;95th=yes';
        }
    } else {
        $res = '<img src="bandwidth-graph.php?bill_id='.$bill_id;
    }

    // $res .= "&amp;type=".$type;
    $res .= '&amp;type='.$imgtype;
    $res .= '&amp;x=1190&amp;y=250';
    $res .= '&amp;bill_hist_id='.$bill_hist_id;
    $res .= '" style="margin: 15px 5px 25px 5px;" />';
    return $res;
}//end showDetails()


// $url        = generate_url($vars, array('detail' => 'yes'));
$url = $PHP_SELF.'/bill/'.$bill_id.'/history/detail=all/';

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
        <th colspan="2" style="text-align: right;"><a href="'.generate_url($vars, array('detail' => 'all')).'">
            <img src="images/16/chart_curve.png" border="0" align="absmiddle" alt="Show details" title="Show details" /> Show all details</a>
        </th>
    </tr>
    </thead>
    <tbody>';

foreach (dbFetchRows('SELECT * FROM `bill_history` WHERE `bill_id` = ? ORDER BY `bill_datefrom` DESC LIMIT 24', array($bill_id)) as $history) {
    if (bill_permitted($history['bill_id'])) {
        unset($class);
        $datefrom   = $history['bill_datefrom'];
        $dateto     = $history['bill_dateto'];
        $type       = $history['bill_type'];
        $percent    = $history['bill_percent'];
        $dir_95th   = $history['dir_95th'];
        $rate_95th  = formatRates($history['rate_95th']);
        $total_data = format_number($history['traf_total'], $config['billing']['base']);

        $background = get_percentage_colours($percent);

        if ($type == 'CDR') {
            $allowed = formatRates($history['bill_allowed']);
            $used    = formatRates($history['rate_95th']);
            $in      = formatRates($history['rate_95th_in']);
            $out     = formatRates($history['rate_95th_out']);
            $overuse = (($history['bill_overuse'] <= 0) ? '-' : '<span style="color: #'.$background['left'].'; font-weight: bold;">'.formatRates($history['bill_overuse']).'</span>');
        } elseif ($type == 'Quota') {
            $allowed = format_number($history['bill_allowed'], $config['billing']['base']);
            $used    = format_number($history['total_data'], $config['billing']['base']);
            $in      = format_number($history['traf_in'], $config['billing']['base']);
            $out     = format_number($history['traf_out'], $config['billing']['base']);
            $overuse = (($history['bill_overuse'] <= 0) ? '-' : '<span style="color: #'.$background['left'].'; font-weight: bold;">'.format_number($history['bill_overuse'], $config['billing']['base']).'B</span>');
        }

        $total_data = (($type == 'Quota') ? '<b>'.$total_data.'</b>' : $total_data);
        $rate_95th  = (($type == 'CDR') ? '<b>'.$rate_95th.'</b>' : $rate_95th);

        $url = generate_url($vars, array('detail' => $history['bill_hist_id']));

        echo '
            <tr>
                <td></td>
                <td><span style="font-weight: bold;" class="interface">'.strftime('%Y-%m-%d', strtotime($datefrom)).' to '.strftime('%Y-%m-%d', strtotime($dateto))."</span></td>
                <td>$type</td>
                <td>$allowed</td>
                <td>$in</td>
                <td>$out</td>
                <td>$total_data</td>
                <td>$rate_95th</td>
                <td style=\"text-align: center;\">$overuse</td>
                <td width=\"250\">".print_percentage_bar(250, 20, $percent, null, 'ffffff', $background['left'], $percent.'%', 'ffffff', $background['right']).'</td>
                <td>
                    <a href="'.$url.'"><img src="images/16/chart_curve.png" border="0" align="absmiddle" alt="Show details" title="Show details"/></a>
                </td>
            </tr>';

        if ($vars['detail'] == $history['bill_hist_id'] || $vars['detail'] == 'all') {
            $img['bitrate'] = showDetails($bill_id, 'bitrate', $history['bill_hist_id'], $type);
            $img['bw_day']  = showDetails($bill_id, 'day', $history['bill_hist_id']);
            $img['bw_hour'] = showDetails($bill_id, 'hour', $history['bill_hist_id']);
            echo '
                <tr style="background: #fff; border-top: 1px solid '.$row_colour.'; border-bottom: 1px solid #ccc;">
                    <td colspan="11">
                    <!-- <b>Accuate Graph</b><br /> //-->
                    '.$img['bitrate'].'<br />
                    <!-- <b>Bandwidth Graph per day</b><br /> //-->
                    '.$img['bw_day'].'<br />
                    <!-- <b>Bandwidth Graph per hour</b><br /> //-->
                    '.$img['bw_hour'].'
                    </td>
                </tr>';
        }
    } //end if
}//end foreach

echo '</tbody>
</table>';
