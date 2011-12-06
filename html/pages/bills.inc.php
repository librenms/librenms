<?php

if ($_POST['addbill'] == "yes")
{
  $updated = '1';

  ### Multiply bill_quota by base twice for now, as we know it's in GB. Later we should allow different measurements.
  if(is_numeric($_POST['bill_quota']) { $_POST['bill_quota'] * $config['billing']['base'] * $config['billing']['base']; }

  $insert = array('bill_name' => $_POST['bill_name'], 'bill_type' => $_POST['bill_type'], 'bill_cdr' => $_POST['bill_cdr'], 'bill_day' => $_POST['bill_day'], 'bill_quota' => $_POST['bill_quota'],
                  'bill_custid' => $_POST['bill_custid'], 'bill_ref' => $_POST['bill_ref'], 'bill_notes' => $_POST['bill_notes']);

  $bill_id = dbInsert($insert, 'bills');

  $message .= $message_break . "Bill ".mres($_POST['bill_name'])." (".$bill_id.") added!";
  $message_break .= "<br />";

  if (is_numeric($bill_id) && is_numeric($_POST['port']))
  {
    dbInsert(array('bill_id' => $bill_id, 'port_id' => $_POST['port']), 'bill_ports');
    $message .= $message_break . "Port ".mres($_POST['port'])." added!";
    $message_break .= "<br />";
  }
}

$pagetitle[] = "Billing";


echo("<meta http-equiv='refresh' content='10000'>");

if ($_GET['opta'] == "history")
{
  include("pages/bills/search.inc.php");
  include("pages/bills/pmonth.inc.php");
}
elseif ($_GET['opta'] == "add")
{
  if(is_numeric($vars['port']))
  {
    $port = dbFetchRow("SELECT * FROM `ports` AS P, `devices` AS D WHERE `interface_id` = ? AND D.device_id = P.device_id", array($vars['port']));
  }

?>

<div style='padding:10px;font-size:20px; font-weight: bold;'>Add Bill</div>
<form name="form1" method="post" action="bills/">

<?php

if(is_array($port))
{
  echo("<h3>Ports</h3>");
  echo(generate_port_link($port) . " on " . generate_device_link($port) . "<br />");
  echo("<input type=hidden name=port value=".$port['interface_id'].">");
}

?>

 <input type=hidden name=addbill value=yes>
 <div style="padding: 10px; background: #f0f0f0;">
  <table cellpadding=2px width=400px>
  <tr>
    <td><strong>Description</strong></td>
    <td><input type="text" name="bill_name" size="32" value="<?php echo($port['port_descr_descr']); ?>"></td>
  </tr>
  <tr>
    <td><strong>Billing Type</strong></td>
    <td>
      <input type="radio" name="bill_type" value="cdr" checked /> CDR 95th: <input type="text" name="bill_cdr" size="10">Kbps
      <br />
      <input type="radio" name="bill_type" value="quota" /> Quota: <input type="text" name="bill_quota" size="10">GB

  </tr>
  <tr>
    <td><strong>Billing Day</strong></td>
    <td><input type="text" name="bill_day" size="5" value="1"></td>
  </tr>
  <tr><td colspan=4><h3>Optional Information</h3></td></tr>
  <tr>
    <td><strong>Customer Reference</strong></td>
    <td><input type="text" name="bill_custid" size="32"></td>
  </tr>
  <tr>
    <td><strong>Billing Reference</strong></td>
    <td><input type="text" name="bill_ref" size="32" value="<?php echo($port['port_descr_circuit']); ?>"></td>
  </tr>
  <tr>
    <td><strong>Notes</strong></td>
    <td><input type="textarea" name="bill_notes" size="32" value="<?php echo($port['port_descr_speed']); ?>"></td>
  </tr>

  <tr>
    <td></td><td><input type="submit" class="submit" name="Submit" value=" Add Bill "></td>
  </tr>
  </table>
 </div>
</form>

<?php

} else {

  include("pages/bills/search.inc.php");

  $i=0;
  echo("<table border=0 cellspacing=0 cellpadding=5 class=devicetable width=100%>
           <tr style=\"font-weight: bold; \">
             <td width=\"7\"></td>
             <td width=\"250\">Billing name</td>
             <td></td>
             <td>Type</td>
             <td>Allowed</td>
             <td>Used</td>
             <td style=\"text-align: center;\">Overusage</td>
             <td width=\"250\"></td>
             <td width=\"60\"></td>
           </tr>");
  foreach (dbFetchRows("SELECT * FROM `bills` ORDER BY `bill_name`") as $bill)
  {
    if (bill_permitted($bill['bill_id']))
    {
      unset($class);
      $day_data     = getDates($bill['bill_day']);
      $datefrom     = $day_data['0'];
      $dateto       = $day_data['1'];
#      $rate_data    = getRates($bill['bill_id'],$datefrom,$dateto);
      $rate_data    = $bill;
      $rate_95th    = $rate_data['rate_95th'];
      $dir_95th     = $rate_data['dir_95th'];
      $total_data   = $rate_data['total_data'];
      $rate_average = $rate_data['rate_average'];

      if ($bill['bill_type'] == "cdr")
      {
         $type = "CDR";
         $allowed = formatRates($bill['bill_cdr'] * 1000);
         $used    = formatRates($rate_data['rate_95th'] * 1000);
         $percent = round(($rate_data['rate_95th'] / $bill['bill_cdr']) * 100,2);
         $background = get_percentage_colours($percent);
         $overuse = $rate_data['rate_95th'] - $bill['bill_cdr'];
         $overuse = (($overuse <= 0) ? "-" : "<span style=\"color: #".$background['left']."; font-weight: bold;\">".formatRates($overuse * 1000)."</span>");
      } elseif ($bill['bill_type'] == "quota") {
         $type = "Quota";
         $allowed = formatStorage($bill['bill_gb']* 1000 * 1000 * 1000);
         $used    = formatStorage($rate_data['total_data'] * 1000 * 1000);
         $percent = round(($rate_data['total_data'] / ($bill['bill_gb'] * 1000)) * 100,2);
         $background = get_percentage_colours($percent);
         $overuse = $rate_data['total_data'] - ($bill['bill_gb'] * 1000);
         $overuse = (($overuse <= 0) ? "-" : "<span style=\"color: #".$background['left']."; font-weight: bold;\">".formatStorage($overuse * 1000 * 1000)."</span>");
      }

      $right_background = $background['right'];
      $left_background  = $background['left'];

      if (!is_integer($i/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }
      echo("
           <tr bgcolor='$row_colour'>
             <td></td>
             <td><a href='".generate_url(array('page' => "bill", 'bill_id' => $bill['bill_id']))."'><span style='font-weight: bold;' class=interface>".$bill['bill_name']."</span></a><br />".strftime("%F", strtotime($datefrom))." to ".strftime("%F", strtotime($dateto))."</td>
             <td>$notes</td>
             <td>$type</td>
             <td>$allowed</td>
             <td>$used</td>
             <td style=\"text-align: center;\">$overuse</td>
             <td>".print_percentage_bar (250, 20, $perc, NULL, "ffffff", $background['left'], $percent . "%", "ffffff", $background['right'])."</td>
             <td><a href='".generate_url(array('page' => "bill", 'bill_id' => $bill['bill_id'], 'view' => "edit"))."'><img src='images/16/wrench.png' align=absmiddle alt='Edit'> Edit</a></td>
           </tr>
         ");

      $i++;
    } ### PERMITTED
  }
  echo("</table>");
}

echo("</td></tr></table>");

?>
