<table cellpadding=2 cellspacing=1 class=devicetable width=100%>
  <tr bgcolor='#eeeeee' style='padding: 3px;'>
  <form method='post' action=''>
    <td width='200' style="padding: 10px;">
      <select name='device_id' id='device_id'>
      <option value=''>All Devices</option>
      <?php
        $query = mysql_query("SELECT `device_id`,`hostname` FROM `devices` GROUP BY `hostname` ORDER BY `hostname`");
        while($data = mysql_fetch_array($query)) {
          echo("<option value='".$data['device_id']."'");
          if($data['device_id'] == $_POST['device_id']) { echo("selected"); }
          echo(">".$data['hostname']."</option>");
        }
      ?>
      </select>
    </td>
    <td>
      <select name='state' id='state'>
        <option value=''>All States</option>
        <option value='up' <?php if($_POST['state'] == "up") { echo("selected"); } ?>>Up</option>
        <option value='down'<?php if($_POST['state'] == "down") { echo("selected"); } ?>>Down</option>
        <option value='admindown' <?php if($_POST['state'] == "admindown") { echo("selected"); } ?>>Shutdown</option>
        <option value='errors' <?php if($_POST['state'] == "errors") { echo("selected"); } ?>>Errors</option>
      </select>
    </td>
    <td>
      <select name='ifSpeed' id='ifSpeed'>
      <option value=''>All Speeds</option>
      <?php
        $query = mysql_query("SELECT `ifSpeed` FROM `interfaces` GROUP BY `ifSpeed` ORDER BY `ifSpeed`");
        while($data = mysql_fetch_array($query)) {
          echo("<option value='".$data['ifSpeed']."'");
          if($data['ifSpeed'] == $_POST['ifSpeed']) { echo("selected"); }
          echo(">".humanspeed($data['ifSpeed'])."</option>");
        }
      ?>
       </select>
    </td>
    <td>
      <select name='ifType' id='ifType'>
      <option value=''>All Media</option>
      <?php
        $query = mysql_query("SELECT `ifType` FROM `interfaces` GROUP BY `ifType` ORDER BY `ifType`");
        while($data = mysql_fetch_array($query)) {
          echo("<option value='".$data['ifType']."'");
          if($data['ifType'] == $_POST['ifType']) { echo("selected"); }
          echo(">".$data['ifType']."</option>");
        }
      ?>
       </select>
             </td>
             <td>
        <input type="text" name="ifAlias" id="ifAlias" size=40 value="<?php  echo($_POST['ifAlias']); ?>" />
        Deleted <input type=checkbox id="deleted" name="deleted" value=1 <?php if($_POST['deleted']) { echo("checked"); } ?> ></input>
        <input style="align:right;" type=submit value=Search></div>
             </td>
           </form>
         </tr>


<?php

#if ($_SESSION['userlevel'] >= '5') {
#  $sql = "SELECT * FROM `interfaces` AS I, `devices` AS D WHERE I.device_id = D.device_id ORDER BY D.hostname, I.ifDescr";
#} else {
#  $sql = "SELECT * FROM `interfaces` AS I, `devices` AS D, `devices_perms` AS P WHERE I.device_id = D.device_id AND D.device_id = P.device_id AND P.user_id = '" . $_SESSION['user_id'] . "' ORDER BY D.hostname, I.ifDescr";
#}


if($_GET['type'] == "down" || $_POST['state'] == "down") {
  $where .= "AND I.ifAdminStatus = 'up' AND I.ifOperStatus = 'down'";  
} elseif ($_GET['type'] == "admindown" || $_POST['state'] == "admindown") {
  $where .= "AND I.ifAdminStatus = 'down'";
} elseif ($_GET['type'] == "errors" || $_POST['state'] == "errors") {
  $where .= "AND ( I.`out_errors` > '0' OR I.`in_errors` > '0' )";
} elseif ($_GET['type'] == "up" || $_POST['state'] == "up") {
  $where .= "AND I.ifOperStatus = 'up'";
}

if($_POST['device_id']) { $where .= " AND I.device_id = '".$_POST['device_id']."'"; }
if($_POST['ifType']) { $where .= " AND I.ifType = '".$_POST['ifType']."'"; }
if($_POST['ifSpeed']) { $where .= " AND I.ifSpeed = '".$_POST['ifSpeed']."'"; }
if($_POST['ifAlias']) { $where .= " AND I.ifAlias LIKE '%".$_POST['ifAlias']."%'"; }
if($_POST['deleted'] || $_GET['type'] == "deleted") { $where .= " AND I.deleted = '1'";  }

print_r($_GET);

$sql = "SELECT * FROM `interfaces` AS I, `devices` AS D WHERE I.device_id = D.device_id $where ORDER BY D.hostname, I.ifIndex";

$query = mysql_query($sql);

echo("<tr class=tablehead><th>Device</a></th><th>Interface</th><th>Speed</th><th>Media</th><th>Description</th></tr>");

$row = 1;

while($interface = mysql_fetch_array($query)) {

  if(is_integer($row/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

  $speed = humanspeed($interface['ifSpeed']);
  $type = humanmedia($interface['ifType']);

    if($interface['in_errors'] > 0 || $interface['out_errors'] > 0) {
    $error_img = generateiflink($interface,"<img src='images/16/chart_curve_error.png' alt='Interface Errors' border=0>",errors);
  } else { $error_img = ""; }

  if( interfacepermitted($interface['interface_id']) ) 
  {
    echo("<tr bgcolor=$row_colour>
          <td class=list-bold>" . generatedevicelink($interface) . "</td>
          <td class=list-bold>" . generateiflink($interface, makeshortif(fixifname($interface['ifDescr']))) . " $error_img</td>
          <td>$speed</td>
          <td>$type</td>
          <td>" . $interface['ifAlias'] . "</td>
        </tr>\n");

    $row++;

  }

}

echo("</table>");


?>

