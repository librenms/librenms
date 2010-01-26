<?php print_optionbar_start(50); ?>
<table style="text-align: left;" cellpadding=0 cellspacing=5 class=devicetable width=100%>
  <tr style='padding: 0px;'>
  <form method='post' action=''>
    <td width='200'>
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
    <td width='150'>
      <select name='state' id='state'>
        <option value=''>All States</option>
        <option value='up' <?php if($_POST['state'] == "up") { echo("selected"); } ?>>Up</option>
        <option value='down'<?php if($_POST['state'] == "down") { echo("selected"); } ?>>Down</option>
        <option value='admindown' <?php if($_POST['state'] == "admindown") { echo("selected"); } ?>>Shutdown</option>
        <option value='errors' <?php if($_POST['state'] == "errors") { echo("selected"); } ?>>Errors</option>
        <option value='ethernet' <?php if($_POST['state'] == "ethernet") { echo("selected"); } ?>>Ethernet</option>
        <option value='l2vlan' <?php if($_POST['state'] == "l2vlan") { echo("selected"); } ?>>L2 VLAN</option>
        <option value='sonet' <?php if($_POST['state'] == "sonet") { echo("selected"); } ?>>SONET</option>
        <option value='propvirtual' <?php if($_POST['state'] == "propvirtual") { echo("selected"); } ?>>Virtual</option>
        <option value='ppp' <?php if($_POST['state'] == "ppp") { echo("selected"); } ?>>PPP</option>
        <option value='loopback' <?php if($_POST['state'] == "loopback") { echo("selected"); } ?>>Loopback</option>
      </select>
    </td>
    <td width=110>
      <select name='ifSpeed' id='ifSpeed'>
      <option value=''>All Speeds</option>
      <?php
        $query = mysql_query("SELECT `ifSpeed` FROM `interfaces` GROUP BY `ifSpeed` ORDER BY `ifSpeed`");
        while($data = mysql_fetch_array($query)) {
          if ($data['ifSpeed'])
          {
            echo("<option value='".$data['ifSpeed']."'");
            if($data['ifSpeed'] == $_POST['ifSpeed']) { echo("selected"); }
            echo(">".humanspeed($data['ifSpeed'])."</option>");
          }
        }
      ?>
       </select>
    </td>
    <td width=200>
      <select name='ifType' id='ifType'>
      <option value=''>All Media</option>
      <?php
        $query = mysql_query("SELECT `ifType` FROM `interfaces` GROUP BY `ifType` ORDER BY `ifType`");
        while($data = mysql_fetch_array($query)) {
          if ($data['ifType'])
          {
            echo("<option value='".$data['ifType']."'");
            if($data['ifType'] == $_POST['ifType']) { echo("selected"); }
            echo(">".$data['ifType']."</option>");
          }
        }
      ?>
       </select>
             </td>
             <td>
        <input type="text" name="ifAlias" id="ifAlias" size=40 value="<?php  echo($_POST['ifAlias']); ?>" />
        Deleted <input type=checkbox id="deleted" name="deleted" value=1 <?php if($_POST['deleted']) { echo("checked"); } ?> ></input>
        </td>
        <td style="text-align: right;">
        <input style="align:right;" type=submit value=Search></div>
             </td>
           </form>
         </tr>
</table>
<?php print_optionbar_end(); ?>

<table cellpadding=3 cellspacing=0 class=devicetable width=100%>


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
  $where .= "AND ( I.`ifInErrors_delta` > '0' OR I.`ifOutErrors_delta` > '0' )";
} elseif ($_GET['type'] == "up" || $_POST['state'] == "up") {
  $where .= "AND I.ifOperStatus = 'up'";
} elseif ($_GET['type'] == "l2vlan" || $_POST['state'] == "l2vlan") {
  $where .= " AND I.ifType = 'l2vlan'";
} elseif ($_GET['type'] == "ethernet" || $_POST['state'] == "ethernet") {
  $where .= " AND I.ifType = 'ethernetCsmacd'";
} elseif ($_GET['type'] == "loopback" || $_POST['state'] == "loopback") {
  $where .= " AND I.ifType = 'softwareLoopback'";
} elseif ($_GET['typee'] == "sonet" || $_POST['state'] == "sonet") {
  $where .= " AND I.ifType = 'sonet'";
} elseif ($_POST['state'] == "propvirtual") {
  $where .= " AND I.ifType = 'propVirtual'";
} elseif ($_POST['state'] == "ppp") {
  $where .= " AND I.ifType = 'ppp'";
}



if($_POST['device_id']) { $where .= " AND I.device_id = '".$_POST['device_id']."'"; }
if($_POST['ifType']) { $where .= " AND I.ifType = '".$_POST['ifType']."'"; }
if($_POST['ifSpeed']) { $where .= " AND I.ifSpeed = '".$_POST['ifSpeed']."'"; }
if($_POST['ifAlias']) { $where .= " AND I.ifAlias LIKE '%".$_POST['ifAlias']."%'"; }
if($_POST['deleted'] || $_GET['type'] == "deleted") { $where .= " AND I.deleted = '1'";  }

$sql = "SELECT * FROM `interfaces` AS I, `devices` AS D WHERE I.device_id = D.device_id $where ORDER BY D.hostname, I.ifIndex";

$query = mysql_query($sql);

echo("<tr class=tablehead><td></td><th>Device</a></th><th>Interface</th><th>Speed</th><th>Media</th><th>Description</th></tr>");

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
          <td width=5></td>
          <td width=200 class=list-bold>" . generatedevicelink($interface) . "</td>
          <td width=150 class=list-bold>" . generateiflink($interface, makeshortif(fixifname($interface['ifDescr']))) . " $error_img</td>
          <td width=110 >$speed</td>
          <td width=200>$type</td>
          <td>" . $interface['ifAlias'] . "</td>
        </tr>\n");

    $row++;

  }

}

echo("</table>");


?>

