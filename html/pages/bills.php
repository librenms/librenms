<?php

echo("<table cellpadding=7 cellspacing=0 class=devicetable width=100%><tr><td>");

if($_GET['bill']) {

 $bill_id = $_GET['bill'];
 include("includes/billing.php");

} else {

  $sql  = "SELECT * FROM `bills` ORDER BY `bill_name`";
  $query = mysql_query($sql);
  if($bg == "#ffffff") { $bg = "#e5e5e5"; } else { $bg="#ffffff"; }
  echo("<table border=0 cellspacing=0 cellpadding=2 class=devicetable width=100%>");
  while($bill = mysql_fetch_array($query)) {
    unset($class);
    $day_data     = getDates($bill['bill_day']);
    $datefrom     = $day_data['0'];
    $dateto       = $day_data['1'];
    $rate_data    = getRates($bill['bill_id'],$datefrom,$dateto);
    $rate_95th    = $rate_data['rate_95th'];
    $dir_95th     = $rate_data['dir_95th'];
    $total_data   = $rate_data['total_data'];
    $rate_average = $rate_data['rate_average'];

    if($bill['bill_type'] == "cdr") {
       $type = "CDR"; 
       $allowed = formatRates($bill['bill_cdr'] * 1000);
       $used    = formatRates($rate_data['rate_95th'] * 1000);
       $percent = round(($rate_data['rate_95th'] / $bill['bill_cdr']) * 100,2);
    } elseif ($bill['bill_type'] == "quota") { 
       $type = "Quota"; 
       $allowed = formatStorage($bill['bill_gb']* 1024 * 1024 * 1024);
       $used    = formatStorage($rate_data['total_data'] * 1024 * 1024);
       $percent = round(($rate_data['total_data'] / ($bill['bill_gb'] * 1024)) * 100,2);
    }

    echo("
           <tr bgcolor='$bg'>
             <td width='7'></td>
             <td width='250'><a href='/bill/".$bill['bill_id']."'><span style='font-weight: bold;' class=interface>".$bill['bill_name']."</span></a></td>
             <td>$notes</td>
	     <td>$type</td>
             <td>$allowed</td>
             <td>$used</td>
             <td><img src='percentage.php?width=350&per=$percent'> $percent%</td>
           </tr>
         ");
  }
  echo("</table>");

}

echo("</td></tr></table>");

?>
