<?php

  $pagetitle[] = "Historical Usage";
  $i=0;

  echo("<table border=0 cellspacing=0 cellpadding=5 class=devicetable width=100%>
           <tr style=\"font-weight: bold; \">
             <td width=\"7\"></td>
             <td width=\"220\">Period</td>
             <td>Type</td>
             <td>Allowed</td>
             <td>Inbound</td>
             <td>Outbound</td>
             <td>Total</td>
             <td>95 percentile</td>
             <td style=\"text-align: center;\">Overusage</td>
             <td width=\"250\"></td>
           </tr>");

  foreach (dbFetchRows("SELECT * FROM `bill_history` WHERE `bill_id` = ? ORDER BY `bill_datefrom` AND `bill_dateto` DESC LIMIT 24", array($bill_id)) as $history)
  {
    if (bill_permitted($history['bill_id']))
    {
      unset($class);
      $datefrom       = $history['bill_datefrom'];
      $dateto         = $history['bill_dateto'];
      $type           = $history['bill_type'];
      $percent        = $history['bill_percent'];
      $dir_95th       = $history['dir_95th'];
      $rate_95th      = formatRates($history['rate_95th'] * 1000);
      $total_data     = formatStorage($history['traf_total'] * 1024 * 1024);

      $background = get_percentage_colours($percent);
      $row_colour = ((!is_integer($i/2)) ? $list_colour_a : $list_colour_b);

      if ($type == "CDR")
      {
         $allowed = formatRates($history['bill_allowed'] * 1000);
         $used    = formatRates($history['rate_95th'] * 1000);
         $in      = formatRates($history['rate_95th_in'] * 1000);
         $out     = formatRates($history['rate_95th_out'] * 1000);
         $overuse = (($history['bill_overuse'] <= 0) ? "-" : "<span style=\"color: #".$background['left']."; font-weight: bold;\">".formatRates($history['bill_overuse'] * 1000)."</span>");
      } elseif ($type == "Quota") {
         $allowed = formatStorage($history['bill_allowed'] * 1024 * 1024);
         $used    = formatStorage($history['total_data'] * 1024 * 1024);
         $in      = formatStorage($history['traf_in'] * 1024 * 1024);
         $out     = formatStorage($history['traf_out'] * 1024 * 1024);
         $overuse = (($history['bill_overuse'] <= 0) ? "-" : "<span style=\"color: #".$background['left']."; font-weight: bold;\">".formatStorage($history['bill_overuse'] * 1024 * 1024)."</span>");
      }

      $total_data     = (($type == "Quota") ? "<b>".$total_data."</b>" : $total_data);
      $rate_95th      = (($type == "CDR") ? "<b>".$rate_95th."</b>" : $rate_95th);

      echo("
           <tr style=\"background: $row_colour;\">
             <td></td>
             <td><span style=\"font-weight: bold;\" class=\"interface\">from ".strftime("%x", strtotime($datefrom))." to ".strftime("%x", strtotime($dateto))."</span></td>
             <td>$type</td>
             <td>$allowed</td>
             <td>$in</td>
             <td>$out</td>
             <td>$total_data</td>
             <td>$rate_95th</td>
             <td style=\"text-align: center;\">$overuse</td>
             <td>".print_percentage_bar (250, 20, $perc, NULL, "ffffff", $background['left'], $percent . "%", "ffffff", $background['right'])."</td>
           </tr>");

      $i++;
    } ### PERMITTED
  }
  echo("</table>");

?>
