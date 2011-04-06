<?php

$bill_id = mres($_GET['opta']);

if ($_SESSION['userlevel'] == "10")
{
  include("pages/bill/actions.inc.php");
}

if (bill_permitted($bill_id))
{
  $bi_q = mysql_query("SELECT * FROM bills WHERE bill_id = $bill_id");
  $bill_data = mysql_fetch_assoc($bi_q);

  $today = str_replace("-", "", mysql_result(mysql_query("SELECT CURDATE()"), 0));
  $yesterday = str_replace("-", "", mysql_result(mysql_query("SELECT DATE_SUB(CURDATE(), INTERVAL 1 DAY)"), 0));
  $tomorrow = str_replace("-", "", mysql_result(mysql_query("SELECT DATE_ADD(CURDATE(), INTERVAL 1 DAY)"), 0));
  $last_month = str_replace("-", "", mysql_result(mysql_query("SELECT DATE_SUB(CURDATE(), INTERVAL 1 MONTH)"), 0));

  $rightnow = $today . date(His);
  $before = $yesterday . date(His);
  $lastmonth = $last_month . date(His);

  $bill_name  = $bill_data['bill_name'];
  $dayofmonth = $bill_data['bill_day'];
  $paidrate   = $bill_data['bill_paid_rate'];
  $paid_kb    = $paidrate / 1000;
  $paid_mb    = $paid_kb / 1000;

  if ($paidrate < 1000000) { $paidrate_text = $paid_kb . "Kbps is the CDR."; }
  if ($paidrate >= 1000000) { $paidrate_text = $paid_mb . "Mbps is the CDR."; }

  $day_data     = getDates($dayofmonth);
  $datefrom     = $day_data['0'];
  $dateto       = $day_data['1'];
  $rate_data    = getRates($bill_id,$datefrom,$dateto);
  $rate_95th    = $rate_data['rate_95th'];
  $dir_95th     = $rate_data['dir_95th'];
  $total_data   = $rate_data['total_data'];
  $rate_average = $rate_data['rate_average'];

  if ($rate_95th > $paid_kb)
  {
    $over = $rate_95th - $paid_kb;
    $bill_text = $over . "Kbit excess.";
    $bill_color = "#cc0000";
  }
  else
  {
    $under = $paid_kb - $rate_95th;
    $bill_text = $under . "Kbit headroom.";
    $bill_color = "#0000cc";
  }

  $fromtext = mysql_result(mysql_query("SELECT DATE_FORMAT($datefrom, '%M %D %Y')"), 0);
  $totext   = mysql_result(mysql_query("SELECT DATE_FORMAT($dateto, '%M %D %Y')"), 0);
  $unixfrom = mysql_result(mysql_query("SELECT UNIX_TIMESTAMP('$datefrom')"), 0);
  $unixto = mysql_result(mysql_query("SELECT UNIX_TIMESTAMP('$dateto')"), 0);

  echo("<font face=\"Verdana, Arial, Sans-Serif\"><h2>
  Bill : " . $bill_name . "</h2>");

  print_optionbar_start();

  if (!$_GET['optb']) { $_GET['optb'] = "details"; }

  if ($_GET['optb'] == "details") { echo("<strong>"); }
  echo("<a href='bill/".$bill_id."/details/'>Details</a>");
  if ($_GET['optb'] == "details") { echo("</strong>"); }

  if ($_SESSION['userlevel'] == "10")
  {
    echo(" | ");
    if ($_GET['optb'] == "edit") { echo("<strong>"); }
    echo("<a href='bill/".$bill_id."/edit/'>Edit</a>");
    if ($_GET['optb'] == "edit") { echo("</strong>"); }

    echo(" | ");
    if ($_GET['optb'] == "delete") { echo("<strong>"); }
    echo("<a href='bill/".$bill_id."/delete/'>Delete</a>");
    if ($_GET['optb'] == "delete") { echo("</strong>"); }
  }

  print_optionbar_end();

#  echo("<table width=715 border=0 cellspace=0 cellpadding=0><tr><td>");

  if ($_GET['optb'] == "edit" && $_SESSION['userlevel'] == "10")
  {
    include("pages/bill/edit.inc.php");
  }
  elseif ($_GET['optb'] == "delete" && $_SESSION['userlevel'] == "10")
  {
    include("pages/bill/delete.inc.php");
  }
  elseif ($_GET['optb'] == "details")
  {

  echo("<h3>Billed Ports</h3>");

  $ports = mysql_query("SELECT * FROM `bill_ports` AS B, `ports` AS P, `devices` AS D
                        WHERE B.bill_id = '".$bill_id."' AND P.interface_id = B.port_id
                        AND D.device_id = P.device_id");

  while ($port = mysql_fetch_assoc($ports))
  {
    echo(generate_port_link($port) . " on " . generate_device_link($port) . "<br />");
  }

  echo("<h3>Bill Summary</h3>");

  if ($bill_data['bill_type'] == "quota")
  {
    // The Customer is billed based on a pre-paid quota

    echo("<h4>Quota Bill</h4>");

    $percent = round(($total_data / 1024) / $bill_data['bill_gb'] * 100, 2);
    $unit = "MB";
    $total_data = round($total_data, 2);
    echo("Billing Period from " . $fromtext . " to " . $totext . "
    <br />Transferred ".formatStorage($total_data * 1024 * 1024)." of ".formatStorage($bill_data['bill_gb'] * 1024 * 1024 * 1024)." (".$percent."%)
    <br />Average rate " . formatRates($rate_average * 1000));

    if ($percent > 100) { $perc = "100"; } else { $perc = $percent; }
    if ($perc > '90') { $left_background='c4323f'; $right_background='C96A73';
    } elseif ($perc > '75') { $left_background='bf5d5b'; $right_background='d39392';
    } elseif ($perc > '50') { $left_background='bf875b'; $right_background='d3ae92';
    } elseif ($perc > '25') { $left_background='5b93bf'; $right_background='92b7d3';
    } else { $left_background='9abf5b'; $right_background='bbd392'; }

    echo("<p>".print_percentage_bar (350, 20, $perc, NULL, "ffffff", $left_background, $percent . "%", "ffffff", $right_background)."</p>");

    $type="&amp;ave=yes";
  }
  elseif ($bill_data['bill_type'] == "cdr")
  {
    // The customer is billed based on a CDR with 95th%ile overage

    echo("<h4>CDR / 95th Bill</h4>");

    $unit = "kbps";
    $cdr = $bill_data['bill_cdr'];
    if ($rate_95th > "1000") { $rate_95th = $rate_95th / 1000; $cdr = $cdr / 1000; $unit = "Mbps"; }
    if ($rate_95th > "1000") { $rate_95th = $rate_95th / 1000; $cdr = $cdr / 1000; $unit = "Gps"; }
    $rate_95th = round($rate_95th, 2);

    $percent = round(($rate_95th) / $cdr * 100, 2);

    $type="&amp;95th=yes";

    echo("<strong>" . $fromtext . " to " . $totext . "</strong>
    <br />Measured ".$rate_95th."$unit of ".$cdr."$unit (".$percent."%)");

   if ($percent > 100) { $perc = "100"; } else { $perc = $percent; }
    if ($perc > '90') { $left_background='c4323f'; $right_background='C96A73';
    } elseif ($perc > '75') { $left_background='bf5d5b'; $right_background='d39392';
    } elseif ($perc > '50') { $left_background='bf875b'; $right_background='d3ae92';
    } elseif ($perc > '25') { $left_background='5b93bf'; $right_background='92b7d3';
    } else { $left_background='9abf5b'; $right_background='bbd392'; }

    echo("<p>".print_percentage_bar (350, 20, $perc, NULL, "ffffff", $left_background, $percent . "%", "ffffff", $right_background)."</p>");

  #  echo("<p>Billing Period : " . $fromtext . " to " . $totext . "<br />
  #  " . $paidrate_text . " <br />
  #  " . $total_data . "MB transfered in the current billing cycle. <br />
  #  " . $rate_average . "Kbps Average during the current billing cycle. </p>
  #  <font face=\"Trebuchet MS, Verdana, Arial, Sans-Serif\" color=" . $bill_color . "><B>" . $rate_95th . "Kbps @ 95th Percentile.</b> (" . $dir_95th . ") (" . $bill_text . ")</font>
  #  </td><td><img src=\"images/billing-key.png\"></td></tr></table>
  #  <br />");

  }

#  echo("</td><td><img src='images/billing-key.png'></td></tr></table>");

#  $bi =       "<img src='billing-graph.php?bill_id=" . $bill_id . "&amp;bill_code=" . $_GET['bill_code'];
#  $bi = $bi . "&amp;from=" . $unixfrom .  "&amp;to=" . $unixto;
#  $bi = $bi . "&amp;x=715&amp;y=250";
#  $bi = $bi . "$type'>";

  $bi = "<img src='graph.php?type=bill_bits&amp;id=" . $bill_id;
  $bi .= "&amp;from=" . $unixfrom .  "&amp;to=" . $unixto;
  $bi .= "&amp;width=715&amp;height=200&amp;total=1'>";

  $lastmonth = mysql_result(mysql_query("SELECT UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 MONTH))"), 0);
  $yesterday = mysql_result(mysql_query("SELECT UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 DAY))"), 0);
  $rightnow = date(U);

#  $di =       "<img src='billing-graph.php?bill_id=" . $bill_id . "&amp;bill_code=" . $_GET['bill_code'];
#  $di = $di . "&amp;from=" . $yesterday .  "&amp;to=" . $rightnow . "&amp;x=715&amp;y=250";
#  $di = $di . "$type'>";

  $di = "<img src='graph.php?type=bill_bits&amp;id=" . $bill_id;
  $di .= "&amp;from=" . $config['day'] .  "&amp;to=" . $config['now'];
  $di .= "&amp;width=715&amp;height=200&amp;total=1'>";


#  $mi =       "<img src='billing-graph.php?bill_id=" . $bill_id . "&amp;bill_code=" . $_GET['bill_code'];
#  $mi = $mi . "&amp;from=" . $lastmonth .  "&amp;to=" . $rightnow . "&amp;x=715&amp;y=250";
#  $mi = $mi . "$type'>";

  $mi = "<img src='graph.php?type=bill_bits&amp;id=" . $bill_id;
  $mi .= "&amp;from=" . $lastmonth .  "&amp;to=" . $rightnow;
  $mi .= "&amp;width=715&amp;height=200&amp;total=1'>";


  if ($null)
  {
    echo("
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

  ");

  }

  if ($_GET['all'])
  {
    $ai = "<img src=\"billing-graph.php?bill_id=" . $bill_id . "&amp;bill_code=" . $_GET['bill_code'];
    $ai .= "&amp;from=0&amp;to=" . $rightnow;
    $ai .= "&amp;x=715&amp;y=250";
    $ai .= "&amp;count=60\">";
    echo("<h3>Entire Data View</h3>$ai");
   }
   elseif ($_GET['custom'])
   {
    $cg = "<img src=\"billing-graph.php?bill_id=" . $bill_id . "&amp;bill_code=" . $_GET['bill_code'];
    $cg .= "&amp;from=" . $_GET['fromdate'] . "000000&amp;to=" . $_GET['todate'] . "235959";
    $cg .= "&amp;x=715&amp;y=250";
    $cg .= "&amp;count=60\">";
    echo("<h3>Custom Graph</h3>$cg");
   }
   else
   {
     echo("<h3>Billing View</h3>$bi<h3>24 Hour View</h3>$di");
     echo("<h3>Monthly View</h3>$mi");
#     echo("<br /><a href=\"rate.php?" . $_SERVER['QUERY_STRING'] . "&amp;all=yes\">Graph All Data (SLOW)</a>");
   }
  } # End if details
}
else
{
  include("includes/error-no-perm.inc.php");
}

?>