<?php

$pagetitle[] = 'Bandwidth Graphs';

// $bill_data          = dbFetchRow("SELECT * FROM bills WHERE bill_id = ?", array($bill_id));
// $today              = str_replace("-", "", dbFetchCell("SELECT CURDATE()"));
// $tomorrow           = str_replace("-", "", dbFetchCell("SELECT DATE_ADD(CURDATE(), INTERVAL 1 DAY)"));
// $last_month         = str_replace("-", "", dbFetchCell("SELECT DATE_SUB(CURDATE(), INTERVAL 1 MONTH)"));
// $rightnow           = $today . date(His);
// $before             = $yesterday . date(His);
// $lastmonth          = $last_month . date(His);
// $dayofmonth         = $bill_data['bill_day'];
// $day_data           = getDates($dayofmonth);
// $datefrom           = $day_data['0'];
// $dateto             = $day_data['1'];
// $lastfrom           = $day_data['2'];
// $lastto             = $day_data['3'];
// print_r($bill_data);
$total_data = $bill_data['total_data'];
$in_data = $bill_data['total_data_in'];
$out_data = $bill_data['total_data_out'];

$fromtext = dbFetchCell("SELECT DATE_FORMAT($datefrom, '" . \LibreNMS\Config::get('dateformat.mysql.date') . "')");
$totext = dbFetchCell("SELECT DATE_FORMAT($dateto, '" . \LibreNMS\Config::get('dateformat.mysql.date') . "')");
$unixfrom = dbFetchCell("SELECT UNIX_TIMESTAMP('$datefrom')");
$unixto = dbFetchCell("SELECT UNIX_TIMESTAMP('$dateto')");
$unix_prev_from = dbFetchCell("SELECT UNIX_TIMESTAMP('$lastfrom')");
$unix_prev_to = dbFetchCell("SELECT UNIX_TIMESTAMP('$lastto')");
$lastmonth = dbFetchCell('SELECT UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 MONTH))');
$yesterday = dbFetchCell('SELECT UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 DAY))');
$rightnow = date('U');

$cur_days = date('d', (strtotime('now') - strtotime($datefrom)));
$total_days = round((strtotime($dateto) - strtotime($datefrom)) / (60 * 60 * 24));

$total['data'] = format_bytes_billing($bill_data['total_data']);
if ($bill_data['bill_type'] == 'quota') {
    $total['allow'] = format_bytes_billing($bill_data['bill_quota']);
} else {
    $total['allow'] = '-';
}

$total['ave'] = format_bytes_billing(($bill_data['total_data'] / $cur_days));
$total['est'] = format_bytes_billing(($bill_data['total_data'] / $cur_days * $total_days));
if ($bill_data['bill_type'] == 'quota') {
    $total['per'] = round(($bill_data['total_data'] / $bill_data['bill_quota'] * 100), 2);
} else {
    $total['per'] = round(($bill_data['total_data'] / ($bill_data['total_data'] / $cur_days * $total_days) * 100), 2);
}
$total['bg'] = \LibreNMS\Util\Colors::percentage($total['per'], null);

$in['data'] = format_bytes_billing($bill_data['total_data_in']);
$in['allow'] = $total['allow'];
$in['ave'] = format_bytes_billing(($bill_data['total_data_in'] / $cur_days));
$in['est'] = format_bytes_billing(($bill_data['total_data_in'] / $cur_days * $total_days));
$in['per'] = round(($bill_data['total_data_in'] / $bill_data['total_data'] * 100), 2);
$in['bg'] = \LibreNMS\Util\Colors::percentage($in['per'], null);

$out['data'] = format_bytes_billing($bill_data['total_data_out']);
$out['allow'] = $total['allow'];
$out['ave'] = format_bytes_billing(($bill_data['total_data_out'] / $cur_days));
$out['est'] = format_bytes_billing(($bill_data['total_data_out'] / $cur_days * $total_days));
$out['per'] = round(($bill_data['total_data_out'] / $bill_data['total_data'] * 100), 2);
$out['bg'] = \LibreNMS\Util\Colors::percentage($out['per'], null);

