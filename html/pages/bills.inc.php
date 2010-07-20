
<?php

if($_POST['addbill'] == "yes") {

  $updated = '1';

  $sql = "INSERT INTO `bills` (`bill_name`,`bill_type`,`bill_cdr`,`bill_day`,`bill_gb`, `bill_custid`, `bill_ref`, `bill_notes`)
          VALUES ('" . mres($_POST['bill_name']) . "','" . mres($_POST['bill_type']) . "',
                                  '" . mres($_POST['bill_cdr']) . "','" . mres($_POST['bill_day']) . "',
                                  '" . mres($_POST['bill_quota']) . "','" . mres($_POST['bill_custid']) . "',
                                  '" . mres($_POST['bill_ref']) . "','" . mres($_POST['bill_notes']) . "' )";

  $query = mysql_query($sql);
  $affected = mysql_affected_rows() . "records affected";

  $message .= $message_break . "Bill ".mres($_POST['bill_name'])." added!";
  $message_break .= "<br />";

}


echo("<meta http-equiv='refresh' content='10000'>");

if($_GET['opta'] == "add") {

?>
<div style='padding:10px;font-size:20px; font-weight: bold;'>Add Bill</div>

<form name="form1" method="post" action="bills/">

 <input type=hidden name=addbill value=yes>

 <div style="padding: 10px; background: #f0f0f0;">
  <table cellpadding=2px width=400px>
  <tr>
    <td><strong>Description</strong></td>
    <td><input type="text" name="bill_name" size="32"></td>
  </tr>
  <tr>
    <td><strong>Billing Type</strong></td>
    <td>
      <input type="radio" name="bill_type" value="cdr" checked /> CDR 95th: <input type="text" name="bill_cdr" size="10">KBps
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
    <td><input type="text" name="bill_ref" size="32"></td>
  </tr>
  <tr>
    <td><strong>Notes</strong></td>
    <td><input type="textarea" name="bill_notes" size="32"></td>
  </tr>


  <tr>
    <td></td><td><input type="submit" class="submit" name="Submit" value=" Add Bill "></td>
  </tr>
  </table>
 </div>
</form>


<?php


} else {

print_optionbar_start('40');

  ?>

	<table cellpadding=7 cellspacing=0 class=devicetable width=100%>
<form method='post' action=''>
<tr>
             <td width='40' align=center valign=middle><div style='font-weight: bold; font-size: 16px;'>Bills</div></td>
             <td width='240'><span style='font-weight: bold; font-size: 14px;'></span>
             <input type="text" name="hostname" id="hostname" size=40 value="<?php  echo($_POST['hostname']); ?>" />
             </td>
             <td width='100'>
      <select name='os' id='os'>
      <option value=''>All Types</option>
      <option value=''>CDR</option>
      <option value=''>95th</option>			
      <option value=''>Quota</option>
       </select>
             </td>
             <td width='100'>
      <select name='hardware' id='hardware'>
      <option value=''>All States</option>
      <option value=''>Under Quota</option>
      <option value=''>Over Quota</option>
       </select>
             </td>
             <td width='100'>
      <select name='location' id='location'>
      <option value=''>All Customers</option>
       </select>
     </td>
		 <td>
         <input type=submit class=submit value=Search>
             </td>
             <td width='80' style='font-weight:bold; font-size: 12px;'>
						   <a href='bills/add/'><img src="images/16/add.png" align=absmiddle alt="Add"> Add Bill</a>
             </td>
           </tr>
  </form>
</table>

<?php

print_optionbar_end();

  $sql  = "SELECT * FROM `bills` ORDER BY `bill_name`";
  $query = mysql_query($sql);
  echo("<table border=0 cellspacing=0 cellpadding=5 class=devicetable width=100%>");
  $i=1;
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
    if(!is_integer($i/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }
    echo("
           <tr bgcolor='$row_colour'>
             <td width='7'></td>
             <td width='250'><a href='".$config['base_url']."/bill/".$bill['bill_id']."/'><span style='font-weight: bold;' class=interface>".$bill['bill_name']."</span></a></td>
             <td>$notes</td>
	     <td>$type</td>
             <td>$allowed</td>
             <td>$used</td>
             <td><img src='percentage.php?width=350&per=$percent'> $percent%</td>
						 <td></td>
						 <td width=60><a href='".$config['base_url']."/bill/1/edit/'><img src='images/16/wrench.png' align=absmiddle alt='Edit'> Edit</a></td>
           </tr>
         ");
    $i++;
  }
  echo("</table>");
}

echo("</td></tr></table>");

?>
