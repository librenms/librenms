<?php

  $pagetitle[] = "Historical Usage";
  $detail      = (isset($_GET['detail']) ? $_GET['detail'] : "");
  $url         = $PHP_SELF."/bill/".$bill_id."/history/";
  $i           = 0;

  $gbconvert   = $config['billing']['base'];

  $img['his']  = "<img src=\"bandwidth-graph.php?bill_id=".$bill_id;
  $img['his'] .= "&amp;type=historical";
  $img['his'] .= "&amp;x=1190&amp;y=250";
  $img['his'] .= "\" style=\"margin: 15px 5px 25px 5px;\" />";

  echo($img['his']);

  function showDetails($id, $imgtype, $from, $to, $bittype = "Quota")
  {
    if ($imgtype == "bitrate") {
      $res  = "<img src=\"billing-graph.php?bill_id=".$id;
      if ($bittype == "Quota")
      {
        $res .= "&amp;ave=yes";
      }
      elseif ($bittype == "CDR") {
        $res .= "&amp;95th=yes";
      }
    } else
    {
      $res  = "<img src=\"bandwidth-graph.php?bill_id=".$id;
    }
    //$res .= "&amp;type=".$type;
    $res .= "&amp;type=".$imgtype;
    $res .= "&amp;x=1190&amp;y=250";
    $res .= "&amp;from=".$from."&amp;to=".$to;
    $res .= "\" style=\"margin: 15px 5px 25px 5px;\" />";
    return $res;
  }

  echo("<table border=0 cellspacing=0 cellpadding=5 class=devicetable width=100%>
           <tr style=\"font-weight: bold; \">
             <td width=\"7\"></td>
             <td width=\"250\">Period</td>
             <td>Type</td>
             <td>Allowed</td>
             <td>Inbound</td>
             <td>Outbound</td>
             <td>Total</td>
             <td>95 percentile</td>
             <td style=\"text-align: center;\">Overusage</td>
             <td colspan=\"2\" style=\"text-align: right;\"><a href=\"".$url."?detail=all\"><img src=\"images/16/chart_curve.png\" border=\"0\" align=\"absmiddle\" alt=\"Show details\" title=\"Show details\" /> Show all details</a></td>
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
      $total_data     = formatStorage($history['traf_total'] * $gbconvert * $gbconvert);

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
         $allowed = formatStorage($history['bill_allowed'] * $gbconvert * $gbconvert);
         $used    = formatStorage($history['total_data'] * $gbconvert * $gbconvert);
         $in      = formatStorage($history['traf_in'] * $gbconvert * $gbconvert);
         $out     = formatStorage($history['traf_out'] * $gbconvert * $gbconvert);
         $overuse = (($history['bill_overuse'] <= 0) ? "-" : "<span style=\"color: #".$background['left']."; font-weight: bold;\">".formatStorage($history['bill_overuse'] * $gbconvert * $gbconvert)."</span>");
      }

      $total_data     = (($type == "Quota") ? "<b>".$total_data."</b>" : $total_data);
      $rate_95th      = (($type == "CDR") ? "<b>".$rate_95th."</b>" : $rate_95th);

      echo("
           <tr style=\"background: ".$row_colour.";\">
             <td></td>
             <td><span style=\"font-weight: bold;\" class=\"interface\">".strftime("%Y-%m-%d", strtotime($datefrom))." -> ".strftime("%Y-%m-%d", strtotime($dateto))."</span></td>
             <td>$type</td>
             <td>$allowed</td>
             <td>$in</td>
             <td>$out</td>
             <td>$total_data</td>
             <td>$rate_95th</td>
             <td style=\"text-align: center;\">$overuse</td>
             <td width=\"250\">".print_percentage_bar(250, 20, $perc, NULL, "ffffff", $background['left'], $percent."%", "ffffff", $background['right'])."</td>
             <td>
                <a href=\"".$url."?detail=".($i+1)."\"><img src=\"images/16/chart_curve.png\" border=\"0\" align=\"absmiddle\" alt=\"Show details\" title=\"Show details\"/></a>
                <a href=\"#\"><img src=\"images/16/page_white_acrobat.png\" border=\"0\" align=\"absmiddle\" alt=\"PDF Report\" title=\"PDF Report\"/></a>
             </td>
           </tr>");

      if ($detail == ($i+1) || $detail == "all") {
        $img['bitrate'] = showDetails($history['bill_id'], "bitrate", strtotime($datefrom), strtotime($dateto), $type);
        $img['bw_day']  = showDetails($history['bill_id'], "day", strtotime($datefrom), strtotime($dateto));
        $img['bw_hour'] = showDetails($history['bill_id'], "hour", strtotime($datefrom), strtotime($dateto));
        echo("
           <tr style=\"background: #fff; border-top: 1px solid ".$row_colour."; border-bottom: 1px solid #ccc;\">
             <td colspan=\"11\">
               <!-- <b>Accuate Graph</b><br /> //-->
               ".$img['bitrate']."<br />
               <!-- <b>Bandwidth Graph per day</b><br /> //-->
               ".$img['bw_day']."<br />
               <!-- <b>Bandwidth Graph per hour</b><br /> //-->
               ".$img['bw_hour']."
             </td>
           </tr>");
      }

      $i++;
    } ### PERMITTED
  }
  echo("</table>");

?>
