<?php

// FIXME - this could do with some performance improvements, i think. possible rearranging some tables and setting flags at poller time (nothing changes outside of then anyways)

$service_alerts = dbFetchCell("SELECT COUNT(service_id) FROM services WHERE service_status = '0'");
$if_alerts      = dbFetchCell("SELECT COUNT(port_id) FROM `ports` WHERE `ifOperStatus` = 'down' AND `ifAdminStatus` = 'up' AND `ignore` = '0'");

if (isset($config['enable_bgp']) && $config['enable_bgp'])
{
  $bgp_alerts = dbFetchCell("SELECT COUNT(bgpPeer_id) FROM bgpPeers AS B where (bgpPeerAdminStatus = 'start' OR bgpPeerAdminStatus = 'running') AND bgpPeerState != 'established'");
}

?>

<nav class="navbar navbar-default navbar-fixed-top" role="navigation">
  <div class="container-fluid">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navHeaderCollapse">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
<?php

  if ($config['title_image'])
  {
    echo('<a class="navbar-brand" href=""><img src="' . $config['title_image'] . '" /></a>');
  }
  else
  {
    echo('<a class="navbar-brand" href="">'.$config['project_name'].'</a>');
  }

?>
    </div>

    <div class="collapse navbar-collapse" id="navHeaderCollapse">
      <ul class="nav navbar-nav">
        <li class="dropdown">
          <a href="<?php echo(generate_url(array('page'=>'overview'))); ?>" class="dropdown-toggle" data-hover="dropdown" data-toggle="dropdown"><img src="images/16/lightbulb.png" border="0" align="absmiddle" /> Overview<b class="caret"></b></a>
          <ul class="dropdown-menu">
            <li><a href="<?php echo(generate_url(array('page'=>'overview'))); ?>"><img src="images/16/lightbulb.png" border="0" align="absmiddle" /> Overview</a></li>
          <li class="dropdown-submenu">
            <a href="<?php echo(generate_url(array('page'=>'alerts'))); ?>"><img src="images/16/monitor_error.png" border="0" align="absmiddle" /> Alerts</a>
            <ul class="dropdown-menu scrollable-menu">
<?php
if ($_SESSION['userlevel'] >= '10') {
?>
<li><a href="<?php echo(generate_url(array('page'=>'alerts'))); ?>"><img src="images/16/monitor_error.png" border="0" align="absmiddle" /> Alerts</a></li>
<li><a href="<?php echo(generate_url(array('page'=>'alert-rules'))); ?>"><img src="images/16/monitor_go.png" border="0" align="absmiddle" /> Alert Rules</a></li>
<li><a href="<?php echo(generate_url(array('page'=>'templates'))); ?>"><img src="images/16/monitor_link.png" border="0" align="absmiddle" /> Alert Templates</a></li>
<?php
}
?>
            </ul>
          </li>
            <li role="presentation" class="divider"></li>
<?php if (isset($config['enable_map']) && $config['enable_map']) {
  echo('              <li><a href="'.generate_url(array('page'=>'overview')).'"><img src="images/16/map.png" border="0" align="absmiddle" /> Network Map</a></li>');
} ?>
            <li><a href="<?php echo(generate_url(array('page'=>'eventlog'))); ?>"><img src="images/16/report.png" border="0" align="absmiddle" /> Eventlog</a></li>
