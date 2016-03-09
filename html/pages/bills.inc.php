<?php

$no_refresh = true;

if ($_POST['addbill'] == 'yes') {
    if ($_SESSION['userlevel'] < 10) {
        include 'includes/error-no-perm.inc.php';
        exit;
    }
    
    $updated = '1';

    if (isset($_POST['bill_quota']) or isset($_POST['bill_cdr'])) {
        if ($_POST['bill_type'] == 'quota') {
            if (isset($_POST['bill_quota_type'])) {
                if ($_POST['bill_quota_type'] == 'MB') {
                    $multiplier = (1 * $config['billing']['base']);
                }

                if ($_POST['bill_quota_type'] == 'GB') {
                    $multiplier = (1 * $config['billing']['base'] * $config['billing']['base']);
                }

                if ($_POST['bill_quota_type'] == 'TB') {
                    $multiplier = (1 * $config['billing']['base'] * $config['billing']['base'] * $config['billing']['base']);
                }

                $bill_quota = (is_numeric($_POST['bill_quota']) ? $_POST['bill_quota'] * $config['billing']['base'] * $multiplier : 0);
                $bill_cdr   = 0;
            }
        }

        if ($_POST['bill_type'] == 'cdr') {
            if (isset($_POST['bill_cdr_type'])) {
                if ($_POST['bill_cdr_type'] == 'Kbps') {
                    $multiplier = (1 * $config['billing']['base']);
                }

                if ($_POST['bill_cdr_type'] == 'Mbps') {
                    $multiplier = (1 * $config['billing']['base'] * $config['billing']['base']);
                }

                if ($_POST['bill_cdr_type'] == 'Gbps') {
                    $multiplier = (1 * $config['billing']['base'] * $config['billing']['base'] * $config['billing']['base']);
                }

                $bill_cdr   = (is_numeric($_POST['bill_cdr']) ? $_POST['bill_cdr'] * $multiplier : 0);
                $bill_quota = 0;
            }
        }
    }//end if

    $insert = array(
        'bill_name'   => $_POST['bill_name'],
        'bill_type'   => $_POST['bill_type'],
        'bill_cdr'    => $bill_cdr,
        'bill_day'    => $_POST['bill_day'],
        'bill_quota'  => $bill_quota,
        'bill_custid' => $_POST['bill_custid'],
        'bill_ref'    => $_POST['bill_ref'],
        'bill_notes'  => $_POST['bill_notes'],
        'rate_95th_in'      => 0,
        'rate_95th_out'     => 0,
        'rate_95th'         => 0,
        'dir_95th'          => 'in',
        'total_data'        => 0,
        'total_data_in'     => 0,
        'total_data_out'    => 0,
        'rate_average'      => 0,
        'rate_average_in'   => 0,
        'rate_average_out'  => 0,
        'bill_last_calc'    => array('NOW()'),
        'bill_autoadded'    => 0,
    );

    $bill_id = dbInsert($insert, 'bills');

    if (is_numeric($bill_id) && is_numeric($_POST['port'])) {
        dbInsert(array('bill_id' => $bill_id, 'port_id' => $_POST['port']), 'bill_ports');
    }
    
    header('Location: /' . generate_url(array('page' => 'bill', 'bill_id' => $bill_id, 'view' => 'edit')));
    exit();
}

$pagetitle[] = 'Billing';

echo "<meta http-equiv='refresh' content='10000'>";

if ($vars['view'] == 'history') {
    include 'pages/bills/search.inc.php';
    include 'pages/bills/pmonth.inc.php';
}
else {
    include 'pages/bills/search.inc.php';
    include 'includes/modal/new_bill.inc.php';
?>
    <table class="table table-striped">
    <thead>
        <th>Billing name</th>
        <th></th>
        <th>Type</th>
        <th>Allowed</th>
        <th>Used</th>
        <th>Overusage</th>
        <th></th>
        <th></th>
    </thead>
    <tbody>
<?php
    foreach (dbFetchRows('SELECT * FROM `bills` ORDER BY `bill_name`') as $bill) {
        if (bill_permitted($bill['bill_id'])) {
            unset($class);
            $day_data = getDates($bill['bill_day']);
            $datefrom = $day_data['0'];
            $dateto   = $day_data['1'];
            $rate_data    = $bill;
            $rate_95th    = $rate_data['rate_95th'];
            $dir_95th     = $rate_data['dir_95th'];
            $total_data   = $rate_data['total_data'];
            $rate_average = $rate_data['rate_average'];

            if ($bill['bill_type'] == 'cdr') {
                $type       = 'CDR';
                $allowed    = format_si($bill['bill_cdr']).'bps';
                $used       = format_si($rate_data['rate_95th']).'bps';
                $percent    = round((($rate_data['rate_95th'] / $bill['bill_cdr']) * 100), 2);
                $background = get_percentage_colours($percent);
                $overuse    = ($rate_data['rate_95th'] - $bill['bill_cdr']);
                $overuse    = (($overuse <= 0) ? '-' : '<span style="color: #'.$background['left'].'; font-weight: bold;">'.format_si($overuse).'bps</span>');
            }
            else if ($bill['bill_type'] == 'quota') {
                $type       = 'Quota';
                $allowed    = format_bytes_billing($bill['bill_quota']);
                $used       = format_bytes_billing($rate_data['total_data']);
                $percent    = round((($rate_data['total_data'] / ($bill['bill_quota'])) * 100), 2);
                $background = get_percentage_colours($percent);
                $overuse    = ($rate_data['total_data'] - $bill['bill_quota']);
                $overuse    = (($overuse <= 0) ? '-' : '<span style="color: #'.$background['left'].'; font-weight: bold;">'.format_bytes_billing($overuse).'</span>');
            }

            $right_background = $background['right'];
            $left_background  = $background['left'];
?>
        <tr>
            <td>
                <a href='<?php echo generate_url(array('page' => 'bill', 'bill_id' => $bill['bill_id'])) ?>'><span style='font-weight: bold;' class=interface><?php echo $bill['bill_name'] ?></span></a>
                <br />  
                <?php echo strftime('%F', strtotime($datefrom)) ?> to <?php echo strftime('%F', strtotime($dateto)) ?>
            </td>
            <td><?php echo $notes ?></td>
            <td><?php echo $type ?></td>
            <td><?php echo $allowed ?></td>
            <td><?php echo $used ?></td>
            <td style="text-align: center;"><?php echo $overuse ?></td>
            <td><?php echo print_percentage_bar(250, 20, $percent, null, 'ffffff', $background['left'], $percent.'%', 'ffffff', $background['right'])?></td>
            <td>
                <?php if ($_SESSION['userlevel'] >= 10) { ?>
                <a href='<?php echo generate_url(array('page' => 'bill', 'bill_id' => $bill['bill_id'], 'view' => 'edit')) ?>'><img src='images/16/wrench.png' align=absmiddle alt='Edit'> Edit</a>
                <?php } ?>
            </td>
        </tr>
<?php   }
    }?>
    </tbody>
    </table>
<?php 
    if ($vars['view'] == 'add') {
?>
    <script type="text/javascript">
        $(function() {
            $('#create-bill').modal('show');    
        });
    </script>
<?php
    }
}
