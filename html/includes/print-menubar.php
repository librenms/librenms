<?php

$service_alerts = mysql_result(mysql_query("SELECT count(service_id) FROM services WHERE service_status = '0'"),0);
$if_alerts = mysql_result(mysql_query("SELECT count(*) FROM `ports` WHERE `ifOperStatus` = 'down' AND `ifAdminStatus` = 'up' AND `ignore` = '0'"),0);
$device_alerts = "0";
$device_alert_sql = "WHERE 0";

if (isset($config['enable_bgp']) && $config['enable_bgp'])
{
  $bgp_alerts = mysql_result(mysql_query("SELECT COUNT(*) FROM bgpPeers AS B where (bgpPeerAdminStatus = 'start' OR bgpPeerAdminStatus = 'running') AND bgpPeerState != 'established'"), 0);
}

$query_a = mysql_query("SELECT * FROM `devices`");
while ($device = mysql_fetch_assoc($query_a))
{
  $this_alert = 0;
  if ($device['status'] == 0 && $device['ignore'] == '0') { $this_alert = "1"; } elseif ($device['ignore'] == '0')
  {
    if (mysql_result(mysql_query("SELECT count(service_id) FROM services WHERE service_status = '0' AND device_id = '".$device['device_id']."'"),0)) { $this_alert = "1"; }
    if (mysql_result(mysql_query("SELECT count(*) FROM ports WHERE `ifOperStatus` = 'down' AND `ifAdminStatus` = 'up' AND device_id = '" . $device['device_id'] . "' AND `ignore` = '0'"),0)) { $this_alert = "1"; }
  }
  if ($this_alert)
  {
   $device_alerts++;
   $device_alert_sql .= " OR `device_id` = '" . $device['device_id'] . "'";
  }
}
?>

<div class="menu2">
<ul>
<li><a class="menu2four" href="overview/"><img src="images/16/lightbulb.png" border="0" align="absmiddle" /> Overview</a>
        <table><tr><td>
        <ul>
        <?php if (isset($config['enable_map']) && $config['enable_map']) {
          echo('<li><a href="map/"><img src="images/16/map.png" border="0" align="absmiddle" /> Network Map</a></li>');
        } ?>
        <li><a href="eventlog/"><img src="images/16/report.png" border="0" align="absmiddle" /> Eventlog</a></li>
  <?php if (isset($config['enable_syslog']) && $config['enable_syslog']) {
      echo('<li><a href="syslog/"><img src="images/16/page.png" border="0" align="absmiddle" /> Syslog</a></li>');
  } ?>
<!--        <li><a href="alerts/"><img src="images/16/exclamation.png" border="0" align="absmiddle" /> Alerts</a></li> -->
        <li><a href="inventory/"><img src="images/16/bricks.png" border="0" align="absmiddle" /> Inventory</a></li>
        </ul>
        </td></tr></table>

</li>
</ul>
<ul>
<li><a class="menu2four" href="devices/"><img src="images/16/server.png" border="0" align="absmiddle" /> Devices</a>
        <table><tr><td>
        <ul>
        <li><a href="devices/"><img src="images/16/server.png" border="0" align="absmiddle" /> All Devices</a></li>
        <li><hr width="140" /></li>

<?php

foreach ($config['device_types'] as $devtype)
{
  echo('        <li><a href="devices/' . $devtype['type'] . '/"><img src="images/icons/' . $devtype['icon'] . '" border="0" align="absmiddle" /> ' . $devtype['text'] . '</a></li>');
}

?>
        <li><hr width="140" /></li>
        <li><a href="devices/alerted/"><img src="images/icons/alerts.png" border="0" align="absmiddle" /> Alerts (<?php echo($device_alerts) ?>)</a></li>