<?php if (isset($config['enable_syslog']) && $config['enable_syslog']) {
  echo('              <li><a href="'.generate_url(array('page'=>'syslog')).'"><img src="images/16/page.png" border="0" align="absmiddle" /> Syslog</a></li>');
} ?>
<!--        <li><a href="<?php echo(generate_url(array('page'=>'alerts'))); ?>"><img src="images/16/exclamation.png" border="0" align="absmiddle" /> Alerts</a></li> -->
            <li><a href="<?php echo(generate_url(array('page'=>'inventory'))); ?>"><img src="images/16/bricks.png" border="0" align="absmiddle" /> Inventory</a></li>
            <li role="presentation" class="divider"></li>
            <li role="presentation" class="dropdown-header"> Search</li>
            <li><a href="<?php echo(generate_url(array('page'=>'search','search'=>'ipv4'))); ?>"><img src="images/icons/ipv4.png" border="0" align="absmiddle" /> IPv4 Search</a></li>
            <li><a href="<?php echo(generate_url(array('page'=>'search','search'=>'ipv6'))); ?>"><img src="images/icons/ipv6.png" border="0" align="absmiddle" /> IPv6 Search</a></li>
            <li><a href="<?php echo(generate_url(array('page'=>'search','search'=>'mac'))); ?>"><img src="images/16/email_link.png" border="0" align="absmiddle" /> MAC Search</a></li>
            <li><a href="<?php echo(generate_url(array('page'=>'search','search'=>'arp'))); ?>"><img src="images/16/email_link.png" border="0" align="absmiddle" /> ARP Tables</a></li>
          </ul>
        </li>
        <li class="dropdown">
          <a href="devices/" class="dropdown-toggle" data-hover="dropdown" data-toggle="dropdown"><img src="images/16/server.png" border="0" align="absmiddle" /> Devices<b class="caret"></b></a>
          <ul class="dropdown-menu">
            <li class="dropdown-submenu">
              <a href="devices/"><img src="images/16/server.png" border="0" align="absmiddle" /> All Devices</a>
              <ul class="dropdown-menu scrollable-menu">
<?php

