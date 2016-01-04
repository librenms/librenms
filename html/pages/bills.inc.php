<?php

if ($_POST['addbill'] == 'yes') {
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

    $message       .= $message_break.'Bill '.mres($_POST['bill_name']).' ('.$bill_id.') added!';
    $message_break .= '<br />';

    if (is_numeric($bill_id) && is_numeric($_POST['port'])) {
        dbInsert(array('bill_id' => $bill_id, 'port_id' => $_POST['port']), 'bill_ports');
        $message       .= $message_break.'Port '.mres($_POST['port']).' added!';
        $message_break .= '<br />';
    }
}

$pagetitle[] = 'Billing';

echo "<meta http-equiv='refresh' content='10000'>";

if ($vars['view'] == 'history') {
    include 'pages/bills/search.inc.php';
    include 'pages/bills/pmonth.inc.php';
}
else if ($vars['view'] == 'add') {
    if (is_numeric($vars['port'])) {
        $port = dbFetchRow('SELECT * FROM `ports` AS P, `devices` AS D WHERE `port_id` = ? AND D.device_id = P.device_id', array($vars['port']));
    }

?>

<?php
    print_optionbar_start();
    echo "<span style='font-weight: bold;'>Bill</span> &#187; ";
    if (!$vars['view']) {
        $vars['view'] = 'add';
    }

    if ($_SESSION['userlevel'] >= '10') {
        if ($vars['view'] == 'add') {
            echo "<span class='pagemenu-selected'>";
        }

        echo '<A href="'.generate_url(array('page' => 'bills/add')).'">Add</a>';
        if ($vars['view'] == 'add') {
            echo '</span>';
        }
    }

    echo '<div style="font-weight: bold; float: right;"><a href="'.generate_url(array('page' => 'bills')).'/"><img align=absmiddle src="images/16/arrow_left.png"> Back to Bills</a></div>';
    print_optionbar_end();
?>

<h3>Bill : Add Bill</h3>

<form name="form1" method="post" action="bills/" class="form-horizontal" role="form">
  <input type=hidden name=addbill value=yes>
    <script type="text/javascript">
    function billType() {
        $('#cdrDiv').toggle();
        $('#quotaDiv').toggle();
    }
    </script>
<?php
    if (is_array($port)) {
        $devicebtn = str_replace('list-device', 'btn', generate_device_link($port));
        $portbtn   = str_replace('interface-upup', 'btn', generate_port_link($port));
        $portalias = (empty($port['ifAlias']) ? '' : ' - '.$port['ifAlias'].'');
        $devicebtn = str_replace('">'.$port['hostname'], '" style="color: #000;"><i class="fa fa-asterisk"></i> '.$port['hostname'], $devicebtn);
        $devicebtn = str_replace("overlib('", "overlib('<div style=\'border: 5px solid #e5e5e5; background: #fff; padding: 10px;\'>", $devicebtn);
        $devicebtn = str_replace("<div>',;", "</div></div>',", $devicebtn);
        $portbtn   = str_replace('">'.strtolower($port['ifName']), '" style="color: #000;"><i class="fa fa-random"></i> '.$port['ifName'].''.$portalias, $portbtn);
        $portbtn   = str_replace("overlib('", "overlib('<div style=\'border: 5px solid #e5e5e5; background: #fff; padding: 10px;\'>", $portbtn);
        $portbtn   = str_replace("<div>',;", "</div></div>',", $portbtn);
        echo "  <fieldset>\n";
        echo '    <input type="hidden" name="port" value="'.$port['port_id']."\">\n";
        echo "    <legend>Ports</legend>\n";
        echo "    <div class=\"control-group\">\n";
        echo "      <div class=\"btn-toolbar\">\n";
        echo "        <div class=\"btn-group\">\n";
        echo '          '.$devicebtn."\n";
        echo '          '.$portbtn."\n";
        echo "        </div>\n";
        echo "      </div>\n";
        echo "    </div>\n";
        echo "  </fieldset>\n";
    }
?>

<div class="form-group">
  <label for="bill_name" class="col-sm-2 control-label"><strong>Description</strong></label>
  <div class="col-sm-4">
    <input class="form-control input-sm" type="text" id="bill_name" name="bill_name" value="<?php echo $port['port_descr_descr']; ?>">
  </div>
  <div class="col-sm6">
  </div>
</div>
<div class="form-group">
  <label class="col-sm-2 control-label" for="bill_type"><strong>Billing Type</strong></label>
  <div class="col-sm-10">
    <input type="radio" name="bill_type" value="cdr" checked onchange="javascript: billType();" /> CDR 95th
    <input type="radio" name="bill_type" value="quota" onchange="javascript: billType();" /> Quota
  </div>
</div>
<div class="form-group">
  <div id="cdrDiv">
    <div class="col-sm-2">
    </div>
    <div class="col-sm-3">
      <input class="form-control input-sm" type="text" name="bill_cdr">
    </div>
    <div class="col-sm-3">
      <select name="bill_cdr_type" class="form-control input-sm">
        <option value="Kbps">Kilobits per second (Kbps)</option>
        <option value="Mbps" selected>Megabits per second (Mbps)</option>
        <option value="Gbps">Gigabits per second (Gbps)</option>
      </select>
    </div>
    <div class="col-sm-4">
    </div>
  </div>
  <div id="quotaDiv" style="display: none">
    <div class="col-sm-2">
    </div>
    <div class="col-sm-3">
      <input class="form-control input-sm" type="text" name="bill_quota">
    </div>
    <div class="col-sm-3">
      <select name="bill_quota_type" class="form-control input-sm">
        <option value="MB">Megabytes (MB)</option>
        <option value="GB" selected>Gigabytes (GB)</option>
        <option value="TB">Terabytes (TB)</option>
      </select>
    </div>
    <div class="col-sm-4">
    </div>
  </div>
</div>
<div class="form-group">
  <label class="col-sm-2 control-label" for="bill_day"><strong>Billing Day</strong></label>
  <div class="col-sm-1">
    <select name="bill_day" class="form-control input-sm">
<?php
    for ($x = 1; $x < 32; $x++) {
        echo '          <option value="'.$x.'">'.$x."</option>\n";
    }

?>
    </select>
  </div>
  <div class="col-sm-9">
  </div>
</div>
<h3>Optional Information</h3>
<div class="form-group">
  <label class="col-sm-2 control-label" for="bill_custid"><strong>Customer Reference</strong></label>
  <div class="col-sm-4">
    <input class="form-control input-sm" type="text" name="bill_custid">
  </div>
  <div class="col-sm-6">
  </div>
</div>
<div class="form-group">
  <label class="col-sm-2 control-label" for="bill_ref"><strong>Billing Reference</strong></label>
  <div class="col-sm-4">
    <input class="form-control input-sm" type="text" name="bill_ref" value="<?php echo $port['port_descr_circuit']; ?>">
  </div>
  <div class="col-sm-6">
  </div>
</div>
<div class="form-group">
  <label class="col-sm-2 control-label" for="bill_notes"><strong>Notes</strong></label>
  <div class="col-sm-4">
    <input class="form-control input-sm" type="textarea" name="bill_notes" value="<?php echo $port['port_descr_speed']; ?>">
  </div>
  <div class="col-sm-6">
  </div>
</div>
<button type="submit" class="btn btn-success"><i class="fa fa-check"></i> <strong>Add Bill</strong></button>
</form>

<?php
}
else {
    include 'pages/bills/search.inc.php';

    $i = 0;
    echo "<table border='0' cellspacing='0' cellpadding='5' class='table table-condensed'>
        <tr>
        <th>Billing name</th>
        <th></th>
        <th>Type</th>
        <th>Allowed</th>
        <th>Used</th>
        <th>Overusage</th>
        <th></th>
        <th></th>
        </tr>";
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

            if (!is_integer($i / 2)) {
                $row_colour = $list_colour_a;
            }
            else {
                $row_colour = $list_colour_b;
            }

            echo "
                <tr bgcolor='$row_colour'>
                <td><a href='".generate_url(array('page' => 'bill', 'bill_id' => $bill['bill_id']))."'><span style='font-weight: bold;' class=interface>".$bill['bill_name'].'</span></a><br />'.strftime('%F', strtotime($datefrom)).' to '.strftime('%F', strtotime($dateto))."</td>
                <td>$notes</td>
                <td>$type</td>
                <td>$allowed</td>
                <td>$used</td>
                <td style=\"text-align: center;\">$overuse</td>
                <td>".print_percentage_bar(250, 20, $percent, null, 'ffffff', $background['left'], $percent.'%', 'ffffff', $background['right'])."</td>
                <td><a href='".generate_url(array('page' => 'bill', 'bill_id' => $bill['bill_id'], 'view' => 'edit'))."'><img src='images/16/wrench.png' align=absmiddle alt='Edit'> Edit</a></td>
                </tr>
                ";

            $i++;
        }
    }
    
    echo '</table>';
}