$ousage['over'] = ($bill_data['total_data'] - ($bill_data['bill_quota']));
$ousage['over'] = (($ousage['over'] < 0) ? '0' : $ousage['over']);
$ousage['data'] = \LibreNMS\Util\Number::formatBase($ousage['over'], \LibreNMS\Config::get('billing.base'), 2, 3, '');
$ousage['allow'] = $total['allow'];
$ousage['ave'] = format_bytes_billing(($ousage['over'] / $cur_days));
$ousage['est'] = format_bytes_billing(($ousage['over'] / $cur_days * $total_days));
$ousage['per'] = round((($bill_data['total_data'] / $bill_data['bill_quota'] * 100) - 100), 2);
$ousage['per'] = (($ousage['per'] < 0) ? '0' : $ousage['per']);
$ousage['bg'] = \LibreNMS\Util\Colors::percentage($ousage['per'], null);

function showPercent($per)
{
    $background = \LibreNMS\Util\Colors::percentage($per, null);
    $right_background = $background['right'];
    $left_background = $background['left'];
    $res = print_percentage_bar(200, 20, $per, null, 'ffffff', $left_background, $per . '%', 'ffffff', $right_background);

    return $res;
}//end showPercent()

?>

<h3>Transfer Report</h3>
<strong>Billing Period from <?php echo $fromtext ?> to <?php echo $totext ?></strong>
<br /><br />

<div class="row">
    <div class="col-lg-5 col-lg-push-7">
        <?php print_port_list($ports) ?>
    </div>
    <div class="col-lg-7 col-lg-pull-5">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    Bill Summary
                </h3>
            </div>
            <table class="table table-striped">
            <thead>
                <tr>
                    <th>Bandwidth</th>
                    <th>Used</th>
                    <th>Allowed</th>
                    <th>Average</th>
                    <th>Estimated</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th>Transferred</th>
                    <td><?php echo $total['data'] ?></td>
                    <td><?php echo $total['allow'] ?></td>
                    <td><?php echo $total['ave'] ?></td>
                    <td><?php echo $total['est'] ?></td>
                    <td><?php echo showPercent($total['per']) ?></td>
                </tr>
                <tr>
                    <th>Inbound</th>
                    <td><?php echo $in['data'] ?></td>
                    <td><?php echo $in['allow'] ?></td>
                    <td><?php echo $in['ave'] ?></td>
                    <td><?php echo $in['est'] ?></td>
                    <td><?php echo showPercent($in['per']) ?></td>
                </tr>
                <tr>
                    <th>Outbound</th>
                    <td><?php echo $out['data'] ?></td>
                    <td><?php echo $out['allow'] ?></td>
                    <td><?php echo $out['ave'] ?></td>
                    <td><?php echo $out['est'] ?></td>
                    <td><?php echo showPercent($out['per']) ?></td>
                </tr>
        <?php if ($ousage['over'] > 0 && $bill_data['bill_type'] == 'quota') { ?>
                <tr>
                    <th>Already overusage</th>
                    <td><span style="color: #<?php echo $total['bg']['left'] ?>; font-weight: bold;"><?php echo $ousage['data'] ?></span></td>
                    <td><?php echo $ousage['allow'] ?></td>
                    <td><?php echo $ousage['ave'] ?></td>
                    <td><?php echo $ousage['est'] ?></td>
                    <td><?php echo showPercent($ousage['per']) ?></td>
                </tr>

        <?php } ?>
            </tbody>
            </table>
        </div>
    </div>
</div>



<?php
$bi = "<img src='graph.php?type=bill_historictransfer&id=" . $bill_id;
$bi .= '&amp;from=' . $unixfrom . '&amp;to=' . $unixto;
$bi .= '&amp;imgtype=day';
$bi .= '&amp;width=1190&amp;height=250';
$bi .= "'>";

$di = "<img src='graph.php?type=bill_historictransfer&id=" . $bill_id;
$di .= '&amp;from=' . \LibreNMS\Config::get('time.day') . '&amp;to=' . \LibreNMS\Config::get('time.now');
$di .= '&amp;imgtype=hour';
$di .= '&amp;width=1190&amp;height=250';
$di .= "'>";

$mi = "<img src='graph.php?type=bill_historictransfer&id=" . $bill_id;
$mi .= '&amp;from=' . $lastmonth . '&amp;to=' . $rightnow;
$mi .= '&amp;&imgtype=day';
$mi .= '&amp;width=1190&amp;height=250';
$mi .= "'>";
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">Billing Period View</h3>
    </div>
    <div class="panel-body">
    <?php echo $bi ?>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">Rolling 24 Hour View</h3>
    </div>
    <div class="panel-body">
    <?php echo $di ?>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">Rolling Monthly View</h3>
    </div>
    <div class="panel-body">
    <?php echo $mi ?>
    </div>
</div>