<?php
if ($_SESSION['userlevel'] >= '10') {
  echo('
        <li><hr width="140" /></li>
        <li><a href="addhost/"><img src="images/16/server_add.png" border="0" align="absmiddle" /> Add Device</a></li>
        <li><a href="delhost/"><img src="images/16/server_delete.png" border="0" align="absmiddle" /> Delete Device</a></li>');
}
?>

        </ul>
        </td></tr></table>
</li>
<?php
## Display Services entry if $config['show_services']
if (!isset($config['show_services']) || $config['show_services'])
{
?>
<li><a class="menu2four" href="services/"><img src="images/16/cog.png" border="0" align="absmiddle" /> Services</a>
        <table><tr><td>
        <ul>
        <li><a href="services/"><img src="images/16/cog.png" border="0" align="absmiddle" /> All Services </a></li>
<?php if ($service_alerts) {
echo('  <li><hr width=140 /></li>
        <li><a href="services/?status=0"><img src="images/16/cog_error.png" border="0" align="absmiddle" /> Alerts ('.$service_alerts.')</a></li>');
} ?>

<?php
if ($_SESSION['userlevel'] >= '10') {
  echo('
        <li><hr width="140" /></li>
        <li><a href="addsrv/"><img src="images/16/cog_add.png" border="0" align="absmiddle" /> Add Service</a></li>
        <li><a href="delsrv/"><img src="images/16/cog_delete.png" border="0" align="absmiddle" /> Delete Service</a></li>');
}
?>
        </ul>
        </td></tr></table>
</li>

<?php
}

## Display Locations entry if $config['show_locations']
if ($config['show_locations'])
{
?>
<li><a class="menu2four" href="locations/"><img src="images/16/building.png" border="0" align="absmiddle" /> Locations</a>
<?php
  if ($config['show_locations_dropdown'])
  {
?>
        <table><tr><td>
        <ul>
<?php
    foreach (getlocations() as $location)
    {
      echo('        <li><a href="?page=devices&amp;location=' . urlencode($location) . '"><img src="images/16/building.png" border="0" align="absmiddle" /> ' . $location . ' </a></li>');
    }
?>
        </ul>
        </td></tr></table>
<?php
  }
?>
</li>
<?php
}
?>

<li><a class="menu2four" href="ports/"><img src="images/16/connect.png" border="0" align="absmiddle" /> Ports</a>

<table><tr><td>
        <ul>
<li><a href="ports/"><img src="images/16/connect.png" border="0" align="absmiddle" /> All Ports</a></li>

<?php

if ($ports['errored'])
{
  echo('<li><a href="ports/errors/"><img src="images/16/chart_curve_error.png" border="0" align="absmiddle" /> Errored ('.$ports['errored'].')</a></li>');
}

if ($ports['ignored'])
{
  echo('<li><a href="ports/ignored/"><img src="images/16/chart_curve_link.png" border="0" align="absmiddle" /> Ignored ('.$ports['ignored'].')</a></li>');
}

if ($config['enable_billing']) { echo('<li><a href="bills/"><img src="images/16/money_pound.png" border="0" align="absmiddle" /> Traffic Bills</a></li>'); $ifbreak = 1; }

if ($config['enable_pseudowires']) { echo('<li><a href="pseudowires/"><img src="images/16/arrow_switch.png" border="0" align="absmiddle" /> Pseudowires</a></li>'); $ifbreak = 1; }

?>
<?php

if ($_SESSION['userlevel'] >= '5')
{
  echo('<li><hr width="140" /></li>');
  if ($config['int_customers']) { echo('<li><a href="customers/"><img src="images/16/group_link.png" border="0" align="absmiddle" /> Customers</a></li>'); $ifbreak = 1; }
  if ($config['int_l2tp']) { echo('<li><a href="iftype/l2tp/"><img src="images/16/user.png" border="0" align="absmiddle" /> L2TP</a></li>'); $ifbreak = 1; }
  if ($config['int_transit']) { echo('<li><a href="iftype/transit/"><img src="images/16/lorry_link.png" border="0" align="absmiddle" /> Transit</a></li>');  $ifbreak = 1; }
  if ($config['int_peering']) { echo('<li><a href="iftype/peering/"><img src="images/16/bug_link.png" border="0" align="absmiddle" /> Peering</a></li>'); $ifbreak = 1; }
  if ($config['int_peering'] && $config['int_transit']) { echo('<li><a href="iftype/peering,transit/"><img src="images/16/world_link.png" border="0" align="absmiddle" /> Peering + Transit</a></li>'); $ifbreak = 1; }
  if ($config['int_core']) { echo('<li><a href="iftype/core/"><img src="images/16/brick_link.png" border="0" align="absmiddle" /> Core</a></li>'); $ifbreak = 1; }
}

if ($ifbreak) { echo('<li><hr width="140" /></li>'); }

if (isset($interface_alerts))
{
  echo('<li><a href="ports/?status=0"><img src="images/16/link_error.png" border="0" align="absmiddle" /> Alerts ('.$interface_alerts.')</a></li>');
}

$sql = "SELECT * FROM `ports` AS P, `devices` as D WHERE P.`deleted` = '1' AND D.device_id = P.device_id";
$query = mysql_query($sql);
$deleted_ports = 0;
while ($interface = mysql_fetch_assoc($query))
{
  if (port_permitted($interface['interface_id'], $interface['device_id']))
  {
    $deleted_ports++;
  }
}
?>

<li><a href="ports/down/"><img src="images/16/if-disconnect.png" border="0" align="absmiddle" /> Down</a></li>
<li><a href="ports/admindown/"><img src="images/16/if-disable.png" border="0" align="absmiddle" /> Disabled</a></li>
<?php

if ($deleted_ports) { echo('<li><a href="ports/deleted/"><img src="images/16/cross.png" border="0" align="absmiddle" /> Deleted ('.$deleted_ports.')</a></li>'); }

?>
</ul></td></tr></table>
</li>

<?php

# FIXME does not check user permissions...
$sql = "SELECT sensor_class,COUNT(sensor_id) AS c FROM sensors GROUP BY sensor_class ORDER BY sensor_class ";
$result = mysql_query($sql);
while ($row = mysql_fetch_assoc($result))
{
  $used_sensors[$row['sensor_class']] = $row['c'];
}

# Copy the variable so we can use $used_sensors later in other parts of the code
$menu_sensors = $used_sensors;

?>

<li><a class="menu2four" href="health/"><img src="images/icons/sensors.png" border="0" align="absmiddle" /> Health
<!--[if IE 7]><!--></a><!--<![endif]-->
        <table><tr><td>
        <ul>
        <li><a href="health/mempool/"><img src="images/icons/memory.png" border="0" align="absmiddle" /> Memory</a></li>
        <li><a href="health/processor/"><img src="images/icons/processor.png" border="0" align="absmiddle" /> Processor</a></li>
        <li><a href="health/storage/"><img src="images/icons/storage.png" border="0" align="absmiddle" /> Storage</a></li>
<?php
if ($menu_sensors)
{
  $sep = 0;
  echo('<li><hr width="140" /></li>');
}

foreach (array('fanspeed','humidity','temperature') as $item)
{
  if ($menu_sensors[$item])
  {
    echo ('<li><a href="health/'.$item.'/"><img src="images/icons/'.$item.'.png" border="0" align="absmiddle" /> '.ucfirst($item).'</a></li>');
    unset($menu_sensors[$item]);$sep++;
  }
}

if ($sep)
{
  echo('<li><hr width="140" /></li>');
  $sep = 0;
}

foreach (array('current','frequency','power','voltage') as $item)
{
  if ($menu_sensors[$item])
  {
    echo ('<li><a href="health/'.$item.'/"><img src="images/icons/'.$item.'.png" border="0" align="absmiddle" /> '.ucfirst($item).'</a></li>');
    unset($menu_sensors[$item]);$sep++;
  }
}

# Show remaining/custom sensor types if there are any in the database
if ($menu_sensors && $sep)
{
  echo('<li><hr width="140" /></li>');
  $sep = 0;
}

foreach (array_keys($menu_sensors) as $item)
{
  echo ('<li><a href="health/'.$item.'/"><img src="images/icons/'.$item.'.png" border="0" align="absmiddle" /> '.ucfirst($item).'</a></li>');
  unset($menu_sensors[$item]);$sep++;
}

?>
        </ul>
        </td></tr></table>
<!--[if lte IE 6]></a><![endif]-->
</li>


<!-- <li><a class="menu2four" href="inventory/"><img src="images/16/bricks.png" border="0" align="absmiddle" /> Inventory</a></li> -->

<?php
if ($_SESSION['userlevel'] >= '5')
{
    echo('
      <li><a class="menu2four" href="routing/"><img src="images/16/arrow_branch.png" border="0" align="absmiddle" /> Routing</a>
        <table><tr><td>
        <ul>');

  if ($config['enable_vrfs']) { echo('<li><a href="routing/vrf/"><img src="images/16/layers.png" border="0" align="absmiddle" /> VRFs</a></li> <li><hr width=140></li> '); }

  ## BGP Sessions
  if ($_SESSION['userlevel'] >= '5' && (isset($config['enable_bgp']) && $config['enable_bgp']))
  {
    echo('
        <li><a href="routing/bgp/all/nographs/"><img src="images/16/link.png" border="0" align="absmiddle" /> BGP All Sessions </a></li>

        <li><a href="routing/bgp/external/nographs/"><img src="images/16/world_link.png" border="0" align="absmiddle" /> BGP External</a></li>
        <li><a href="routing/bgp/internal/nographs/"><img src="images/16/brick_link.png" border="0" align="absmiddle" /> BGP Internal</a></li>');
  }

  ## Do Alerts at the bottom
  if ($bgp_alerts)
  {
    echo('
        <li><hr width=140></li>
        <li><a href="routing/bgp/alerts/"><img src="images/16/link_error.png" border="0" align="absmiddle" /> Alerted BGP (' . $bgp_alerts . ')</a></li>
   ');
  }


  echo('      </ul>
        </td></tr></table>
    </li>
    ');

}

?>

<li><a class="menu2four" href=""><img src="images/16/find.png" border="0" align="absmiddle" /> Search
<!--[if IE 7]><!--></a><!--<![endif]-->
        <table><tr><td>
        <ul>
          <li><a href="search/ipv4/"><img src="images/16/email_link.png" border="0" align="absmiddle" /> IPv4 Search</a></li>
          <li><a href="search/ipv6/"><img src="images/16/email_link.png" border="0" align="absmiddle" /> IPv6 Search</a></li>
          <li><a href="search/mac/"><img src="images/16/email_link.png" border="0" align="absmiddle" /> MAC Search</a></li>
        </ul>
      </td></tr></table>
    </li>

<li style="float: right;"><a><img src="images/16/wrench.png" border="0" align="absmiddle" /> System
<!--[if IE 7]><!--></a><!--<![endif]-->
  <table><tr><td>
    <ul>
     <li><a href="about/"><img src="images/16/information.png" border="0" align="absmiddle" /> About</a></li>
     <?php if ($_SESSION['userlevel'] >= '10') {
      echo('
        <li><hr width="140" /></li>
        <li><a href="settings/"><img src="images/16/wrench.png" border="0" align="absmiddle" /> Global Settings</a></li>');
        }
     ?>
     <li><hr width="140" /></li>
      <li><a href="preferences/"><img src="images/16/wrench_orange.png" border="0" align="absmiddle" /> My Settings</a></li>
    <?php if ($_SESSION['userlevel'] >= '10') {
      echo('
        <li><hr width="140" /></li>');

      if (auth_usermanagement())
      {
      echo('
        <li><a href="adduser/"><img src="images/16/user_add.png" border="0" align="absmiddle" /> Add User</a></li>
        <li><a href="deluser/"><img src="images/16/user_delete.png" border="0" align="absmiddle" /> Remove User</a></li>
        <li><a href="?page=edituser"><img src="images/16/user_edit.png" border="0" align="absmiddle" /> Edit User</a></li>
        <li><hr width="140" /></li>');
      }
      echo('
        <li><a href="authlog/"><img src="images/16/lock.png" border="0" align="absmiddle" /> Authlog</a></li>');
    } ?>
        </ul>
      </td></tr></table>
<!--[if lte IE 6]></a><![endif]-->
    </li>
  </ul>
</div>
