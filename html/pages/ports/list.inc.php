
<table cellpadding=3 cellspacing=0 class="devicetable sortable" width=100%>

<?php

#if ($_SESSION['userlevel'] >= '5') {
#  $sql = "SELECT * FROM `ports` AS I, `devices` AS D WHERE I.device_id = D.device_id ORDER BY D.hostname, I.ifDescr";
#} else {
#  $sql = "SELECT * FROM `ports` AS I, `devices` AS D, `devices_perms` AS P WHERE I.device_id = D.device_id AND D.device_id = P.device_id AND P.user_id = '" . $_SESSION['user_id'] . "' ORDER BY D.hostname, I.ifDescr";
#}

$param = array();

# FIXME block below is not totally used, at least the iftype stuff is bogus?
if ($_GET['opta'] == "down" || $_GET['type'] == "down" || $_POST['state'] == "down")
{
  $where .= "AND I.ifAdminStatus = 'up' AND I.ifOperStatus = 'down' AND I.ignore = '0'";
} elseif ($_GET['opta'] == "admindown" || $_GET['type'] == "admindown" || $_POST['state'] == "admindown") {
  $where .= "AND I.ifAdminStatus = 'down'";
} elseif ($_GET['opta'] == "errors" || $_GET['type'] == "errors" || $_POST['state'] == "errors") {
  $where .= "AND (I.`ifInErrors_delta` > '0' OR I.`ifOutErrors_delta` > '0')";
} elseif ($_GET['type'] == "up" || $_POST['state'] == "up") {
  $where .= "AND I.ifOperStatus = 'up'";
} elseif ($_GET['opta'] == "ignored" || $_GET['type'] == "ignored" || $_POST['state'] == "ignored") {
  $where .= "AND I.ignore = '1'";
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

if (is_numeric($_POST['device_id'])) 
{ 
  $where .= " AND I.device_id = ?";
  $param[] = $_POST['device_id'];
}
if ($_POST['ifType']) 
{
  $where .= " AND I.ifType = ?"; 
  $param[] = $_POST['ifType'];
}

if (is_numeric($_POST['ifSpeed'])) 
{
  $where .= " AND I.ifSpeed = ?"; 
  $param[] = $_POST['ifSpeed'];
}

if ($_POST['ifAlias']) {
  $where .= " AND I.ifAlias LIKE ?"; 
  $param[] = "%".$_POST['ifAlias']."%";
}

if ($_POST['deleted'] || $_GET['type'] == "deleted") { $where .= " AND I.deleted = '1'";  }

$query = "SELECT * FROM `ports` AS I, `devices` AS D WHERE I.device_id = D.device_id ".$where." ORDER BY D.hostname, I.ifIndex";

echo("<tr class=tablehead><td></td><th>Device</a></th><th>Interface</th><th>Speed</th><th>Down</th><th>Up</th><th>Media</th><th>Description</th></tr>");

$row = 1;

foreach (dbFetchRows($query, $param) as $interface)
{
  if (is_integer($row/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

  $speed = humanspeed($interface['ifSpeed']);
  $type = humanmedia($interface['ifType']);

  $interface['in_rate'] = formatRates($interface['ifInOctets_rate'] * 8);
  $interface['out_rate'] = formatRates($interface['ifOutOctets_rate'] * 8);


  if ($interface['in_errors'] > 0 || $interface['out_errors'] > 0)
  {
    $error_img = generate_port_link($interface,"<img src='images/16/chart_curve_error.png' alt='Interface Errors' border=0>",errors);
  } else { $error_img = ""; }

  if (port_permitted($interface['interface_id'], $interface['device_id']))
  {
    $interface = ifLabel($interface, $device);
    echo("<tr bgcolor=$row_colour>
          <td width=5></td>
          <td width=200 class=list-bold>" . generate_device_link($interface) . "</td>
          <td width=150 class=list-bold>" . generate_port_link($interface) . " $error_img</td>
          <td width=110 >$speed</td>
          <td width=110 class=green>".$interface['in_rate']."</td>
          <td width=110 class=blue>".$interface['out_rate']."</td>
          <td width=200>$type</td>
          <td>" . $interface['ifAlias'] . "</td>
        </tr>\n");

    $row++;
  }
}

echo("</table>");

?>
