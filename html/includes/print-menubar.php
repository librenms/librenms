<?php

// FIXME - this could do with some performance improvements, i think. possible rearranging some tables and setting flags at poller time (nothing changes outside of then anyways)

$service_alerts = dbFetchCell("SELECT COUNT(service_id) FROM services WHERE service_status = '0'");
$if_alerts      = dbFetchCell("SELECT COUNT(port_id) FROM `ports` WHERE `ifOperStatus` = 'down' AND `ifAdminStatus` = 'up' AND `ignore` = '0'");

if ($_SESSION['userlevel'] >= 5) {
    $links['count']        = dbFetchCell("SELECT COUNT(*) FROM `links`");
} else {
	$links['count']       = dbFetchCell("SELECT COUNT(*) FROM `links` AS `L`, `devices` AS `D`, `devices_perms` AS `P` WHERE `P`.`user_id` = ? AND `P`.`device_id` = `D`.`device_id` AND `L`.`local_device_id` = `D`.`device_id`", array($_SESSION['user_id']));
}

if (isset($config['enable_bgp']) && $config['enable_bgp'])
{
  $bgp_alerts = dbFetchCell("SELECT COUNT(bgpPeer_id) FROM bgpPeers AS B where (bgpPeerAdminStatus = 'start' OR bgpPeerAdminStatus = 'running') AND bgpPeerState != 'established'");
}

if (isset($config['site_style']) && ($config['site_style'] == 'dark' || $config['site_style'] == 'mono')) {
    $navbar = 'navbar-inverse';
}

?>

<nav class="navbar navbar-default <?php echo $navbar; ?> navbar-fixed-top" role="navigation">
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
          <a href="<?php echo(generate_url(array('page'=>'overview'))); ?>" class="dropdown-toggle" data-hover="dropdown" data-toggle="dropdown"><i class="fa fa-lightbulb-o fa-fw fa-lg fa-nav-icons"></i> Overview</a>
          <ul class="dropdown-menu">
            <li><a href="<?php echo(generate_url(array('page'=>'overview'))); ?>"><i class="fa fa-lightbulb-o fa-fw fa-lg"></i> Overview</a></li>
          <li class="dropdown-submenu">
            <a href="<?php echo(generate_url(array('page'=>'alerts'))); ?>"><i class="fa fa-exclamation-circle fa-fw fa-lg"></i> Alerts</a>
            <ul class="dropdown-menu scrollable-menu">
            <li><a href="<?php echo(generate_url(array('page'=>'alerts'))); ?>"><i class="fa fa-bell fa-fw fa-lg"></i> Alerts</a></li>
            <li><a href="<?php echo(generate_url(array('page'=>'alert-log'))); ?>"><i class="fa fa-th-list fa-fw fa-lg"></i> Alert Log</a></li>
<?php
if ($_SESSION['userlevel'] >= '10') {
?>
<li><a href="<?php echo(generate_url(array('page'=>'alert-rules'))); ?>"><i class="fa fa-tasks fa-fw fa-lg"></i> Alert Rules</a></li>
<li><a href="<?php echo(generate_url(array('page'=>'alert-map'))); ?>"><i class="fa fa-link fa-fw fa-lg"></i> Alert Map</a></li>
<li><a href="<?php echo(generate_url(array('page'=>'templates'))); ?>"><i class="fa fa-sitemap fa-fw fa-lg"></i> Alert Templates</a></li>
<?php
}
?>
            </ul>
          </li>
            <li role="presentation" class="divider"></li>
<?php if (isset($config['enable_map']) && $config['enable_map']) {
  echo('              <li><a href="'.generate_url(array('page'=>'overview')).'"><i class="fa fa-globe fa-fw fa-lg"></i> Network Map</a></li>');
} ?>
            <li><a href="<?php echo(generate_url(array('page'=>'eventlog'))); ?>"><i class="fa fa-book fa-fw fa-lg"></i> Eventlog</a></li>
