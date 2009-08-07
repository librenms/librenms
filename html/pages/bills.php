

<?php

echo("<table cellpadding=7 cellspacing=0 class=devicetable width=100%><tr><td>");

if($_GET['opta'] == "add") {

?>
<div style='font-size:20px; font-weight: bold;'>Add Bill</div>

<div style="margin:5px;">
  <b class="rounded">
  <b class="rounded1"><b></b></b>
  <b class="rounded2"><b></b></b>
  <b class="rounded3"></b>
  <b class="rounded4"></b>
  <b class="rounded5"></b></b>

  <div class="roundedfg" style="padding-left:10px;">
    Content
  </div>

  <b class="rounded">
  <b class="rounded5"></b>
  <b class="rounded4"></b>
  <b class="rounded3"></b>
  <b class="rounded2"><b></b></b>
  <b class="rounded1"><b></b></b></b>
</div>



<?php


} else {

  ?>
	
	<table cellpadding=7 cellspacing=0 class=devicetable width=100%>
<form method='post' action=''>
<tr bgcolor='#eeeeee' 
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
         <input type=submit value=Search>
             </td>
             <td width='80' style='font-weight:bold; font-size: 12px;'>
						   <a><img src="images/16/add.png" align=absmiddle alt="Add"> Add Bill</a>
             </td>
           </tr>
  </form>
</table>
	
	<?php

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
             <td width='250'><a href='".$config['base_url']."/bill/".$bill['bill_id']."'><span style='font-weight: bold;' class=interface>".$bill['bill_name']."</span></a></td>
             <td>$notes</td>
	     <td>$type</td>
             <td>$allowed</td>
             <td>$used</td>
             <td><img src='percentage.php?width=350&per=$percent'> $percent%</td>
						 <td></td>
						 <td width=60><a><img src='images/16/wrench.png' align=absmiddle alt='Edit'> Edit</a></td>
           </tr>
         ");
  }
  echo("</table>");
  $i++;
}

echo("</td></tr></table>");

?>
