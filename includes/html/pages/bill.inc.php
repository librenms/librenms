<?php

$bill_id = $vars['bill_id'];

if (Auth::user()->hasGlobalAdmin()) {
    include 'includes/html/pages/bill/actions.inc.php';
}

if (bill_permitted($bill_id)) {
    $bill_data = dbFetchRow('SELECT * FROM bills WHERE bill_id = ?', [$bill_id]);

    $bill_name = $bill_data['bill_name'];

    $today = str_replace('-', '', dbFetchCell('SELECT CURDATE()'));
    $yesterday = str_replace('-', '', dbFetchCell('SELECT DATE_SUB(CURDATE(), INTERVAL 1 DAY)'));
    $tomorrow = str_replace('-', '', dbFetchCell('SELECT DATE_ADD(CURDATE(), INTERVAL 1 DAY)'));
    $last_month = str_replace('-', '', dbFetchCell('SELECT DATE_SUB(CURDATE(), INTERVAL 1 MONTH)'));

    $rightnow = $today . date('His');
    $before = $yesterday . date('His');
    $lastmonth = $last_month . date('His');

    $bill_name = $bill_data['bill_name'];
    $dayofmonth = $bill_data['bill_day'];

    $day_data = getDates($dayofmonth);

    $datefrom = $day_data['0'];
    $dateto = $day_data['1'];
    $lastfrom = $day_data['2'];
    $lastto = $day_data['3'];

    $rate_95th = $bill_data['rate_95th'];
    $dir_95th = $bill_data['dir_95th'];
    $total_data = $bill_data['total_data'];
    $rate_average = $bill_data['rate_average'];

    if ($rate_95th > $paid_kb) {
        $over = ($rate_95th - $paid_kb);
        $bill_text = $over . 'Kbit excess.';
        $bill_color = '#cc0000';
    } else {
        $under = ($paid_kb - $rate_95th);
        $bill_text = $under . 'Kbit headroom.';
        $bill_color = '#0000cc';
    }

    $fromtext = dbFetchCell("SELECT DATE_FORMAT($datefrom, '" . \LibreNMS\Config::get('dateformat.mysql.date') . "')");
    $totext = dbFetchCell("SELECT DATE_FORMAT($dateto, '" . \LibreNMS\Config::get('dateformat.mysql.date') . "')");
    $unixfrom = dbFetchCell("SELECT UNIX_TIMESTAMP('$datefrom')");
    $unixto = dbFetchCell("SELECT UNIX_TIMESTAMP('$dateto')");

    $unix_prev_from = dbFetchCell("SELECT UNIX_TIMESTAMP('$lastfrom')");
    $unix_prev_to = dbFetchCell("SELECT UNIX_TIMESTAMP('$lastto')");
    // Speeds up loading for other included pages by setting it before progessing of mysql data!
    $ports = dbFetchRows(
        'SELECT * FROM `bill_ports` AS B, `ports` AS P, `devices` AS D
        WHERE B.bill_id = ? AND P.port_id = B.port_id
        AND D.device_id = P.device_id',
        [$bill_id]
    );

    if (! $vars['view']) {
        $vars['view'] = 'quick';
    }

    function print_port_list($ports)
    {
        echo '<div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Billed Ports</h3>
            </div>
            <div class="list-group">';

        // Collected Earlier
        foreach ($ports as $port) {
            $port = cleanPort($port);
            $portalias = (empty($port['ifAlias']) ? '' : ' - ' . $port['ifAlias'] . '');

            echo '<div class="list-group-item">';
            echo generate_port_link($port, $port['ifName'] . $portalias) . ' on ' . generate_device_link($port);
            echo '</div>';
        }

        echo '</div></div>';
    }//end print_port_list?>

    <h2><?php   echo "Bill: ${bill_data['bill_name']}"; ?></h2>

    <?php
    print_optionbar_start();
    echo '<strong>Bill</strong> &raquo; ';
    $menu_options = [
        'quick' => 'Quick Graphs',
        'accurate' => 'Accurate Graphs',
        'transfer' => 'Transfer Graphs',
        'history' => 'Historical Graphs',
    ];
    if (Auth::user()->hasGlobalAdmin()) {
        $menu_options['edit'] = 'Edit';
        $menu_options['delete'] = 'Delete';
        $menu_options['reset'] = 'Reset';
    }
    $sep = '';
    foreach ($menu_options as $option => $text) {
        echo $sep;
        if ($vars['view'] == $option) {
            echo "<span class='pagemenu-selected'>";
        }

        echo generate_link($text, $vars, ['view' => $option]);
        if ($vars['view'] == $option) {
            echo '</span>';
        }

        $sep = ' | ';
    }

    echo '<div style="font-weight: bold; float: right;"><a href="' . \LibreNMS\Util\Url::generate(['page' => 'bills']) . '/"><i class="fa fa-arrow-left fa-lg icon-theme" aria-hidden="true"></i> Back to Bills</a></div>';

    print_optionbar_end();

    if ($vars['view'] == 'edit' && Auth::user()->hasGlobalAdmin()) {
        include 'includes/html/pages/bill/edit.inc.php';
    } elseif ($vars['view'] == 'delete' && Auth::user()->hasGlobalAdmin()) {
        include 'includes/html/pages/bill/delete.inc.php';
    } elseif ($vars['view'] == 'reset' && Auth::user()->hasGlobalAdmin()) {
        include 'includes/html/pages/bill/reset.inc.php';
    } elseif ($vars['view'] == 'history') {
        include 'includes/html/pages/bill/history.inc.php';
    } elseif ($vars['view'] == 'transfer') {
        include 'includes/html/pages/bill/transfer.inc.php';
    } elseif ($vars['view'] == 'quick' || $vars['view'] == 'accurate') {
        ?>

        <?php   if ($bill_data['bill_type'] == 'quota') { ?>
    <h3>Quota Bill</h3>
        <?php   } elseif ($bill_data['bill_type'] == 'cdr') {  ?>
    <h3>
        CDR / 95th Bill
    </h3>
        <?php           } ?>
<strong>Billing Period from <?php echo $fromtext ?> to <?php echo $totext ?></strong>
<br /><br />

<div class="row">
<div class="col-lg-6 col-lg-push-6">
        <?php print_port_list($ports) ?>
</div>
<div class="col-lg-6 col-lg-pull-6">
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">
            Bill Summary
        </h3>
    </div>
    <table class="table">
    <tr>
        <?php   if ($bill_data['bill_type'] == 'quota') {
            // The Customer is billed based on a pre-paid quota with overage in xB
            $percent = round((($total_data) / $bill_data['bill_quota'] * 100), 2);
            $unit = 'MB';
            $total_data = round($total_data, 2);
            $background = \LibreNMS\Util\Colors::percentage($percent, null);
            $type = '&amp;ave=yes'; ?>
        <td>
            <?php echo format_bytes_billing($total_data) ?> of <?php echo format_bytes_billing($bill_data['bill_quota']) . ' (' . $percent . '%)' ?>
            - Average rate <?php echo \LibreNMS\Util\Number::formatSi($rate_average, 2, 3, 'bps') ?>
        </td>
        <td style="width: 210px;"><?php echo print_percentage_bar(200, 20, $percent, null, 'ffffff', $background['left'], $percent . '%', 'ffffff', $background['right']) ?></td>
        </tr>
        <tr>
            <td colspan="2">
            <?php
            echo 'Predicted usage: ' . format_bytes_billing(getPredictedUsage($bill_data['bill_day'], $bill_data['total_data'])); ?>
            </td>
            <?php
        } elseif ($bill_data['bill_type'] == 'cdr') {
            // The customer is billed based on a CDR with 95th%ile overage
            $unit = 'kbps';
            $cdr = $bill_data['bill_cdr'];
            $rate_95th = round($rate_95th, 2);
            $percent = round((($rate_95th) / $cdr * 100), 2);
            $background = \LibreNMS\Util\Colors::percentage($percent, null);
            $type = '&amp;95th=yes'; ?>
        <td>
            <?php echo \LibreNMS\Util\Number::formatSi($rate_95th, 2, 3, '') . 'bps' ?> of <?php echo \LibreNMS\Util\Number::formatSi($cdr, 2, 3, '') . 'bps (' . $percent . '%)' ?> (95th%ile)
        </td>
        <td style="width: 210px;">
            <?php echo print_percentage_bar(200, 20, $percent, null, 'ffffff', $background['left'], $percent . '%', 'ffffff', $background['right']) ?>
        </td>
        </tr>
        <tr>
            <td colspan="2">
            <?php
                echo 'Predicted usage: ' . \LibreNMS\Util\Number::formatSi(getPredictedUsage($bill_data['bill_day'], $bill_data['rate_95th']), 2, 3, '') . 'bps'; ?>
            </td>

        <?php
        }//end if?>
    </tr>
    </table>
</div>
</div>
</div>

        <?php

        $lastmonth = dbFetchCell('SELECT UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 MONTH))');
        $yesterday = dbFetchCell('SELECT UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 DAY))');
        $rightnow = date('U');

        if ($vars['view'] == 'accurate') {
            $bi = "<img src='billing-graph.php?bill_id=" . $bill_id . '&amp;bill_code=' . $_GET['bill_code'];
            $bi .= '&amp;from=' . $unixfrom . '&amp;to=' . $unixto;
            $bi .= '&amp;x=1190&amp;y=250';
            $bi .= "$type'>";

            $li = "<img src='billing-graph.php?bill_id=" . $bill_id . '&amp;bill_code=' . $_GET['bill_code'];
            $li .= '&amp;from=' . $unix_prev_from . '&amp;to=' . $unix_prev_to;
            $li .= '&amp;x=1190&amp;y=250';
            $li .= "$type'>";

            $di = "<img src='billing-graph.php?bill_id=" . $bill_id . '&amp;bill_code=' . $_GET['bill_code'];
            $di .= '&amp;from=' . \LibreNMS\Config::get('time.day') . '&amp;to=' . \LibreNMS\Config::get('time.now');
            $di .= '&amp;x=1190&amp;y=250';
            $di .= "$type'>";

            $mi = "<img src='billing-graph.php?bill_id=" . $bill_id . '&amp;bill_code=' . $_GET['bill_code'];
            $mi .= '&amp;from=' . $lastmonth . '&amp;to=' . $rightnow;
            $mi .= '&amp;x=1190&amp;y=250';
            $mi .= "$type'>";
        } else {
            $bi = "<img src='graph.php?type=bill_bits&amp;id=" . $bill_id;
            $bi .= '&amp;from=' . $unixfrom . '&amp;to=' . $unixto;
            $bi .= '&amp;width=1000&amp;height=200&amp;total=1&amp;dir=' . $dir_95th . "'>";

            $li = "<img src='graph.php?type=bill_bits&amp;id=" . $bill_id;
            $li .= '&amp;from=' . $unix_prev_from . '&amp;to=' . $unix_prev_to;
            $li .= '&amp;width=1000&amp;height=200&amp;total=1&amp;dir=' . $dir_95th . "'>";

            $di = "<img src='graph.php?type=bill_bits&amp;id=" . $bill_id;
            $di .= '&amp;from=' . \LibreNMS\Config::get('time.day') . '&amp;to=' . \LibreNMS\Config::get('time.now');
            $di .= '&amp;width=1000&amp;height=200&amp;total=1&amp;dir=' . $dir_95th . "'>";

            $mi = "<img src='graph.php?type=bill_bits&amp;id=" . $bill_id;
            $mi .= '&amp;from=' . $lastmonth . '&amp;to=' . $rightnow;
            $mi .= '&amp;width=1000&amp;height=200&amp;total=1&amp;dir=' . $dir_95th . "'>";
        }//end if

        ?>
<div class="panel panel-default">
<div class="panel-heading">
    <h3 class="panel-title">Billing View</h3>
</div>
        <?php echo $bi ?>
</div>

<div class="panel panel-default">
<div class="panel-heading">
    <h3 class="panel-title">24 Hour View</h3>
</div>
        <?php echo $di ?>
</div>

<div class="panel panel-default">
<div class="panel-heading">
    <h3 class="panel-title">Monthly View</h3>
</div>
        <?php echo $mi ?>
</div>
        <?php
    } //end if
} else {
    include 'includes/html/error-no-perm.inc.php';
}//end if
?>