<?php if (isset($config['enable_syslog']) && $config['enable_syslog']) {
  echo('              <li><a href="'.generate_url(array('page'=>'syslog')).'"><i class="fa fa-book fa-fw fa-lg"></i> Syslog</a></li>');
} ?>
            <li><a href="<?php echo(generate_url(array('page'=>'inventory'))); ?>"><i class="fa fa-cube fa-fw fa-lg"></i> Inventory</a></li>
            <li role="presentation" class="divider"></li>
            <li role="presentation" class="dropdown-header"> Search</li>
            <li><a href="<?php echo(generate_url(array('page'=>'search','search'=>'ipv4'))); ?>"><i class="fa fa-search fa-fw fa-lg"></i> IPv4 Search</a></li>
            <li><a href="<?php echo(generate_url(array('page'=>'search','search'=>'ipv6'))); ?>"><i class="fa fa-search fa-fw fa-lg"></i> IPv6 Search</a></li>
            <li><a href="<?php echo(generate_url(array('page'=>'search','search'=>'mac'))); ?>"><i class="fa fa-search fa-fw fa-lg"></i> MAC Search</a></li>
            <li><a href="<?php echo(generate_url(array('page'=>'search','search'=>'arp'))); ?>"><i class="fa fa-search fa-fw fa-lg"></i> ARP Tables</a></li>
          </ul>
        </li>
        <li class="dropdown">
          <a href="devices/" class="dropdown-toggle" data-hover="dropdown" data-toggle="dropdown"><i class="fa fa-server fa-fw fa-lg fa-nav-icons"></i> Devices</a>
          <ul class="dropdown-menu">
            <li class="dropdown-submenu">
              <a href="devices/"><i class="fa fa-server fa-fw fa-lg"></i> All Devices</a>
              <ul class="dropdown-menu scrollable-menu">
<?php

foreach (dbFetchRows('SELECT `type`,COUNT(`type`) AS total_type FROM `devices` AS D WHERE 1 GROUP BY `type` ORDER BY `type`') as $devtype) {
    if (empty($devtype['type'])) {
        $devtype['type'] = 'generic';
    }
    echo('            <li><a href="devices/type=' . $devtype['type'] . '/"><i class="fa fa-angle-double-right fa-fw fa-lg"></i> ' . ucfirst($devtype['type']) . '</a></li>');
}

