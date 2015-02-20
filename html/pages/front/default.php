<?php

include_once("includes/object-cache.inc.php");

function generate_front_box ($frontbox_class, $content)
{
echo("<div class=\"front-box $frontbox_class\">
      $content
      </div>");
}

echo('
  <div class="row">
');
if ($config['vertical_summary']) {
  echo('    <div class="col-md-9">');
}
else
{
  echo('    <div class="col-md-8">');
}
echo('
      <div class="row">
        <div class="col-md-12">
');

echo('<div class=front-page>');

echo('<div class="status-boxes">');

$count_boxes = 0;

// Device down boxes
if ($_SESSION['userlevel'] >= '10')
{
  $sql = "SELECT * FROM `devices` WHERE `status` = '0' AND `ignore` = '0' LIMIT ".$config['front_page_down_box_limit'];
} else {
  $sql = "SELECT * FROM `devices` AS D, devices_perms AS P WHERE D.device_id = P.device_id AND P.user_id = '" . $_SESSION['user_id'] . "' AND D.status = '0' AND D.ignore = '0' LIMIT".$config['front_page_down_box_limit'];
}
foreach (dbFetchRows($sql) as $device)
{
  generate_front_box("device-down", generate_device_link($device, shorthost($device['hostname']))."<br />
    <span class=list-device-down>Device Down</span> <br />
    <span class=body-date-1>".truncate($device['location'], 20)."</span>");
  ++$count_boxes;
}

if ($_SESSION['userlevel'] >= '10')
{
  $sql = "SELECT * FROM `ports` AS I, `devices` AS D WHERE I.device_id = D.device_id AND ifOperStatus = 'down' AND ifAdminStatus = 'up' AND D.ignore = '0' AND I.ignore = '0' LIMIT ".$config['front_page_down_box_limit'];
} else {
  $sql = "SELECT * FROM `ports` AS I, `devices` AS D, devices_perms AS P WHERE D.device_id = P.device_id AND P.user_id = '" . $_SESSION['user_id'] . "' AND  I.device_id = D.device_id AND ifOperStatus = 'down' AND ifAdminStatus = 'up' AND D.ignore = '0' AND I.ignore = '0' LIMIT ".$config['front_page_down_box_limit'];
}

// These things need to become more generic, and more manageable across different frontpages... rewrite inc :>

// Port down boxes
if ($config['warn']['ifdown'])
{
  foreach (dbFetchRows($sql) as $interface)
  {
    if (!$interface['deleted'])
    {
      $interface = ifNameDescr($interface);
      generate_front_box("port-down", generate_device_link($interface, shorthost($interface['hostname']))."<br />
        <span class=\"interface-updown\">Port Down</span><br />
<!--      <img src='graph.php?type=bits&amp;if=".$interface['port_id']."&amp;from=".$config['time']['day']."&amp;to=".$config['time']['now']."&amp;width=100&amp;height=32' /> -->
        ".generate_port_link($interface, truncate(makeshortif($interface['label']),13,''))." <br />
        " . ($interface['ifAlias'] ? '<span class="body-date-1">'.truncate($interface['ifAlias'], 20, '').'</span>' : ''));
      ++$count_boxes;
    }
  }
}

/* FIXME service permissions? seem nonexisting now.. */
// Service down boxes
if ($_SESSION['userlevel'] >= '10')
{
  $sql = "SELECT * FROM `services` AS S, `devices` AS D WHERE S.device_id = D.device_id AND service_status = 'down' AND D.ignore = '0' AND S.service_ignore = '0' LIMIT ".$config['front_page_down_box_limit'];
  $param[] = '';
}
else
{
  $sql = "SELECT * FROM services AS S, devices AS D, devices_perms AS P WHERE P.`user_id` = ? AND P.`device_id` = D.`device_id` AND S.`device_id` = D.`device_id` AND S.`service_ignore` = '0' AND S.`service_disabled` = '0' AND S.`service_status` = '0' LIMIT ".$config['front_page_down_box_limit'];
  $param[] = $_SESSION['user_id'];
}
foreach (dbFetchRows($sql,$param) as $service)
{
  generate_front_box("service-down", generate_device_link($service, shorthost($service['hostname']))."<br />
    <span class=service-down>Service Down</span>
    ".$service['service_type']."<br />
    <span class=body-date-1>".truncate($interface['ifAlias'], 20)."</span>");
  ++$count_boxes;
}

// BGP neighbour down boxes
if (isset($config['enable_bgp']) && $config['enable_bgp'])
{
  if ($_SESSION['userlevel'] >= '10')
  {
    $sql = "SELECT * FROM `devices` AS D, bgpPeers AS B WHERE bgpPeerAdminStatus != 'start' AND bgpPeerState != 'established' AND bgpPeerState != '' AND B.device_id = D.device_id AND D.ignore = 0 LIMIT ".$config['front_page_down_box_limit'];
  } else {
    $sql = "SELECT * FROM `devices` AS D, bgpPeers AS B, devices_perms AS P WHERE D.device_id = P.device_id AND P.user_id = '" . $_SESSION['user_id'] . "' AND  bgpPeerAdminStatus != 'start' AND bgpPeerState != 'established' AND bgpPeerState != '' AND B.device_id = D.device_id AND D.ignore = 0 LIMIT ".$config['front_page_down_box_limit'];
  }
  foreach (dbFetchRows($sql) as $peer)
  {
  generate_front_box("bgp-down", generate_device_link($peer, shorthost($peer['hostname']))."<br />
    <span class=bgp-down>BGP Down</span>
    <span class='" . (strstr($peer['bgpPeerIdentifier'],':') ? 'front-page-bgp-small' : 'front-page-bgp-normal') . "'>".$peer['bgpPeerIdentifier']."</span><br />
    <span class=body-date-1>AS".truncate($peer['bgpPeerRemoteAs']." ".$peer['astext'], 14, "")."</span>");
    ++$count_boxes;
  }
}

// Device rebooted boxes
if (filter_var($config['uptime_warning'], FILTER_VALIDATE_FLOAT) !== FALSE && $config['uptime_warning'] > 0)
{
  if ($_SESSION['userlevel'] >= '10')
  {
    $sql = "SELECT * FROM `devices` AS D WHERE D.status = '1' AND D.uptime > 0 AND D.uptime < '" . $config['uptime_warning'] . "' AND D.ignore = 0 LIMIT ".$config['front_page_down_box_limit'];
  } else {
    $sql = "SELECT * FROM `devices` AS D, devices_perms AS P WHERE D.device_id = P.device_id AND P.user_id = '" . $_SESSION['user_id'] . "' AND D.status = '1' AND D.uptime > 0 AND D.uptime < '" .
    $config['uptime_warning'] . "' AND D.ignore = 0 LIMIT ".$config['front_page_down_box_limit'];
  }

  foreach (dbFetchRows($sql) as $device)
  {
    generate_front_box("device-rebooted", generate_device_link($device, shorthost($device['hostname']))."<br />
      <span class=device-rebooted>Device Rebooted</span><br />
      <span class=body-date-1>".formatUptime($device['uptime'], 'short')."</span>");
    ++$count_boxes;
  }
}
if ($count_boxes == 0) {
  echo("<h5>Nothing here yet</h5><p class=welcome>This is where status notifications about devices and services would normally go. You might have none
  because you run such a great network, or perhaps you've just started using ".$config['project_name'].". If you're new to ".$config['project_name'].", you might
  want to start by adding one or more devices in the Devices menu.</p>");
}
echo('</div>');
echo('</div>');
echo('</div>');
echo('
  </div>
  </div>
');

if ($config['vertical_summary'])
{
  echo('   <div class="col-md-3">');
  include_once("includes/device-summary-vert.inc.php");
}
else
{
  echo('   <div class="col-md-4">');
  include_once("includes/device-summary-horiz.inc.php");
}

echo('
  </div>
</div>
<div class="row">
  <div class="col-md-12">
');

if ($config['enable_syslog'])
{

  $sql = "SELECT *, DATE_FORMAT(timestamp, '%D %b %T') AS date from syslog ORDER BY timestamp DESC LIMIT 20";
  $query = mysql_query($sql);

  echo('<div class="container-fluid">
          <div class="row">
            <div class="col-md-12">
              &nbsp;
            </div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="panel panel-default panel-condensed">
              <div class="panel-heading">
                <strong>Syslog entries</strong>
              </div>
              <table class="table table-hover table-condensed table-striped">');

  foreach (dbFetchRows($sql) as $entry)
  {
    $entry = array_merge($entry, device_by_id_cache($entry['device_id']));

    include("includes/print-syslog.inc.php");
  }
  echo("</table>");
  echo("</div>");
  echo("</div>");
  echo("</div>");
  echo("</div>");

} else {

  if ($_SESSION['userlevel'] >= '10')
  {
    $query = "SELECT *,DATE_FORMAT(datetime, '%D %b %T') as humandate  FROM `eventlog` ORDER BY `datetime` DESC LIMIT 0,15";
    $alertquery = "SELECT devices.device_id,name,time_logged FROM alert_log LEFT JOIN devices ON alert_log.device_id=devices.device_id RIGHT JOIN alert_rules ON alert_log.rule_id=alert_rules.id ORDER BY `time_logged` DESC LIMIT 0,15";
  } else {
    $query = "SELECT *,DATE_FORMAT(datetime, '%D %b %T') as humandate  FROM `eventlog` AS E, devices_perms AS P WHERE E.host = P.device_id AND P.user_id = " . $_SESSION['user_id'] . " ORDER BY `datetime` DESC LIMIT 0,15";
    $alertquery = "SELECT devices.device_id,name,time_logged FROM alert_log LEFT JOIN devices ON alert_log.device_id=devices.device_id RIGHT JOIN alert_rules ON alert_log.rule_id=alert_rules.id RIGHT JOIN devices_perms ON alert_log.device_id = devices_perms.device_id AND devices_perms.user_id = " . $_SESSION['user_id'] . " ORDER BY `time_logged` DESC LIMIT 0,15";
  }

  $data = mysql_query($query);
  $alertdata = mysql_query($alertquery);

  echo('<div class="container-fluid">
          <div class="row">
            <div class="col-md-6">
              &nbsp;
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 column">
              <div class="panel panel-default panel-condensed">
              <div class="panel-heading">
                <strong>Alertlog entries</strong>
              </div>
              <table class="table table-hover table-condensed table-striped">');

  foreach (dbFetchRows($alertquery) as $alert_entry)
  {
    include("includes/print-alerts.inc.php");
  }
          echo('</table>
                 </div>
                  </div>
            <div class="col-md-6 column">
              <div class="panel panel-default panel-condensed">
              <div class="panel-heading">
                <strong>Eventlog entries</strong>
              </div>
              <table class="table table-hover table-condensed table-striped">');

  foreach (dbFetchRows($query) as $entry)
  {
    include("includes/print-event.inc.php");
  }

  echo("</table>");
  echo("</div>");
  echo("</div>");
  echo("</div>");
  echo("</div>");
}

echo("</div>");

echo('
</div>
');

?>
