<?php

function generate_front_box ($background, $content)
{
echo("<div style='text-align: center; margin: 2px; border: solid 2px #D0D0D0; float: left; margin-right: 2px; padding: 3px; width: 117px; height: 85px; background: $background;'>
      $content
      </div>");
}

echo("<div style='padding: 3px 10px; background: #fff;'>");

if ($_SESSION['userlevel'] == '10')
{
$sql = mysql_query("SELECT * FROM `devices` WHERE `status` = '0' AND `ignore` = '0'");
} else {
$sql = mysql_query("SELECT * FROM `devices` AS D, devices_perms AS P WHERE D.device_id = P.device_id AND P.user_id = '" . $_SESSION['user_id'] . "' AND D.status = '0' AND D.ignore = '0'");
}
while ($device = mysql_fetch_assoc($sql)){

      generate_front_box("#ffaaaa", "<center><strong>".generate_device_link($device, shorthost($device['hostname']))."</strong><br />
      <span style='font-size: 14px; font-weight: bold; margin: 5px; color: #c00;'>Device Down</span> <br />
      <span class=body-date-1>".truncate($device['location'], 20)."</span>
      </center>");

}

if ($_SESSION['userlevel'] == '10')
{
$sql = mysql_query("SELECT * FROM `ports` AS I, `devices` AS D WHERE I.device_id = D.device_id AND ifOperStatus = 'down' AND ifAdminStatus = 'up' AND D.ignore = '0' AND I.ignore = '0'");
} else {
$sql = mysql_query("SELECT * FROM `ports` AS I, `devices` AS D, devices_perms AS P WHERE D.device_id = P.device_id AND P.user_id = '" . $_SESSION['user_id'] . "' AND  I.device_id = D.device_id AND ifOperStatus = 'down' AND ifAdminStatus = 'up' AND D.ignore = '0' AND I.ignore = '0'");
}

### These things need to become more generic, and more manageable across different frontpages... rewrite inc :>

while ($interface = mysql_fetch_assoc($sql))
{
  if (!$interface['deleted'])
  {
   $interface = ifNameDescr($interface);
   generate_front_box("#ffdd99", "<center><strong>".generate_device_link($interface, shorthost($interface['hostname']))."</strong><br />
      <span style='font-size: 14px; font-weight: bold; margin: 5px; color: #c00;'>Port Down</span><br />
<!--      <img src='graph.php?type=bits&amp;if=".$interface['interface_id']."&amp;from=$day&amp;to=$now&amp;width=100&amp;height=32' /> -->
      <strong>".generate_port_link($interface, truncate(makeshortif($interface['label']),13,''))."</strong> <br />
      " . ($interface['ifAlias'] ? '<span class="body-date-1">'.truncate($interface['ifAlias'], 20, '').'</span>' : '') . "
      </center>");
  }
}

/* FIXME service permissions? seem nonexisting now.. */
$sql = mysql_query("SELECT * FROM `services` AS S, `devices` AS D WHERE S.device_id = D.device_id AND service_status = 'down' AND D.ignore = '0' AND S.service_ignore = '0'");
while ($service = mysql_fetch_assoc($sql))
{
    generate_front_box("#ffaaaa", "<center><strong>".generate_device_link($service, shorthost($service['hostname']))."</strong><br />
    <span style='font-size: 14px; font-weight: bold; margin: 5px; color: #c00;'>Service Down</span>
    <strong>".$service['service_type']."</strong><br />
    <span class=body-date-1>".truncate($interface['ifAlias'], 20)."</span>
    </center>");
}

if (isset($config['enable_bgp']) && $config['enable_bgp'])
{
  if ($_SESSION['userlevel'] == '10')
  {
    $sql = mysql_query("SELECT * FROM `devices` AS D, bgpPeers AS B WHERE bgpPeerAdminStatus != 'start' AND bgpPeerState != 'established' AND bgpPeerState != '' AND B.device_id = D.device_id AND D.ignore = 0");
  } else {
    $sql = mysql_query("SELECT * FROM `devices` AS D, bgpPeers AS B, devices_perms AS P WHERE D.device_id = P.device_id AND P.user_id = '" . $_SESSION['user_id'] . "' AND  bgpPeerAdminStatus != 'start' AND bgpPeerState != 'established' AND bgpPeerState != '' AND B.device_id = D.device_id AND D.ignore = 0");
  }
  while ($peer = mysql_fetch_assoc($sql))
  {
  generate_front_box("#ffaaaa", "<center><strong>".generate_device_link($peer, shorthost($peer['hostname']))."</strong><br />
      <span style='font-size: 14px; font-weight: bold; margin: 5px; color: #c00;'>BGP Down</span>
      <span style='" . (strstr($peer['bgpPeerIdentifier'],':') ? 'font-size: 10px' : '') . "'><strong>".$peer['bgpPeerIdentifier']."</strong></span><br />
      <span class=body-date-1>AS".truncate($peer['bgpPeerRemoteAs']." ".$peer['astext'], 14, "")."</span>
      </center>");
  }
}

if (filter_var($config['uptime_warning'], FILTER_VALIDATE_FLOAT) !== FALSE && $config['uptime_warning'] > 0)
{
  if ($_SESSION['userlevel'] == '10')
  {
  $sql = mysql_query("SELECT * FROM `devices` AS D WHERE D.status = '1' AND D.uptime < '" . $config['uptime_warning'] . "' AND D.ignore = 0");
  } else {
  $sql = mysql_query("SELECT * FROM `devices` AS D, devices_perms AS P WHERE D.device_id = P.device_id AND P.user_id = '" . $_SESSION['user_id'] . "' AND D.status = '1' AND D.uptime < '" .
  $config['uptime_warning'] . "' AND D.ignore = 0");
  }

  while ($device = mysql_fetch_assoc($sql))
  {
     generate_front_box("#aaffaa", "<center><strong>".generate_device_link($device, shorthost($device['hostname']))."</strong><br />
        <span style='font-size: 14px; font-weight: bold; margin: 5px; color: #009;'>Device<br />Rebooted</span><br />
        <span class=body-date-1>".formatUptime($device['uptime'], 'short')."</span>
        </center>");
  }
}

if ($config['enable_syslog'])
{
  ## Open Syslog Div
  echo("<div style='margin: 4px; clear: both; padding: 5px;'>
    <h3>Recent Syslog Messages</h3>
  ");

  $sql = "SELECT *, DATE_FORMAT(timestamp, '%D %b %T') AS date from syslog ORDER BY timestamp DESC LIMIT 20";
  $query = mysql_query($sql);
  echo("<table cellspacing=0 cellpadding=2 width=100%>");
  while ($entry = mysql_fetch_assoc($query))
  {
    $entry = array_merge($entry, device_by_id_cache($entry['device_id']));
    include("includes/print-syslog.inc.php");
  }
  echo("</table>");

  echo("</div>"); ## Close Syslog Div

} else {

  ## Open eventlog Div
  echo("<div style='margin: 4px; clear: both; padding: 5px;'>
    <h3>Recent Eventlog Entries</h3>
  ");

  if ($_SESSION['userlevel'] == '10')
  {
    $query = "SELECT *,DATE_FORMAT(datetime, '%D %b %T') as humandate  FROM `eventlog` ORDER BY `datetime` DESC LIMIT 0,15";
  } else {
    $query = "SELECT *,DATE_FORMAT(datetime, '%D %b %T') as humandate  FROM `eventlog` AS E, devices_perms AS P WHERE E.host =
    P.device_id AND P.user_id = " . $_SESSION['user_id'] . " ORDER BY `datetime` DESC LIMIT 0,15";
  }

  $data = mysql_query($query);

  echo('<table cellspacing="0" cellpadding="1" width="100%">');

  while ($entry = mysql_fetch_assoc($data)) {
    include("includes/print-event.inc.php");
  }

  echo("</table>");
  echo("</div>"); ## Close Syslog Div
}

echo("</div>");

?>