foreach (dbFetchRows('SELECT `type`,COUNT(`type`) AS total_type FROM `devices` AS D WHERE 1 GROUP BY `type` ORDER BY `type`') as $devtype) {
    if (empty($devtype['type'])) {
        $devtype['type'] = 'Generic';
    }
    echo('            <li><a href="devices/type=' . $devtype['type'] . '/"><img src="images/icons/' . $devtype['type'] . '" border="0" align="absmiddle" /> ' . ucfirst($devtype['type']) . '</a></li>');
}
        echo ('</ul>
             </li>');
            echo '<li role="presentation" class="divider"></li>';

if ($_SESSION['userlevel'] >= '10') {
if ($config['show_locations'])
{

  echo('
            <li class="dropdown-submenu">
              <a href="#"><img src="images/16/building.png" border="0" align="absmiddle" /> Locations</a>
              <ul class="dropdown-menu scrollable-menu">
  ');
  if ($config['show_locations_dropdown'])
  {
    foreach (getlocations() as $location)
    {
      echo('            <li><a href="devices/location=' . urlencode($location) . '/"><img src="images/16/building.png" border="0" align="absmiddle" /> ' . $location . ' </a></li>');
    }

  }
  echo('
              </ul>
            </li>
  ');
}
  echo('
            <li><a href="addhost/"><img src="images/16/server_add.png" border="0" align="absmiddle" /> Add Device</a></li>
            <li><a href="delhost/"><img src="images/16/server_delete.png" border="0" align="absmiddle" /> Delete Device</a></li>');
}
?>

          </ul>
        </li>

<?php

if ($config['show_services'])
{
?>
        <li class="dropdown">
          <a href="services/" class="dropdown-toggle" data-hover="dropdown" data-toggle="dropdown"><img src="images/16/cog.png" border="0" align="absmiddle" /> Services<b class="caret"></b></a>
          <ul class="dropdown-menu">
            <li><a href="services/"><img src="images/16/cog.png" border="0" align="absmiddle" /> All Services </a></li>

<?php

if ($service_alerts)
{
  echo('
            <li role="presentation" class="divider"></li>
            <li><a href="services/status=0/"><img src="images/16/cog_error.png" border="0" align="absmiddle" /> Alerts ('.$service_alerts.')</a></li>');
}

if ($_SESSION['userlevel'] >= '10')
{
  echo('
            <li role="presentation" class="divider"></li>
            <li><a href="addsrv/"><img src="images/16/cog_add.png" border="0" align="absmiddle" /> Add Service</a></li>
            <li><a href="delsrv/"><img src="images/16/cog_delete.png" border="0" align="absmiddle" /> Delete Service</a></li>');
}
?>
          </ul>
        </li>
<?php
}

?>

    <!-- PORTS -->
        <li class="dropdown">
          <a href="ports/" class="dropdown-toggle" data-hover="dropdown" data-toggle="dropdown"><img src="images/16/connect.png" border="0" align="absmiddle" /> Ports<b class="caret"></b></a>
          <ul class="dropdown-menu">
            <li><a href="ports/"><img src="images/16/connect.png" border="0" align="absmiddle" /> All Ports</a></li>

<?php

if ($ports['errored'])
{
  echo('            <li><a href="ports/errors=1/"><img src="images/16/chart_curve_error.png" border="0" align="absmiddle" /> Errored ('.$ports['errored'].')</a></li>');
}

if ($ports['ignored'])
{
  echo('            <li><a href="ports/ignore=1/"><img src="images/16/chart_curve_link.png" border="0" align="absmiddle" /> Ignored ('.$ports['ignored'].')</a></li>');
}

if ($config['enable_billing']) {
  echo('            <li><a href="bills/"><img src="images/16/money.png" border="0" align="absmiddle" /> Traffic Bills</a></li>'); $ifbreak = 1;
}

if ($config['enable_pseudowires']) {
  echo('            <li><a href="pseudowires/"><img src="images/16/arrow_switch.png" border="0" align="absmiddle" /> Pseudowires</a></li>'); $ifbreak = 1;
}

?>
<?php

if ($_SESSION['userlevel'] >= '5')
{
  echo('            <li role="presentation" class="divider"></li>');
  if ($config['int_customers']) { echo('            <li><a href="customers/"><img src="images/16/group_link.png" border="0" align="absmiddle" /> Customers</a></li>'); $ifbreak = 1; }
  if ($config['int_l2tp']) { echo('            <li><a href="iftype/type=l2tp/"><img src="images/16/user.png" border="0" align="absmiddle" /> L2TP</a></li>'); $ifbreak = 1; }
  if ($config['int_transit']) { echo('            <li><a href="iftype/type=transit/"><img src="images/16/lorry_link.png" border="0" align="absmiddle" /> Transit</a></li>');  $ifbreak = 1; }
  if ($config['int_peering']) { echo('            <li><a href="iftype/type=peering/"><img src="images/16/bug_link.png" border="0" align="absmiddle" /> Peering</a></li>'); $ifbreak = 1; }
  if ($config['int_peering'] && $config['int_transit']) { echo('            <li><a href="iftype/type=peering,transit/"><img src="images/16/world_link.png" border="0" align="absmiddle" /> Peering + Transit</a></li>'); $ifbreak = 1; }
  if ($config['int_core']) { echo('            <li><a href="iftype/type=core/"><img src="images/16/brick_link.png" border="0" align="absmiddle" /> Core</a></li>'); $ifbreak = 1; }
}

if ($ifbreak) {
 echo('            <li role="presentation" class="divider"></li>');
}

if (isset($interface_alerts))
{
  echo('           <li><a href="ports/alerted=yes/"><img src="images/16/link_error.png" border="0" align="absmiddle" /> Alerts ('.$interface_alerts.')</a></li>');
}

$deleted_ports = 0;
foreach (dbFetchRows("SELECT * FROM `ports` AS P, `devices` as D WHERE P.`deleted` = '1' AND D.device_id = P.device_id") as $interface)
{
  if (port_permitted($interface['port_id'], $interface['device_id']))
  {
    $deleted_ports++;
  }
}
?>

            <li><a href="ports/state=down/"><img src="images/16/if-disconnect.png" border="0" align="absmiddle" /> Down</a></li>
            <li><a href="ports/state=admindown/"><img src="images/16/if-disable.png" border="0" align="absmiddle" /> Disabled</a></li>
<?php

if ($deleted_ports) { echo('            <li><a href="deleted-ports/"><img src="images/16/cross.png" border="0" align="absmiddle" /> Deleted ('.$deleted_ports.')</a></li>'); }

?>

          </ul>
        </li>
<?php

// FIXME does not check user permissions...
foreach (dbFetchRows("SELECT sensor_class,COUNT(sensor_id) AS c FROM sensors GROUP BY sensor_class ORDER BY sensor_class ") as $row)
{
  $used_sensors[$row['sensor_class']] = $row['c'];
}

# Copy the variable so we can use $used_sensors later in other parts of the code
$menu_sensors = $used_sensors;

?>

        <li class="dropdown">
          <a href="health/" class="dropdown-toggle" data-hover="dropdown" data-toggle="dropdown"><img src="images/icons/sensors.png" border="0" align="absmiddle" /> Health<b class="caret"></b></a>
          <ul class="dropdown-menu">
            <li><a href="health/metric=mempool/"><img src="images/icons/memory.png" border="0" align="absmiddle" /> Memory</a></li>
            <li><a href="health/metric=processor/"><img src="images/icons/processor.png" border="0" align="absmiddle" /> Processor</a></li>
            <li><a href="health/metric=storage/"><img src="images/icons/storage.png" border="0" align="absmiddle" /> Storage</a></li>
<?php
if ($menu_sensors)
{
  $sep = 0;
  echo('            <li role="presentation" class="divider"></li>');
}

foreach (array('fanspeed','humidity','temperature') as $item)
{
  if ($menu_sensors[$item])
  {
    echo('            <li><a href="health/metric='.$item.'/"><img src="images/icons/'.$item.'.png" border="0" align="absmiddle" /> '.nicecase($item).'</a></li>');
    unset($menu_sensors[$item]);$sep++;
  }
}

if ($sep)
{
  echo('          <li role="presentation" class="divider"></li>');
  $sep = 0;
}

foreach (array('current','frequency','power','voltage') as $item)
{
  if ($menu_sensors[$item])
  {
    echo('            <li><a href="health/metric='.$item.'/"><img src="images/icons/'.$item.'.png" border="0" align="absmiddle" /> '.nicecase($item).'</a></li>');
    unset($menu_sensors[$item]);$sep++;
  }
}

if ($sep && array_keys($menu_sensors))
{
  echo('            <li role="presentation" class="divider"></li>');
  $sep = 0;
}

foreach (array_keys($menu_sensors) as $item)
{
  echo('            <li><a href="health/metric='.$item.'/"><img src="images/icons/'.$item.'.png" border="0" align="absmiddle" /> '.nicecase($item).'</a></li>');
  unset($menu_sensors[$item]);$sep++;
}

?>
          </ul>
        </li>
<?php

$app_count = dbFetchCell("SELECT COUNT(`app_id`) FROM `applications`");

if ($_SESSION['userlevel'] >= '5' && ($app_count) > "0")
{
?>
        <li class="dropdown">
          <a href="apps/" class="dropdown-toggle" data-hover="dropdown" data-toggle="dropdown"><img src="images/icons/apps.png" border="0" align="absmiddle" /> Apps<b class="caret"></b></a>
          <ul class="dropdown-menu">
<?php

  $app_list = dbFetchRows("SELECT `app_type` FROM `applications` GROUP BY `app_type` ORDER BY `app_type`");
  foreach ($app_list as $app)
  {
    $image = $config['html_dir']."/images/icons/".$row['app_type'].".png";
    $icon = (file_exists($image) ? $row['app_type'] : "apps");
echo('
            <li><a href="apps/app='.$app['app_type'].'/"><img src="images/icons/'.$icon.'.png" border="0" align="absmiddle" /> '.nicecase($app['app_type']).' </a></li>');
  }
?>
          </ul>
        </li>    
<?php
}

$routing_count['bgp']  = dbFetchCell("SELECT COUNT(bgpPeer_id) from `bgpPeers` LEFT JOIN devices AS D ON bgpPeers.device_id=D.device_id WHERE D.device_id IS NOT NULL");
$routing_count['ospf'] = dbFetchCell("SELECT COUNT(ospf_instance_id) FROM `ospf_instances` WHERE `ospfAdminStat` = 'enabled'");
$routing_count['cef']  = dbFetchCell("SELECT COUNT(cef_switching_id) from `cef_switching`");
$routing_count['vrf']  = dbFetchCell("SELECT COUNT(vrf_id) from `vrfs`");

if ($_SESSION['userlevel'] >= '5' && ($routing_count['bgp']+$routing_count['ospf']+$routing_count['cef']+$routing_count['vrf']) > "0")
{

?>
        <li class="dropdown">
          <a href="routing/" class="dropdown-toggle" data-hover="dropdown" data-toggle="dropdown"><img src="images/16/arrow_branch.png" border="0" align="absmiddle" /> Routing<b class="caret"></b></a>
          <ul class="dropdown-menu">
<?php
  $separator = 0;

  if ($_SESSION['userlevel'] >= '5' && $routing_count['vrf'])
  {
    echo('            <li><a href="routing/protocol=vrf/"><img src="images/16/layers.png" border="0" align="absmiddle" /> VRFs</a></li>');
    $separator++;
  }

  if ($_SESSION['userlevel'] >= '5' && $routing_count['ospf'])
  {
    if ($separator)
    {
      echo('            <li role="presentation" class="divider"></li>');
      $separator = 0;
    }
    echo('
            <li><a href="routing/protocol=ospf/"><img src="images/16/text_letter_omega.png" border="0" align="absmiddle" /> OSPF Devices </a></li>');
    $separator++;
  }

  // BGP Sessions
  if ($_SESSION['userlevel'] >= '5' && $routing_count['bgp'])
  {
    if ($separator)
    {
      echo('            <li role="presentation" class="divider"></li>');
      $separator = 0;
    }
    echo('
            <li><a href="routing/protocol=bgp/type=all/graph=NULL/"><img src="images/16/link.png" border="0" align="absmiddle" /> BGP All Sessions </a></li>
            <li><a href="routing/protocol=bgp/type=external/graph=NULL/"><img src="images/16/world_link.png" border="0" align="absmiddle" /> BGP External</a></li>
            <li><a href="routing/protocol=bgp/type=internal/graph=NULL/"><img src="images/16/brick_link.png" border="0" align="absmiddle" /> BGP Internal</a></li>');
  }

  // Do Alerts at the bottom
  if ($bgp_alerts)
  {
    echo('
            <li role="presentation" class="divider"></li>
            <li><a href="routing/protocol=bgp/adminstatus=start/state=down/"><img src="images/16/link_error.png" border="0" align="absmiddle" /> Alerted BGP (' . $bgp_alerts . ')</a></li>
   ');
  }

  echo('          </ul>');
?>

        </li><!-- End 4 columns container -->

<?php
}

if ( dbFetchCell("SELECT 1 from `packages` LIMIT 1") ) {
?>
        <li>
          <a href="<?php echo(generate_url(array('page'=>'search','search'=>'packages'))); ?>"><img src="images/16/box.png" border="0" align="absmiddle" /> Packages</a>
        </li>
<?php
} # if ($packages)
?>

        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-hover="dropdown" data-toggle="dropdown"> <img src="images/16/package.png" border="0" alt="Plugins"> Plugins<b class="caret"></b></a>
          <ul class="dropdown-menu">
<?php
Plugins::call('menu');
?>
            <li role="presentation" class="divider"></li>
<?php
if ($_SESSION['userlevel'] >= '10')
{
  echo('            <li><a href="plugin/view=admin">Plugin Admin</a></li>');
}
?>
          </ul>
        </li>

<?php
// Custom menubar entries.
if(is_file("includes/print-menubar-custom.inc.php"))
{
  include("includes/print-menubar-custom.inc.php");
}

?>

      <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-hover="dropdown" data-toggle="dropdown"><img src="images/16/wrench.png" border="0" align="absmiddle" /> System<b class="caret"></b></a>
        <ul class="dropdown-menu">
          <li role="presentation" class="dropdown-header"> Settings</li>
          <li role="presentation" class="divider"></li>
<?php
if ($_SESSION['userlevel'] >= '10')
{
  echo('
          <li><a href="settings/"><img src="images/16/wrench.png" border="0" align="absmiddle" /> Global Settings</a></li>');
}
?>
          <li><a href="preferences/"><img src="images/16/wrench_orange.png" border="0" align="absmiddle" /> My Settings</a></li>
          <li role="presentation" class="divider"></li>
          <li role="presentation" class="dropdown-header"> Users</li>

    <?php if ($_SESSION['userlevel'] >= '10')
    {
      if (auth_usermanagement())
      {
      echo('
           <li><a href="adduser/"><img src="images/16/user_add.png" border="0" align="absmiddle" /> Add User</a></li>
           <li><a href="deluser/"><img src="images/16/user_delete.png" border="0" align="absmiddle" /> Remove User</a></li>
        ');
      }
      echo('
           <li><a href="edituser/"><img src="images/16/user_edit.png" border="0" align="absmiddle" /> Edit User</a></li>
           <li><a href="authlog/"><img src="images/16/lock.png" border="0" align="absmiddle" /> Authlog</a></li>
           <li role="presentation" class="divider"></li>
           <li class="dropdown-submenu">
           <a href="#"><img src="images/16/building.png" border="0" align="absmiddle" /> API</a>
           <ul class="dropdown-menu scrollable-menu">
             <li><a href="api-access/"><img src="images/16/script.png" /> API Settings</a></li>
             <li><a href="https://github.com/librenms/librenms/wiki/API-Docs" target="_blank"><img src="images/16/report.png" /> API Documentation</a></li>
           </ul>
           <li role="presentation" class="divider"></li>');
    } ?>
<?php
if ($_SESSION['authenticated'])
{
  echo('
           <li><a href="logout/">Logout</a></li>
');
}
?>

           <li role="presentation" class="divider"></li>
           <li><a href="about/"><img src="images/16/information.png" border="0" align="absmiddle" /> About&nbsp;<?php echo($config['project_name']); ?></a></li>
         </ul>
       </li>
     </ul>
     <form role="search" class="navbar-form navbar-left">
         <div class="form-group">
             <input class="form-control" type="search" id="gsearch" name="gsearch" placeholder="Global Search">
         </div>
     </form>
   </div>
 </div>
</nav>
<script>
  $('#gsearch').typeahead([
    {
      name: 'devices',
      remote : 'ajax_search.php?search=%QUERY&type=device',
      header : '<h5><strong>&nbsp;Devices</strong></h5>',
      template: '<a href="{{url}}"><p><img src="{{device_image}}" border="0" class="img_left"> <small><strong>{{name}}</strong> | {{device_os}} | {{version}} | {{device_hardware}} with {{device_ports}} port(s) | {{location}}</small></p></a>',
      valueKey:"name",
      engine: Hogan
    },
    {
      name: 'ports',
      remote : 'ajax_search.php?search=%QUERY&type=ports',
      header : '<h5><strong>&nbsp;Ports</strong></h5>',
      template: '<a href="{{url}}"><p><small><img src="images/icons/port.png" /> <strong>{{name}}</strong> – {{hostname}}<br /><i>{{description}}</i></small></p></a>',
      valueKey: "name",
      engine: Hogan
    },
    {
      name: 'bgp',
      remote : 'ajax_search.php?search=%QUERY&type=bgp',
      header : '<h5><strong>&nbsp;BGP</strong></h5>',
      template: '<a href="{{url}}"><p><small><img src="{{bgp_image}}" border="0" class="img_left">{{name}} - {{hostname}}<br />AS{{localas}} -> AS{{remoteas}}</small></p></a>',
      valueKey: "name",
      engine: Hogan
    }
  ]);
</script>