require_once('../includes/device-groups.inc.php');
foreach( GetDeviceGroups() as $group ) {
	echo '<li><a href="'.generate_url(array('page'=>'devices','group'=>$group['id'])).'" alt="'.$group['desc'].'"><i class="fa fa-th fa-fw fa-lg"></i> '.ucfirst($group['name']).'</a></li>';
}
unset($group);

        echo ('</ul>
             </li>');

if ($_SESSION['userlevel'] >= '10') {
if ($config['show_locations'])
{

  echo('
            <li role="presentation" class="divider"></li>
            <li class="dropdown-submenu">
              <a href="#"><i class="fa fa-map-marker fa-fw fa-lg"></i> Locations</a>
              <ul class="dropdown-menu scrollable-menu">
  ');
  if ($config['show_locations_dropdown'])
  {
    foreach (getlocations() as $location)
    {
      echo('            <li><a href="devices/location=' . urlencode($location) . '/"><i class="fa fa-building-o fa-fw fa-lg"></i> ' . $location . ' </a></li>');
    }

  }
  echo('
              </ul>
            </li>
  ');
}
  echo('
            <li role="presentation" class="divider"></li>
            <li><a href="'.generate_url(array('page'=>'device-groups')).'"><i class="fa fa-th fa-fw fa-lg"></i> Manage Groups</a></li>
            <li><a href="addhost/"><i class="fa fa-desktop fa-col-success fa-fw fa-lg"></i> Add Device</a></li>
            <li><a href="delhost/"><i class="fa fa-desktop fa-col-info fa-fw fa-lg"></i> Delete Device</a></li>');
}

if ($links['count'] > 0) {

?>
              <li role="presentation" class="divider"></li>
              <li><a href="map/"><img src="images/16/chart_organisation.png" border="0" alt="Network Map" width="16" height="16" /> Network Map</a></li>
<?php

}

?>

          </ul>
        </li>

<?php

if ($config['show_services'])
{
?>
        <li class="dropdown">
          <a href="services/" class="dropdown-toggle" data-hover="dropdown" data-toggle="dropdown"><i class="fa fa-cogs fa-fw fa-lg fa-nav-icons"></i> Services</a>
          <ul class="dropdown-menu">
            <li><a href="services/"><i class="fa fa-cogs fa-fw fa-lg"></i> All Services </a></li>

<?php

if ($service_alerts)
{
  echo('
            <li role="presentation" class="divider"></li>
            <li><a href="services/status=0/"><i class="fa fa-bell-o fa-fw fa-lg"></i> Alerts ('.$service_alerts.')</a></li>');
}

if ($_SESSION['userlevel'] >= '10')
{
  echo('
            <li role="presentation" class="divider"></li>
            <li><a href="addsrv/"><i class="fa fa-cog fa-col-success fa-fw fa-lg"></i> Add Service</a></li>
            <li><a href="delsrv/"><i class="fa fa-cog fa-col-info fa-fw fa-lg"></i> Delete Service</a></li>');
}
?>
          </ul>
        </li>
<?php
}

?>

    <!-- PORTS -->
        <li class="dropdown">
          <a href="ports/" class="dropdown-toggle" data-hover="dropdown" data-toggle="dropdown"><i class="fa fa-link fa-fw fa-lg fa-nav-icons"></i> Ports</a>
          <ul class="dropdown-menu">
            <li><a href="ports/"><i class="fa fa-link fa-fw fa-lg"></i> All Ports</a></li>

<?php

if (isset($ports['errored']))
{
  echo('            <li><a href="ports/errors=1/"><i class="fa fa-exclamation-circle fa-fw fa-lg"></i> Errored ('.$ports['errored'].')</a></li>');
}

if (isset($ports['ignored']))
{
  echo('            <li><a href="ports/ignore=1/"><i class="fa fa-question-circle fa-fw fa-lg"></i> Ignored ('.$ports['ignored'].')</a></li>');
}

if ($config['enable_billing']) {
  echo('            <li><a href="bills/"><i class="fa fa-money fa-fw fa-lg"></i> Traffic Bills</a></li>'); $ifbreak = 1;
}

if ($config['enable_pseudowires']) {
  echo('            <li><a href="pseudowires/"><i class="fa fa-arrows-alt fa-fw fa-lg"></i> Pseudowires</a></li>'); $ifbreak = 1;
}

?>
<?php

if ($_SESSION['userlevel'] >= '5')
{
  echo('            <li role="presentation" class="divider"></li>');
  if ($config['int_customers']) { echo('            <li><a href="customers/"><i class="fa fa-users fa-fw fa-lg"></i> Customers</a></li>'); $ifbreak = 1; }
  if ($config['int_l2tp']) { echo('            <li><a href="iftype/type=l2tp/"><i class="fa fa-link fa-fw fa-lg"></i> L2TP</a></li>'); $ifbreak = 1; }
  if ($config['int_transit']) { echo('            <li><a href="iftype/type=transit/"><i class="fa fa-truck fa-fw fa-lg"></i> Transit</a></li>');  $ifbreak = 1; }
  if ($config['int_peering']) { echo('            <li><a href="iftype/type=peering/"><i class="fa fa-user-plus fa-fw fa-lg"></i> Peering</a></li>'); $ifbreak = 1; }
  if ($config['int_peering'] && $config['int_transit']) { echo('            <li><a href="iftype/type=peering,transit/"><i class="fa fa-user-secret fa-fw fa-lg"></i> Peering + Transit</a></li>'); $ifbreak = 1; }
  if ($config['int_core']) { echo('            <li><a href="iftype/type=core/"><i class="fa fa-anchor fa-fw fa-lg"></i> Core</a></li>'); $ifbreak = 1; }
}

if ($ifbreak) {
 echo('            <li role="presentation" class="divider"></li>');
}

if (isset($interface_alerts))
{
  echo('           <li><a href="ports/alerted=yes/"><i class="fa fa-exclamation-circle fa-fw fa-lg"></i> Alerts ('.$interface_alerts.')</a></li>');
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

            <li><a href="ports/state=down/"><i class="fa fa-chain-broken fa-col-success fa-fw fa-lg"></i> Down</a></li>
            <li><a href="ports/state=admindown/"><i class="fa fa-chain-broken fa-col-info fa-fw fa-lg"></i> Disabled</a></li>
<?php

if ($deleted_ports) { echo('            <li><a href="deleted-ports/"><i class="fa fa-minus-circle fa-col-primary fa-fw fa-lg"></i> Deleted ('.$deleted_ports.')</a></li>'); }

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
          <a href="health/" class="dropdown-toggle" data-hover="dropdown" data-toggle="dropdown"><i class="fa fa-heartbeat fa-fw fa-lg fa-nav-icons"></i> Health</a>
          <ul class="dropdown-menu">
            <li><a href="health/metric=mempool/"><i class="fa fa-gears fa-fw fa-lg"></i> Memory</a></li>
            <li><a href="health/metric=processor/"><i class="fa fa-desktop fa-fw fa-lg"></i> Processor</a></li>
            <li><a href="health/metric=storage/"><i class="fa fa-database fa-fw fa-lg"></i> Storage</a></li>
<?php
if ($menu_sensors)
{
  $sep = 0;
  echo('            <li role="presentation" class="divider"></li>');
}

$icons = array('fanspeed'=>'tachometer','humidity'=>'tint','temperature'=>'fire','current'=>'bolt','frequency'=>'line-chart','power'=>'power-off','voltage'=>'bolt');
foreach (array('fanspeed','humidity','temperature') as $item)
{
  if (isset($menu_sensors[$item]))
  {
    echo('            <li><a href="health/metric='.$item.'/"><i class="fa fa-'.$icons[$item].' fa-fw fa-lg"></i> '.nicecase($item).'</a></li>');
    unset($menu_sensors[$item]);$sep++;
  }
}

if ($sep && array_keys($menu_sensors))
{
  echo('          <li role="presentation" class="divider"></li>');
  $sep = 0;
}

foreach (array('current','frequency','power','voltage') as $item)
{
  if (isset($menu_sensors[$item]))
  {
    echo('            <li><a href="health/metric='.$item.'/"><i class="fa fa-'.$icons[$item].' fa-fw fa-lg"></i> '.nicecase($item).'</a></li>');
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
  echo('            <li><a href="health/metric='.$item.'/"><i class="fa fa-'.$icons[$item].' fa-fw fa-lg"></i> '.nicecase($item).'</a></li>');
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
          <a href="apps/" class="dropdown-toggle" data-hover="dropdown" data-toggle="dropdown"><i class="fa fa-tasks fa-fw fa-lg fa-nav-icons"></i> Apps</a>
          <ul class="dropdown-menu">
<?php

  $app_list = dbFetchRows("SELECT `app_type` FROM `applications` GROUP BY `app_type` ORDER BY `app_type`");
  foreach ($app_list as $app)
  {
      if (isset($app['app_type'])) {
          $image = $config['html_dir']."/images/icons/".$app['app_type'].".png";
          $icon = (file_exists($image) ? $app['app_type'] : "apps");
echo('
          <li><a href="apps/app='.$app['app_type'].'/"><i class="fa fa-angle-double-right fa-fw fa-lg"></i> '.nicecase($app['app_type']).' </a></li>');
      }
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
          <a href="routing/" class="dropdown-toggle" data-hover="dropdown" data-toggle="dropdown"><i class="fa fa-arrows fa-fw fa-lg fa-nav-icons"></i> Routing</a>
          <ul class="dropdown-menu">
<?php
  $separator = 0;

  if ($_SESSION['userlevel'] >= '5' && $routing_count['vrf'])
  {
    echo('            <li><a href="routing/protocol=vrf/"><i class="fa fa-arrows-alt fa-fw fa-lg"></i> VRFs</a></li>');
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
            <li><a href="routing/protocol=ospf/"><i class="fa fa-circle-o-notch fa-rotate-180 fa-fw fa-lg"></i> OSPF Devices </a></li>');
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
            <li><a href="routing/protocol=bgp/type=all/graph=NULL/"><i class="fa fa-link fa-fw fa-lg"></i> BGP All Sessions </a></li>
            <li><a href="routing/protocol=bgp/type=external/graph=NULL/"><i class="fa fa-external-link fa-fw fa-lg"></i> BGP External</a></li>
            <li><a href="routing/protocol=bgp/type=internal/graph=NULL/"><i class="fa fa-external-link fa-rotate-180 fa-fw fa-lg"></i> BGP Internal</a></li>');
  }

  // Do Alerts at the bottom
  if ($bgp_alerts)
  {
    echo('
            <li role="presentation" class="divider"></li>
            <li><a href="routing/protocol=bgp/adminstatus=start/state=down/"><i class="fa fa-exclamation-circle fa-fw fa-lg"></i> Alerted BGP (' . $bgp_alerts . ')</a></li>
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
          <a href="<?php echo(generate_url(array('page'=>'search','search'=>'packages'))); ?>"><i class="fa fa-archive fa-fw fa-lg"></i> Packages</a>
        </li>
<?php
} # if ($packages)
?>

        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-hover="dropdown" data-toggle="dropdown"><i class="fa fa-plug fa-fw fa-lg fa-nav-icons"></i> Plugins</a>
          <ul class="dropdown-menu">
<?php
Plugins::call('menu');

if ($_SESSION['userlevel'] >= '10')
{
  echo(' 
            <li role="presentation" class="divider"></li>
            <li><a href="plugin/view=admin"> <i class="fa fa-lock fa-fw fa-lg"></i>Plugin Admin</a></li>
  ');
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

    </ul>
     <form role="search" class="navbar-form navbar-right">
         <div class="form-group">
             <input class="form-control" type="search" id="gsearch" name="gsearch" placeholder="Global Search">
         </div>
     </form>
    <ul class="nav navbar-nav navbar-right">
      <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-hover="dropdown" data-toggle="dropdown"><i class="fa fa-cog fa-fw fa-lg fa-nav-icons"></i></a>
        <ul class="dropdown-menu">
          <li role="presentation" class="dropdown-header"> Settings</li>
          <li role="presentation" class="divider"></li>
<?php
if ($_SESSION['userlevel'] >= '10')
{
  echo('
          <li><a href="settings/"><i class="fa fa-cogs fa-fw fa-lg"></i> Global Settings</a></li>');
}
?>
          <li><a href="preferences/"><i class="fa fa-cog fa-fw fa-lg"></i> My Settings</a></li>
          <li role="presentation" class="divider"></li>
          <li role="presentation" class="dropdown-header"> Users</li>

    <?php if ($_SESSION['userlevel'] >= '10')
    {
      if (auth_usermanagement())
      {
      echo('
           <li><a href="adduser/"><i class="fa fa-user-plus fa-fw fa-lg"></i> Add User</a></li>
           <li><a href="deluser/"><i class="fa fa-user-times fa-fw fa-lg"></i> Remove User</a></li>
        ');
      }
      echo('
           <li><a href="edituser/"><i class="fa fa-user-secret fa-fw fa-lg"></i> Edit User</a></li>
           <li><a href="authlog/"><i class="fa fa-key fa-fw fa-lg"></i> Authlog</a></li>
           <li role="presentation" class="divider"></li>
');
          echo('
           <li class="dropdown-submenu">
               <a href="#"><i class="fa fa-clock-o fa-fw fa-lg"></i> Pollers</a>
               <ul class="dropdown-menu scrollable-menu">
                    <li><a href="/poll-log/"><i class="fa fa-exclamation fa-fw fa-lg"></i> Poll-log</a></li>

            ');
          if($config['distributed_poller'] === TRUE) {
            echo ('
                    <li><a href="/pollers/tab=pollers/"><i class="fa fa-clock-o fa-fw fa-lg"></i> Pollers</a></li>
                    <li><a href="/pollers/tab=groups/"><i class="fa fa-gears fa-fw fa-lg"></i> Groups</a></li>
              ');
            }
            echo ('
               </ul>
           </li>
           <li role="presentation" class="divider"></li>
           ');

echo('
           <li class="dropdown-submenu">
           <a href="#"><i class="fa fa-code fa-fw fa-lg"></i> API</a>
           <ul class="dropdown-menu scrollable-menu">
             <li><a href="api-access/"><i class="fa fa-wrench fa-fw fa-lg"></i> API Settings</a></li>
             <li><a href="http://docs.librenms.org/API/API-Docs/" target="_blank"><i class="fa fa-book fa-fw fa-lg"></i> API Documentation</a></li>
           </ul>
           </li>
           <li role="presentation" class="divider"></li>');
    } ?>
<?php
if ($_SESSION['authenticated'])
{
  echo('
           <li class="dropdown-submenu">
               <a href="#"><span class="countdown_timer" id="countdown_timer"></span></a>
               <ul class="dropdown-menu scrollable-menu">
                   <li><a href="#"><span class="countdown_timer_status" id="countdown_timer_status"></span></a></li>
               </ul>
           </li>
           <li><a href="logout/"><i class="fa fa-sign-out fa-fw fa-lg"></i> Logout</a></li>
');
}
?>

           <li role="presentation" class="divider"></li>
           <li><a href="about/"><i class="fa fa-exclamation-circle fa-fw fa-lg"></i> About&nbsp;<?php echo($config['project_name']); ?></a></li>
         </ul>
       </li>
     </ul>
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
