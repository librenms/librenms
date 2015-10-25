<?php

$bill_id = mres($vars['bill_id']);

if ($_SESSION['userlevel'] >= '10') {
    include 'pages/bill/actions.inc.php';
}

if (bill_permitted($bill_id)) {
    $bill_data = dbFetchRow('SELECT * FROM bills WHERE bill_id = ?', array($bill_id));

    $bill_name = $bill_data['bill_name'];

    $today      = str_replace('-', '', dbFetchCell('SELECT CURDATE()'));
    $yesterday  = str_replace('-', '', dbFetchCell('SELECT DATE_SUB(CURDATE(), INTERVAL 1 DAY)'));
    $tomorrow   = str_replace('-', '', dbFetchCell('SELECT DATE_ADD(CURDATE(), INTERVAL 1 DAY)'));
    $last_month = str_replace('-', '', dbFetchCell('SELECT DATE_SUB(CURDATE(), INTERVAL 1 MONTH)'));

    $rightnow  = $today.date(His);
    $before    = $yesterday.date(His);
    $lastmonth = $last_month.date(His);

    $bill_name  = $bill_data['bill_name'];
    $dayofmonth = $bill_data['bill_day'];

    $day_data = getDates($dayofmonth);

    $datefrom = $day_data['0'];
    $dateto   = $day_data['1'];
    $lastfrom = $day_data['2'];
    $lastto   = $day_data['3'];

    $rate_95th    = $bill_data['rate_95th'];
    $dir_95th     = $bill_data['dir_95th'];
    $total_data   = $bill_data['total_data'];
    $rate_average = $bill_data['rate_average'];

    if ($rate_95th > $paid_kb) {
        $over       = ($rate_95th - $paid_kb);
        $bill_text  = $over.'Kbit excess.';
        $bill_color = '#cc0000';
    }
    else {
        $under      = ($paid_kb - $rate_95th);
        $bill_text  = $under.'Kbit headroom.';
        $bill_color = '#0000cc';
    }

    $fromtext = dbFetchCell("SELECT DATE_FORMAT($datefrom, '".$config['dateformat']['mysql']['date']."')");
    $totext   = dbFetchCell("SELECT DATE_FORMAT($dateto, '".$config['dateformat']['mysql']['date']."')");
    $unixfrom = dbFetchCell("SELECT UNIX_TIMESTAMP('$datefrom')");
    $unixto   = dbFetchCell("SELECT UNIX_TIMESTAMP('$dateto')");

    $unix_prev_from = dbFetchCell("SELECT UNIX_TIMESTAMP('$lastfrom')");
    $unix_prev_to   = dbFetchCell("SELECT UNIX_TIMESTAMP('$lastto')");
    // Speeds up loading for other included pages by setting it before progessing of mysql data!
    $ports = dbFetchRows(
        'SELECT * FROM `bill_ports` AS B, `ports` AS P, `devices` AS D
        WHERE B.bill_id = ? AND P.port_id = B.port_id
        AND D.device_id = P.device_id',
        array($bill_id)
    );

    echo '<font face="Verdana, Arial, Sans-Serif"><h2>
        Bill : '.$bill_data['bill_name'].'</h2>';

    print_optionbar_start();

    echo "<span style='font-weight: bold;'>Bill</span> &#187; ";

    if (!$vars['view']) {
        $vars['view'] = 'quick';
    }

    if ($vars['view'] == 'quick') {
        echo "<span class='pagemenu-selected'>";
    }

    echo '<a href="'.generate_url($vars, array('view' => 'quick')).'">Quick Graphs</a>';
    if ($vars['view'] == 'quick') {
        echo '</span>';
    }

    echo ' | ';

    if ($vars['view'] == 'accurate') {
        echo "<span class='pagemenu-selected'>";
    }

    echo '<a href="'.generate_url($vars, array('view' => 'accurate')).'">Accurate Graphs</a>';
    if ($vars['view'] == 'accurate') {
        echo '</span>';
    }

    echo ' | ';

    if ($vars['view'] == 'transfer') {
        echo "<span class='pagemenu-selected'>";
    }

    echo '<A href="'.generate_url($vars, array('view' => 'transfer')).'">Transfer Graphs</a>';
    if ($vars['view'] == 'transfer') {
        echo '</span>';
    }

    echo ' | ';

    if ($vars['view'] == 'history') {
        echo "<span class='pagemenu-selected'>";
    }

    echo '<A href="'.generate_url($vars, array('view' => 'history')).'">Historical Usage</a>';
    if ($vars['view'] == 'history') {
        echo '</span>';
    }

    if ($_SESSION['userlevel'] >= '10') {
        echo ' | ';
        if ($vars['view'] == 'edit') {
            echo "<span class='pagemenu-selected'>";
        }

        echo '<A href="'.generate_url($vars, array('view' => 'edit')).'">Edit</a>';
        if ($vars['view'] == 'edit') {
            echo '</span>';
        }

        echo ' | ';
        if ($vars['view'] == 'delete') {
            echo "<span class='pagemenu-selected'>";
        }

        echo '<A href="'.generate_url($vars, array('view' => 'delete')).'">Delete</a>';
        if ($vars['view'] == 'delete') {
            echo '</span>';
        }

        echo ' | ';
        if ($vars['view'] == 'reset') {
            echo "<span class='pagemenu-selected'>";
        }

        echo '<A href="'.generate_url($vars, array('view' => 'reset')).'">Reset</a>';
        if ($vars['view'] == 'reset') {
            echo '</span>';
        }
    }//end if

    echo '<div style="font-weight: bold; float: right;"><a href="'.generate_url(array('page' => 'bills')).'/"><img align=absmiddle src="images/16/arrow_left.png"> Back to Bills</a></div>';

    print_optionbar_end();

    if ($vars['view'] == 'edit' && $_SESSION['userlevel'] >= '10') {
        include 'pages/bill/edit.inc.php';
    }
    else if ($vars['view'] == 'delete' && $_SESSION['userlevel'] >= '10') {
        include 'pages/bill/delete.inc.php';
    }
    else if ($vars['view'] == 'reset' && $_SESSION['userlevel'] >= '10') {
        include 'pages/bill/reset.inc.php';
    }
    else if ($vars['view'] == 'history') {
        include 'pages/bill/history.inc.php';
    }
    else if ($vars['view'] == 'transfer') {
        include 'pages/bill/transfer.inc.php';
    }
    else if ($vars['view'] == 'quick' || $vars['view'] == 'accurate') {
        echo '<h3>Billed Ports</h3>';

        // Collected Earlier
        foreach ($ports as $port) {
            echo generate_port_link($port).' on '.generate_device_link($port).'<br />';
        }

        echo '<h3>Bill Summary</h3>';

        if ($bill_data['bill_type'] == 'quota') {
            // The Customer is billed based on a pre-paid quota with overage in xB
            echo '<h4>Quota Bill</h4>';

            $percent    = round((($total_data) / $bill_data['bill_quota'] * 100), 2);
            $unit       = 'MB';
            $total_data = round($total_data, 2);
            echo 'Billing Period from '.$fromtext.' to '.$totext;
            echo '<br />Transferred '.format_bytes_billing($total_data).' of '.format_bytes_billing($bill_data['bill_quota']).' ('.$percent.'%)';
            echo '<br />Average rate '.formatRates($rate_average);

            $background = get_percentage_colours($percent);

            echo '<p>'.print_percentage_bar(350, 20, $percent, null, 'ffffff', $background['left'], $percent.'%', 'ffffff', $background['right']).'</p>';

            $type = '&amp;ave=yes';
        }
        else if ($bill_data['bill_type'] == 'cdr') {
            // The customer is billed based on a CDR with 95th%ile overage
            echo '<h4>CDR / 95th Bill</h4>';

            $unit      = 'kbps';
            $cdr       = $bill_data['bill_cdr'];
            $rate_95th = round($rate_95th, 2);

            $percent = round((($rate_95th) / $cdr * 100), 2);

            $type = '&amp;95th=yes';

            echo '<strong>'.$fromtext.' to '.$totext.'</strong>
                <br />Measured '.format_si($rate_95th).'bps of '.format_si($cdr).'bps ('.$percent.'%) @ 95th %ile';

            $background = get_percentage_colours($percent);

            echo '<p>'.print_percentage_bar(350, 20, $percent, null, 'ffffff', $background['left'], $percent.'%', 'ffffff', $background['right']).'</p>';

            // echo("<p>Billing Period : " . $fromtext . " to " . $totext . "<br />
            // " . $paidrate_text . " <br />
            // " . $total_data . "MB transfered in the current billing cycle. <br />
            // " . $rate_average . "Kbps Average during the current billing cycle. </p>
            // <font face=\"Trebuchet MS, Verdana, Arial, Sans-Serif\" color=" . $bill_color . "><B>" . $rate_95th . "Kbps @ 95th Percentile.</b> (" . $dir_95th . ") (" . $bill_text . ")</font>
            // </td><td><img src=\"images/billing-key.png\"></td></tr></table>
            // <br />");
        }//end if

        $lastmonth = dbFetchCell('SELECT UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 MONTH))');
        $yesterday = dbFetchCell('SELECT UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 DAY))');
        $rightnow  = date(U);

        if ($vars['view'] == 'accurate') {
            $bi  = "<img src='billing-graph.php?bill_id=".$bill_id.'&amp;bill_code='.$_GET['bill_code'];
            $bi .= '&amp;from='.$unixfrom.'&amp;to='.$unixto;
            // $bi .= "&amp;x=800&amp;y=250";
            $bi .= '&amp;x=1190&amp;y=250';
            $bi .= "$type'>";

            $li  = "<img src='billing-graph.php?bill_id=".$bill_id.'&amp;bill_code='.$_GET['bill_code'];
            $li .= '&amp;from='.$unix_prev_from.'&amp;to='.$unix_prev_to;
            // $li .= "&amp;x=800&amp;y=250";
            $li .= '&amp;x=1190&amp;y=250';
            $li .= "$type'>";

            $di  = "<img src='billing-graph.php?bill_id=".$bill_id.'&amp;bill_code='.$_GET['bill_code'];
            $di .= '&amp;from='.$config['time']['day'].'&amp;to='.$config['time']['now'];
            // $di .= "&amp;x=800&amp;y=250";
            $di .= '&amp;x=1190&amp;y=250';
            $di .= "$type'>";

            $mi  = "<img src='billing-graph.php?bill_id=".$bill_id.'&amp;bill_code='.$_GET['bill_code'];
            $mi .= '&amp;from='.$lastmonth.'&amp;to='.$rightnow;
            // $mi .= "&amp;x=800&amp;y=250";
            $mi .= '&amp;x=1190&amp;y=250';
            $mi .= "$type'>";
        }
        else {
            $bi  = "<img src='graph.php?type=bill_bits&amp;id=".$bill_id;
            $bi .= '&amp;from='.$unixfrom.'&amp;to='.$unixto;
            $bi .= "&amp;width=1000&amp;height=200&amp;total=1'>";

            $li  = "<img src='graph.php?type=bill_bits&amp;id=".$bill_id;
            $li .= '&amp;from='.$unix_prev_from.'&amp;to='.$unix_prev_to;
            $li .= "&amp;width=1000&amp;height=200&amp;total=1'>";

            $di  = "<img src='graph.php?type=bill_bits&amp;id=".$bill_id;
            $di .= '&amp;from='.$config['time']['day'].'&amp;to='.$config['time']['now'];
            $di .= "&amp;width=1000&amp;height=200&amp;total=1'>";

            $mi  = "<img src='graph.php?type=bill_bits&amp;id=".$bill_id;
            $mi .= '&amp;from='.$lastmonth.'&amp;to='.$rightnow;
            $mi .= "&amp;width=1000&amp;height=200&amp;total=1'>";
        }//end if

        if ($null) {
            echo "
                <script type='text/javascript' src='js/calendarDateInput.js'>
  </script>

  <FORM action='/' method='get'>
    <INPUT type='hidden' name='bill' value='".$_GET['bill']."'>
    <INPUT type='hidden' name='code' value='".$_GET['code']."'>
    <INPUT type='hidden' name='page' value='bills'>
    <INPUT type='hidden' name='custom' value='yes'>

    From:
    <script>DateInput('fromdate', true, 'YYYYMMDD')</script>

    To:
    <script>DateInput('todate', true, 'YYYYMMDD')</script>
    <INPUT type='submit' value='Generate Graph'>

  </FORM>

  ";
        }//end if

        if ($_GET['all']) {
            $ai  = '<img src="billing-graph.php?bill_id='.$bill_id.'&amp;bill_code='.$_GET['bill_code'];
            $ai .= '&amp;from=0&amp;to='.$rightnow;
            $ai .= '&amp;x=715&amp;y=250';
            $ai .= '&amp;count=60">';
            echo "<h3>Entire Data View</h3>$ai";
        }
        else if ($_GET['custom']) {
            $cg  = '<img src="billing-graph.php?bill_id='.$bill_id.'&amp;bill_code='.$_GET['bill_code'];
            $cg .= '&amp;from='.$_GET['fromdate'].'000000&amp;to='.$_GET['todate'].'235959';
            $cg .= '&amp;x=715&amp;y=250';
            $cg .= '&amp;count=60">';
            echo "<h3>Custom Graph</h3>$cg";
        }
        else {
            echo "<h3>Billing View</h3>$bi";
            // echo("<h3>Previous Bill View</h3>$li");
            echo "<h3>24 Hour View</h3>$di";
            echo "<h3>Monthly View</h3>$mi";
            // echo("<br /><a href=\"rate.php?" . $_SERVER['QUERY_STRING'] . "&amp;all=yes\">Graph All Data (SLOW)</a>");
        }//end if
    } //end if
}
else {
    include 'includes/error-no-perm.inc.php';
}//end if
