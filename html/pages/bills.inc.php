<?php

if ($_POST['addbill'] == "yes")
{
  $updated = '1';

  if (isset($_POST['bill_quota']) or isset($_POST['bill_cdr'])) {
    if ($_POST['bill_type'] == "quota") {
      if (isset($_POST['bill_quota_type'])) {
        if ($_POST['bill_quota_type'] == "MB") { $multiplier = 1 * $config['billing']['base']; }
        if ($_POST['bill_quota_type'] == "GB") { $multiplier = 1 * $config['billing']['base'] * $config['billing']['base']; }
        if ($_POST['bill_quota_type'] == "TB") { $multiplier = 1 * $config['billing']['base'] * $config['billing']['base'] * $config['billing']['base']; }
        $bill_quota = (is_numeric($_POST['bill_quota']) ? $_POST['bill_quota'] * $config['billing']['base'] * $multiplier : 0);
        $bill_cdr = 0;
      }
    }
    if ($_POST['bill_type'] == "cdr") {
      if (isset($_POST['bill_cdr_type'])) {
        if ($_POST['bill_cdr_type'] == "Kbps") { $multiplier = 1 * $config['billing']['base']; }
        if ($_POST['bill_cdr_type'] == "Mbps") { $multiplier = 1 * $config['billing']['base'] * $config['billing']['base']; }
        if ($_POST['bill_cdr_type'] == "Gbps") { $multiplier = 1 * $config['billing']['base'] * $config['billing']['base'] * $config['billing']['base']; }
        $bill_cdr = (is_numeric($_POST['bill_cdr']) ? $_POST['bill_cdr'] * $multiplier : 0);
        $bill_quota = 0;
      }
    }
  }

  $insert = array('bill_name' => $_POST['bill_name'], 'bill_type' => $_POST['bill_type'], 'bill_cdr' => $bill_cdr, 'bill_day' => $_POST['bill_day'], 'bill_quota' => $bill_quota,
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
  if (is_numeric($vars['port']))
  {
    $port = dbFetchRow("SELECT * FROM `ports` AS P, `devices` AS D WHERE `port_id` = ? AND D.device_id = P.device_id", array($vars['port']));
  }

?>

<font face="Verdana, Arial, Sans-Serif"><h2>Bill : Add Bill</h2>
<?php

  print_optionbar_start();
  echo("<span style='font-weight: bold;'>Bill</span> &#187; ");
  if (!$vars['view']) { $vars['view'] = "add"; }
  if ($_SESSION['userlevel'] == "10")
  {
    if ($vars['view'] == "add") { echo("<span class='pagemenu-selected'>"); }
    echo('<A href="'.generate_url(array('page' => "bills/add")).'">Add</a>');
    if ($vars['view'] == "add") { echo("</span>"); }
  }
  echo('<div style="font-weight: bold; float: right;"><a href="'.generate_url(array('page' => "bills")).'/"><img align=absmiddle src="/images/16/arrow_left.png"> Back to Bills</a></div>');
  print_optionbar_end();

?>

<form name="form1" method="post" action="bills/" class="form-horizontal">
  <input type=hidden name=addbill value=yes>
  <link rel="stylesheet" href="css/bootstrap.min.css">
  <script type="text/javascript">
    function billType() {
      $('#cdrDiv').toggle();
      $('#quotaDiv').toggle();
    }
  </script>
<?php

if(is_array($port))
{
  $devicebtn = str_replace("list-device", "btn", generate_device_link($port));
  $portbtn = str_replace("interface-upup", "btn", generate_port_link($port));
  $portalias = (empty($port['ifAlias']) ? "" : " - ".$port['ifAlias']."");
  $devicebtn = str_replace("\">".$port['hostname'], "\" style=\"color: #000;\"><i class=\"icon-asterisk\"></i> ".$port['hostname'], $devicebtn);
  $devicebtn = str_replace("overlib('", "overlib('<div style=\'border: 5px solid #e5e5e5; background: #fff; padding: 10px;\'>", $devicebtn);
  $devicebtn = str_replace("<div>',;", "</div></div>',", $devicebtn);
  $portbtn = str_replace("\">".strtolower($port['ifName']), "\" style=\"color: #000;\"><i class=\"icon-random\"></i> ".$port['ifName']."".$portalias, $portbtn);
  $portbtn = str_replace("overlib('", "overlib('<div style=\'border: 5px solid #e5e5e5; background: #fff; padding: 10px;\'>", $portbtn);
  $portbtn = str_replace("<div>',;", "</div></div>',", $portbtn);
  echo("  <fieldset>\n");
  echo("    <input type=\"hidden\" name=\"port\" value=\"".$port['port_id']."\">\n");
  echo("    <legend>Ports</legend>\n");
  echo("    <div class=\"control-group\">\n");
  echo("      <div class=\"btn-toolbar\">\n");
  echo("        <div class=\"btn-group\">\n");
  echo("          ".$devicebtn."\n");
  echo("          ".$portbtn."\n");
  echo("        </div>\n");
  echo("      </div>\n");
  echo("    </div>\n");
  echo("  </fieldset>\n");
}

?>
  <fieldset>
    <legend>Bill Properties</legend>
    <div class="control-group">
      <label class="control-label" for="bill_name"><strong>Description</strong></label>
      <div class="controls">
        <input class="span4" type="text" name="bill_name" value="<?php echo($port['port_descr_descr']); ?>">
      </div>
    </div>
    <div class="control-group">
      <label class="control-label" for="bill_type"><strong>Billing Type</strong></label>
      <div class="controls">
        <input type="radio" name="bill_type" value="cdr" checked onchange="javascript: billType();" /> CDR 95th
        <input type="radio" name="bill_type" value="quota" onchange="javascript: billType();" /> Quota
        <div id="cdrDiv">
          <input class="span1" type="text" name="bill_cdr">
          <select name="bill_cdr_type" style="width: 233px;">
            <option value="Kbps">Kilobits per second (Kbps)</option>
            <option value="Mbps" selected>Megabits per second (Mbps)</option>
            <option value="Gbps">Gigabits per second (Gbps)</option>
          </select>
        </div>
        <div id="quotaDiv" style="display: none">
          <input class="span1" type="text" name="bill_quota">
          <select name="bill_quota_type" style="width: 233px;">
            <option value="MB">Megabytes (MB)</option>
            <option value="GB" selected>Gigabytes (GB)</option>
            <option value="TB">Terabytes (TB)</option>
          </select>
        </div>
      </div>
    </div>
    <div class="control-group">
      <label class="control-label" for="bill_day"><strong>Billing Day</strong></label>
      <div class="controls">
        <select name="bill_day" style="width: 60px;">
<?php

for ($x=1;$x<32;$x++) {
  echo("          <option value=\"".$x."\">".$x."</option>\n");
}

?>
        </select>
      </div>
    </div>
  </fieldset>
  <fieldset>
    <legend>Optional Information</legend>
    <div class="control-group">
      <label class="control-label" for="bill_custid"><strong>Customer&nbsp;Reference</strong></label>
      <div class="controls">
        <input class="span4" type="text" name="bill_custid">
      </div>
    </div>
    <div class="control-group">
      <label class="control-label" for="bill_ref"><strong>Billing Reference</strong></label>
      <div class="controls">
        <input class="span4" type="text" name="bill_ref" value="<?php echo($port['port_descr_circuit']); ?>">
      </div>
    </div>
    <div class="control-group">
      <label class="control-label" for="bill_notes"><strong>Notes</strong></label>
      <div class="controls">
        <input class="span4" type="textarea" name="bill_notes" value="<?php echo($port['port_descr_speed']); ?>">
      </div>
    </div>
  </fieldset>
  <div class="form-actions">
    <!-- <button class="btn btn-danger"><i class="icon-ban-circle icon-white"></i> Cancel</button> //-->
    <button type="submit" class="btn btn-primary"><i class="icon-ok-sign icon-white"></i> <strong>Add Bill</strong></button>
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
         $allowed = format_si($bill['bill_cdr'])."bps";
         $used    = format_si($rate_data['rate_95th'])."bps";
         $percent = round(($rate_data['rate_95th'] / $bill['bill_cdr']) * 100,2);
         $background = get_percentage_colours($percent);
         $overuse = $rate_data['rate_95th'] - $bill['bill_cdr'];
         $overuse = (($overuse <= 0) ? "-" : "<span style=\"color: #".$background['left']."; font-weight: bold;\">".format_si($overuse)."bps</span>");
      } elseif ($bill['bill_type'] == "quota") {
         $type = "Quota";
         $allowed = format_bytes_billing($bill['bill_quota']);
         $used    = format_bytes_billing($rate_data['total_data']);
         $percent = round(($rate_data['total_data'] / ($bill['bill_quota'])) * 100,2);
         $background = get_percentage_colours($percent);
         $overuse = $rate_data['total_data'] - $bill['bill_quota'];
         $overuse = (($overuse <= 0) ? "-" : "<span style=\"color: #".$background['left']."; font-weight: bold;\">".format_bytes_billing($overuse)."</span>");
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
    } // PERMITTED
  }
  echo("</table>");
}

//echo("</td></tr></table>");

?>
