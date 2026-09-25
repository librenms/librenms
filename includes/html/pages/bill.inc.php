<?php

use App\Facades\LibrenmsConfig;
use App\Models\Bill;
use Illuminate\Support\Facades\Gate;
use LibreNMS\Billing;
use LibreNMS\Util\Html;
use LibreNMS\Util\Number;
use LibreNMS\Util\Url;

$bill_id = (int) ($vars['bill_id'] ?? 0);
$bill = Bill::find($bill_id);

if ($bill === null) {
    abort(404);
}

if (Gate::allows('view', $bill)) {
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

    $day_data = Billing::getDates($dayofmonth, 0);

    $datefrom = $day_data['0'];
    $dateto = $day_data['1'];
    $lastfrom = $day_data['2'];
    $lastto = $day_data['3'];

    $rate_95th = $bill_data['rate_95th'];
    $dir_95th = $bill_data['dir_95th'];
    $total_data = $bill_data['total_data'];
    $rate_average = $bill_data['rate_average'];

    $paid_kb ??= 0;
    if ($rate_95th > $paid_kb) {
        $over = ($rate_95th - $paid_kb);
        $bill_text = $over . 'Kbit excess.';
        $bill_color = '#cc0000';
    } else {
        $under = ($paid_kb - $rate_95th);
        $bill_text = $under . 'Kbit headroom.';
        $bill_color = '#0000cc';
    }

    $fromtext = dbFetchCell("SELECT DATE_FORMAT($datefrom, '" . LibrenmsConfig::get('dateformat.mysql.date') . "')");
    $totext = dbFetchCell("SELECT DATE_FORMAT($dateto, '" . LibrenmsConfig::get('dateformat.mysql.date') . "')");
    $unixfrom = dbFetchCell("SELECT UNIX_TIMESTAMP('$datefrom')");
    $unixto = dbFetchCell("SELECT UNIX_TIMESTAMP('$dateto')");

    $unix_prev_from = dbFetchCell("SELECT UNIX_TIMESTAMP('$lastfrom')");
    $unix_prev_to = dbFetchCell("SELECT UNIX_TIMESTAMP('$lastto')");

    $vars['view'] ??= 'quick';

    function print_source_list(Bill $bill, $removable_bill_id = null)
    {
        $sources = $bill->billableSources();

        echo '<div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Billed Sources</h3>
            </div>
            <div class="panel-body">';

        if ($sources->isEmpty()) {
            echo '<div class="alert alert-info" style="margin-bottom: 0;">There are no sources assigned to this bill</div>';
        } else {
            echo '<div class="list-group" style="margin-bottom: 0;">';
            foreach ($sources as $source) {
                echo '<div class="list-group-item">';
                if ($removable_bill_id) {
                    print_source_remove_button(route('bill.source.detach', [$removable_bill_id, $source->getMorphClass(), $source->getKey()]), $source::billingTypeName());
                }
                echo '<span class="label label-default">' . e($source::billingTypeName()) . '</span> ';
                echo $source->getBillingLink() . ' on ' . Url::deviceLink($source->device);
                echo '</div>';
            }
            echo '</div>';
        }

        echo '</div></div>';
    }//end print_source_list

    function print_source_remove_button($action, $type)
    {
        echo '<form action="' . $action . '" method="post" class="pull-right" onsubmit="return confirm(\'Are you sure you wish to remove this ' . e($type) . '?\')">'
            . csrf_field() . method_field('DELETE')
            . '<button type="submit" class="btn btn-danger btn-xs"><i class="fa fa-minus"></i> Remove</button></form>';
    }//end print_source_remove_button?>

    <h2>Bill: <?php echo htmlentities((string) $bill_data['bill_name']); ?></h2>

    <?php
    print_optionbar_start();
    echo '<strong>Bill</strong> &raquo; ';
    $menu_options = [
        'quick' => 'Quick Graphs',
        'accurate' => 'Accurate Graphs',
        'transfer' => 'Transfer Graphs',
        'history' => 'Historical Graphs',
    ];
    if (Gate::allows('update', $bill)) {
        $menu_options['edit'] = 'Edit';
    }
    if (Gate::allows('delete', $bill)) {
        $menu_options['delete'] = 'Delete';
    }
    if (Gate::allows('update', $bill)) {
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

    if ($vars['view'] == 'edit' && Gate::allows('update', $bill)) {
        include 'includes/html/pages/bill/edit.inc.php';
    } elseif ($vars['view'] == 'delete' && Gate::allows('delete', $bill)) {
        include 'includes/html/pages/bill/delete.inc.php';
    } elseif ($vars['view'] == 'reset' && Gate::allows('update', $bill)) {
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
        <?php print_source_list($bill) ?>
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
            $percent = Number::calculatePercent($total_data, $bill_data['bill_quota']);
            $unit = 'MB';
            $total_data = round($total_data, 2);
            $graph_type = ['ave' => 'yes']; ?>
        <td>
            <?php echo Billing::formatBytes($total_data) ?> of <?php echo Billing::formatBytes($bill_data['bill_quota']) . ' (' . $percent . '%)' ?>
            - Average rate <?php echo Number::formatSi($rate_average, 2, 0, 'bps') ?>
        </td>
        <td style="width: 210px;"><?php echo Html::percentageBar(200, 10, $percent, right_text: $percent . '%'); ?></td>
        </tr>
        <tr>
            <td colspan="2">
            <?php
            echo 'Predicted usage: ' . Billing::formatBytes(Billing::getPredictedUsage($bill_data['bill_day'], $bill_data['total_data'])); ?>
            </td>
            <?php
        } elseif ($bill_data['bill_type'] == 'cdr') {
            // The customer is billed based on a CDR with 95th%ile overage
            $unit = 'kbps';
            $cdr = $bill_data['bill_cdr'];
            $rate_95th = round($rate_95th, 2);
            $percent = Number::calculatePercent($rate_95th, $cdr);
            $graph_type = ['95th' => 'yes']; ?>
        <td>
            <?php echo Number::formatSi($rate_95th, 2, 0, '') . 'bps' ?> of <?php echo Number::formatSi($cdr, 2, 0, '') . 'bps (' . $percent . '%)' ?> (95th%ile)
        </td>
        <td style="width: 210px;">
            <?php echo Html::percentageBar(200, 10, $percent, right_text: $percent . '%'); ?>
        </td>
        </tr>
        <tr>
            <td colspan="2">
            <?php
                echo 'Predicted usage: ' . Number::formatSi(Billing::getPredictedUsage($bill_data['bill_day'], $bill_data['rate_95th']), 2, 0, '') . 'bps'; ?>
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

        $graph_args = [
            'id' => $bill_id,
            'width' => 1190,
            'height' => 250,
        ];
        if (isset($graph_type)) {
            $graph_args = array_merge($graph_args, $graph_type);
        }

        if ($vars['view'] == 'accurate') {
            $graph_args['type'] = 'bill_historicbits';
            $graph_args['bill_code'] = (string) ($_GET['bill_code'] ?? '');
        } else {
            $graph_args['type'] = 'bill_bits';
            $graph_args['width'] = 1000;
            $graph_args['height'] = 200;
            $graph_args['total'] = 1;
            $graph_args['dir'] = $dir_95th;
        }

        $bi = array_merge($graph_args, ['from' => $unixfrom, 'to' => $unixto]);
        $li = array_merge($graph_args, ['from' => $unix_prev_from, 'to' => $unix_prev_to]);
        $di = array_merge($graph_args, ['from' => LibrenmsConfig::get('time.day'), 'to' => LibrenmsConfig::get('time.now')]);
        $mi = array_merge($graph_args, ['from' => $lastmonth, 'to' => $rightnow]);

        ?>
<div class="panel panel-default">
<div class="panel-heading">
    <h3 class="panel-title">Billing View</h3>
</div>
        <?php echo Url::graphTag($bi) ?>
</div>

<div class="panel panel-default">
<div class="panel-heading">
    <h3 class="panel-title">24 Hour View</h3>
</div>
        <?php echo Url::graphTag($di) ?>
</div>

<div class="panel panel-default">
<div class="panel-heading">
    <h3 class="panel-title">Monthly View</h3>
</div>
        <?php echo Url::graphTag($mi) ?>
</div>
        <?php
    } //end if
} else {
    include 'includes/html/error-no-perm.inc.php';
}//end if
?>
