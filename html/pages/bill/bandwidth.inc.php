<?php

  $pagetitle[]        = "Bandwidth Graphs";

  $bill_data          = dbFetchRow("SELECT * FROM bills WHERE bill_id = ?", array($bill_id));
  $gbconvert          = $config['billing']['base'];

  $today              = str_replace("-", "", dbFetchCell("SELECT CURDATE()"));
  $tomorrow           = str_replace("-", "", dbFetchCell("SELECT DATE_ADD(CURDATE(), INTERVAL 1 DAY)"));
  $last_month         = str_replace("-", "", dbFetchCell("SELECT DATE_SUB(CURDATE(), INTERVAL 1 MONTH)"));

  $rightnow           = $today . date(His);
  $before             = $yesterday . date(His);
  $lastmonth          = $last_month . date(His);

  $dayofmonth         = $bill_data['bill_day'];
  $day_data           = getDates($dayofmonth);
  $datefrom           = $day_data['0'];
  $dateto             = $day_data['1'];
  $lastfrom           = $day_data['2'];
  $lastto             = $day_data['3'];

  $total_data         = $bill_data['total_data'];
  $in_data            = $bill_data['total_data_in'];
  $out_data           = $bill_data['total_data_out'];

  $fromtext           = dbFetchCell("SELECT DATE_FORMAT($datefrom, '%M %D %Y')");
  $totext             = dbFetchCell("SELECT DATE_FORMAT($dateto, '%M %D %Y')");
  $unixfrom           = dbFetchCell("SELECT UNIX_TIMESTAMP('$datefrom')");
  $unixto             = dbFetchCell("SELECT UNIX_TIMESTAMP('$dateto')");
  $unix_prev_from     = dbFetchCell("SELECT UNIX_TIMESTAMP('$lastfrom')");
  $unix_prev_to       = dbFetchCell("SELECT UNIX_TIMESTAMP('$lastto')");
  $lastmonth          = dbFetchCell("SELECT UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 MONTH))");
  $yesterday          = dbFetchCell("SELECT UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 DAY))");
  $rightnow           = date(U);

  echo("<h3>Billed Ports</h3>");

  $ports = dbFetchRows("SELECT * FROM `bill_ports` AS B, `ports` AS P, `devices` AS D
                        WHERE B.bill_id = ? AND P.interface_id = B.port_id
                        AND D.device_id = P.device_id", array($bill_id));

  foreach ($ports as $port)
  {
    echo(generate_port_link($port) . " on " . generate_device_link($port) . "<br />");
  }

  $cur_days           = date('d', (strtotime("now") - strtotime($datefrom)));
  $total_days         = date('d', (strtotime($dateto) - strtotime($datefrom)));

  $total['data']      = formatStorage($bill_data['total_data'] * $gbconvert * $gbconvert);
  if ($bill_data['bill_type'] == "quota") {
    $total['allow']   = formatStorage($bill_data['bill_gb'] * $gbconvert * $gbconvert * $gbconvert);
  } else {
    $total['allow']   = "-";
  }
  $total['ave']       = formatStorage(($bill_data['total_data'] / $cur_days) * $gbconvert * $gbconvert);
  $total['est']       = formatStorage(($bill_data['total_data'] / $cur_days * $total_days) * $gbconvert * $gbconvert);
  $total['per']       = round((($bill_data['total_data'] / $gbconvert) / $bill_data['bill_gb'] * 100), 2);
  $total['bg']        = get_percentage_colours($total['per']);

  $in['data']         = formatStorage($bill_data['total_data_in'] * $gbconvert * $gbconvert);
  $in['allow']        = $total['data'];
  $in['ave']          = formatStorage(($bill_data['total_data_in'] / $cur_days) * $gbconvert * $gbconvert);
  $in['est']          = formatStorage(($bill_data['total_data_in'] / $cur_days * $total_days) * $gbconvert * $gbconvert);
  $in['per']          = round(($bill_data['total_data_in'] / $bill_data['total_data'] * 100), 2);
  $in['bg']           = get_percentage_colours($in['per']);

  $out['data']        = formatStorage($bill_data['total_data_out'] * $gbconvert * $gbconvert);
  $out['allow']       = $total['data'];
  $out['ave']         = formatStorage(($bill_data['total_data_out'] / $cur_days) * $gbconvert * $gbconvert);
  $out['est']         = formatStorage(($bill_data['total_data_out'] / $cur_days * $total_days) * $gbconvert * $gbconvert);
  $out['per']         = round(($bill_data['total_data_out'] / $bill_data['total_data'] * 100), 2);
  $out['bg']          = get_percentage_colours($out['per']);

  $ousage['over']     = $bill_data['total_data'] - ($bill_data['bill_gb'] * $gbconvert);
  $ousage['over']     = (($ousage['over'] < 0) ? "0" : $ousage['over']);
  $ousage['data']     = formatStorage($ousage['over'] * $gbconvert * $gbconvert);
  $ousage['allow']    = $total['allow'];
  $ousage['ave']      = formatStorage(($ousage['over'] / $cur_days ) * $gbconvert * $gbconvert);
  $ousage['est']      = formatStorage(($ousage['over'] / $cur_days * $total_days) * $gbconvert * $gbconvert);
  $ousage['per']      = round(((($bill_data['total_data'] / $gbconvert) / $bill_data['bill_gb'] * 100) - 100), 2);
  $ousage['per']      = (($ousage['per'] < 0) ? "0" : $ousage['per']);
  $ousage['bg']       = get_percentage_colours($ousage['per']);

  function showPercent($per) {
    $background       = get_percentage_colours($per);
    $right_background = $background['right'];
    $left_background  = $background['left'];
    $res              = print_percentage_bar(350, 20, $perc, NULL, "ffffff", $left_background, $per."%", "ffffff", $right_background);
    return $res;
  }

  echo("<h3>Bill Summary</h3>");
  echo("<h4>Quota Bill</h4>");
  echo("<table cellpadding=\"5\" cellspacing=\"0\" border=\"0\" class=\"devicetable\">");
  echo("  <tr><td colspan=\"7\">Billing Period from ".$fromtext." to ".$totext."</td></tr>");
  echo("  <tr style=\"font-weight: bold;\">");
  echo("    <td width=\"125\">Bandwidth</td>");
  echo("    <td width=\"10\"></td>");
  echo("    <td width=\"100\">Used</td>");
  echo("    <td width=\"100\">Allowed</td>");
  echo("    <td width=\"100\">Average</td>");
  echo("    <td width=\"100\">Estimated</td>");
  echo("    <td width=\"360\"></td>");
  echo("  </tr>");
  echo("  <tr style=\"background: ".$list_colour_b.";\">");
  echo("    <td>Transferred</td>");
  echo("    <td>:</td>");
  echo("    <td>".$total['data']."</td>");
  echo("    <td>".$total['allow']."</td>");
  echo("    <td>".$total['ave']."</td>");
  echo("    <td>".$total['est']."</td>");
  echo("    <td width=\"360\">".showPercent($total['per'])."</td>");
  echo("  </tr>");
  echo("  <tr style=\"background: ".$list_colour_a.";\">");
  echo("    <td>Inbound</td>");
  echo("    <td>:</td>");
  echo("    <td>".$in['data']."</td>");
  echo("    <td>".$in['allow']."</td>");
  echo("    <td>".$in['ave']."</td>");
  echo("    <td>".$in['est']."</td>");
  echo("    <td>".showPercent($in['per'])."</td>");
  echo("  </tr>");
  echo("  <tr style=\"background: ".$list_colour_b.";\">");
  echo("    <td>Outbound</td>");
  echo("    <td>:</td>");
  echo("    <td>".$out['data']."</td>");
  echo("    <td>".$out['allow']."</td>");
  echo("    <td>".$out['ave']."</td>");
  echo("    <td>".$out['est']."</td>");
  echo("    <td>".showPercent($out['per'])."</td>");
  echo("  </tr>");
  if ($ousage['over'] > 0 && $bill_data['bill_type'] == "quota") {
    echo("  <tr style=\"background: ".$list_colour_a.";\">");
    echo("    <td>Already overusage</td>");
    echo("    <td>:</td>");
    echo("    <td><span style=\"color: #".$total['bg']['left']."; font-weight: bold;\">".$ousage['data']."</span></td>");
    echo("    <td>".$ousage['allow']."</td>");
    echo("    <td>".$ousage['ave']."</td>");
    echo("    <td>".$ousage['est']."</td>");
    echo("    <td>".showPercent($ousage['per'])."</td>");
  }
  echo("</table>");

  $bi  =  "<img src='bandwidth-graph.php?bill_id=" . $bill_id . "&amp;bill_code=" . $_GET['bill_code'];
  $bi .= "&amp;from=" . $unixfrom .  "&amp;to=" . $unixto;
  $bi .= "&amp;type=day&imgbill=1";
  $bi .= "&amp;x=1190&amp;y=250";
  $bi .= "$type'>";

  $li  = "<img src='bandwidth-graph.php?bill_id=" . $bill_id . "&amp;bill_code=" . $_GET['bill_code'];
  $li .= "&amp;from=" . $unix_prev_from .  "&amp;to=" . $unix_prev_to;
  $li .= "&amp;type=day";
  $li .= "&amp;x=1190&amp;y=250";
  $li .= "$type'>";

  $di  = "<img src='bandwidth-graph.php?bill_id=" . $bill_id . "&amp;bill_code=" . $_GET['bill_code'];
  $di .= "&amp;from=" . $config['time']['day'] .  "&amp;to=" . $config['time']['now'];
  $di .= "&amp;type=hour";
  $di .= "&amp;x=1190&amp;y=250";
  $di .= "$type'>";

  $mi  = "<img src='bandwidth-graph.php?bill_id=" . $bill_id . "&amp;bill_code=" . $_GET['bill_code'];
  $mi .= "&amp;from=" . $lastmonth .  "&amp;to=" . $rightnow;
  $mi .= "&amp;&type=day";
  $mi .= "&amp;x=1190&amp;y=250";
  $mi .= "$type'>";

  echo("<h3>Billing View</h3>$bi");
  #echo("<h3>Previous Bill View</h3>$li");
  echo("<h3>24 Hour View</h3>$di");
  echo("<h3>Monthly View</h3>$mi");

?>
