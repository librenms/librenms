<?php

$service_alerts = dbFetchCell("SELECT COUNT(service_id) FROM services WHERE service_status = '0'");
$if_alerts      = dbFetchCell("SELECT COUNT(interface_id) FROM `ports` WHERE `ifOperStatus` = 'down' AND `ifAdminStatus` = 'up' AND `ignore` = '0'");

$device_alerts  = "0";
$device_alert_sql = "WHERE 0";

if (isset($config['enable_bgp']) && $config['enable_bgp'])
{
  $bgp_alerts = dbFetchCell("SELECT COUNT(*) FROM bgpPeers AS B where (bgpPeerAdminStatus = 'start' OR bgpPeerAdminStatus = 'running') AND bgpPeerState != 'established'");
}

foreach (dbFetchRows("SELECT * FROM `devices`") as $device)
{
  $this_alert = 0;
  if ($device['status'] == 0 && $device['ignore'] == '0') { $this_alert = "1"; } elseif ($device['ignore'] == '0')
  {

  ## sluggish. maybe we cache this at poll-time?

#    if (dbFetchCell("SELECT count(service_id) FROM services WHERE service_status = '0' AND device_id = ?", array($device['device_id']))) { $this_alert = "1"; }
#    if (dbFetchCell("SELECT count(*) FROM ports WHERE `ifOperStatus` = 'down' AND `ifAdminStatus` = 'up' AND device_id = ? AND `ignore` = '0'", array($device['device_id']))) { $this_alert = "1"; }
  }
  if ($this_alert)
  {
   $device_alerts++;
   $device_alert_sql .= " OR `device_id` = '" . $device['device_id'] . "'";
  }

  $cache['devices'][$device['hostname']] = $device;
}
?>

<ul id="menium">

    <li><a href="<?php echo(generate_url(array('page'=>'overview'))); ?>" class="drop"><img src="images/16/lightbulb.png" border="0" align="absmiddle" /> Overview</a>
        <div class="dropdown_1column">
            <div class="col_1">
        <ul>
        <?php if (isset($config['enable_map']) && $config['enable_map']) {
          echo('<li><a href="'.generate_url(array('page'=>'overview')).'"><img src="images/16/map.png" border="0" align="absmiddle" /> Network Map</a></li>');
        } ?>
        <li><a href="<?php echo(generate_url(array('page'=>'eventlog'))); ?>"><img src="images/16/report.png" border="0" align="absmiddle" /> Eventlog</a></li>
  <?php if (isset($config['enable_syslog']) && $config['enable_syslog']) {
      echo('<li><a href="'.generate_url(array('page'=>'syslog')).'"><img src="images/16/page.png" border="0" align="absmiddle" /> Syslog</a></li>');
  } ?>
<!--        <li><a href="<?php echo(generate_url(array('page'=>'alerts'))); ?>"><img src="images/16/exclamation.png" border="0" align="absmiddle" /> Alerts</a></li> -->
        <li><a href="<?php echo(generate_url(array('page'=>'inventory'))); ?>"><img src="images/16/bricks.png" border="0" align="absmiddle" /> Inventory</a></li>
        </ul>
            </div>

            <div class="col_1">
              <h3>Search</h3>
            </div>

            <div class="col_1">
        <ul>
          <li><a href="<?php echo(generate_url(array('page'=>'search','search'=>'ipv4'))); ?>"><img src="images/icons/ipv4.png" border="0" align="absmiddle" /> IPv4 Search</a></li>
          <li><a href="<?php echo(generate_url(array('page'=>'search','search'=>'ipv6'))); ?>"><img src="images/icons/ipv6.png" border="0" align="absmiddle" /> IPv6 Search</a></li>
          <li><a href="<?php echo(generate_url(array('page'=>'search','search'=>'mac'))); ?>"><img src="images/16/email_link.png" border="0" align="absmiddle" /> MAC Search</a></li>
        </ul>
            </div>


        </div>

    </li>

    <li><a href="devices/" class="drop"><img src="images/16/server.png" border="0" align="absmiddle" /> Devices</a>

     <div class="dropdown_4columns"><!-- Begin 4 columns container -->
      <div class="col_1">
        <ul>
          <li><a href="devices/"><img src="images/16/server.png" border="0" align="absmiddle" /> All Devices</a></li>
          <li><hr width="140" /></li>

<?php

foreach ($config['device_types'] as $devtype)
{
  echo('        <li><a href="devices/type=' . $devtype['type'] . '/"><img src="images/icons/' . $devtype['icon'] . '" border="0" align="absmiddle" /> ' . $devtype['text'] . '</a></li>');
}

?>
<?php
if ($_SESSION['userlevel'] >= '10') {
  echo('
        <li><hr width="140" /></li>
        <li><a href="addhost/"><img src="images/16/server_add.png" border="0" align="absmiddle" /> Add Device</a></li>
        <li><a href="delhost/"><img src="images/16/server_delete.png" border="0" align="absmiddle" /> Delete Device</a></li>');
}
?>

          </ul>

       </div>

       <div id="devices_chart" class="col_3" style="height: 300px";>
       </div>

<script class="code" type="text/javascript">
$(document).ready(function() {
  var data = [
    ['Up', <?php echo($devices['up']); ?>],
    ['Down', <?php echo($devices['down']); ?>],
    ['Ignored', <?php echo($devices['ignored']); ?>],
    ['Disabled', <?php echo($devices['disabled']); ?>]
  ];
  var plot1 = jQuery.jqplot ('devices_chart', [data],
    {
      seriesDefaults: {
        renderer: jQuery.jqplot.PieRenderer,
        rendererOptions: {
          // Turn off filling of slices.
          fill: true,
          showDataLabels: true,
          // Add a margin to seperate the slices.
          sliceMargin: 0,
          // stroke the slices with a little thicker line.
          lineWidth: 5
        }
      },
      legend: { show:true, location: 'e' }
    }
  );
});
</script>
      </div>

    </li><!-- End 5 columns Item -->

<?php

if ($config['show_services'])
{
?>

    <li><a href="services/" class="drop"><img src="images/16/cog.png" border="0" align="absmiddle" /> Services</a><!-- Begin 4 columns Item -->

        <div class="dropdown_4columns"><!-- Begin 4 columns container -->

            <div class="col_1">
<ul>
        <li><a href="services/"><img src="images/16/cog.png" border="0" align="absmiddle" /> All Services </a></li>

<?php if ($service_alerts) {
echo('  <li><hr width=140 /></li>
        <li><a href="services/status=0/"><img src="images/16/cog_error.png" border="0" align="absmiddle" /> Alerts ('.$service_alerts.')</a></li>');
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
        </div>

       <div id="services_chart" class="col_3" style="height: 300px";>
       </div>

<script class="code" type="text/javascript">
$(document).ready(function() {
  var data = [
    ['Up', <?php echo($services['up']); ?>],
    ['Down', <?php echo($services['down']); ?>],
  ];
  var plot2 = jQuery.jqplot ('services_chart', [data],
    {
      seriesDefaults: {
        renderer: jQuery.jqplot.PieRenderer,
        rendererOptions: {
          // Turn off filling of slices.
          fill: true,
          showDataLabels: true,
          // Add a margin to seperate the slices.
          sliceMargin: 0,
          // stroke the slices with a little thicker line.
          lineWidth: 5
        }
      },
      legend: { show:true, location: 'e' }
    }
  );
});
</script>


        </div><!-- End 4 columns container -->

    </li><!-- End 4 columns Item -->

<?php
}

if ($config['show_locations'])
{
?>
    <li><a href="locations/" class="drop"><img src="images/16/building.png" border="0" align="absmiddle" /> Locations</a><!-- Begin Home Item -->

<?php
  if ($config['show_locations_dropdown'])
  {
?>
        <div class="dropdown_2columns"><!-- Begin 2 columns container -->
            <div class="col_2">

        <ul>
<?php
    foreach (getlocations() as $location)
    {
      echo('        <li><a href="devices/location=' . urlencode($location) . '/"><img src="images/16/building.png" border="0" align="absmiddle" /> ' . $location . ' </a></li>');
    }
?>
        </ul>

<?php
}
?>
            </div>
        </div><!-- End 4 columns container -->
    </li><!-- End 4 columns Item -->

<?php
}
?>



    <!-- PORTS -->

    <li><a href="ports/" class="drop"><img src="images/16/connect.png" border="0" align="absmiddle" /> Ports</a><!-- Begin Home Item -->

        <div class="dropdown_4columns"><!-- Begin 2 columns container -->

          <div class="col_1">
             <ul>
<li><a href="ports/"><img src="images/16/connect.png" border="0" align="absmiddle" /> All Ports</a></li>

<?php

if ($ports['errored'])
{
  echo('<li><a href="ports/errors=1/"><img src="images/16/chart_curve_error.png" border="0" align="absmiddle" /> Errored ('.$ports['errored'].')</a></li>');
}

if ($ports['ignored'])
{
  echo('<li><a href="ports/ignore=1/"><img src="images/16/chart_curve_link.png" border="0" align="absmiddle" /> Ignored ('.$ports['ignored'].')</a></li>');
}

if ($config['enable_billing']) { echo('<li><a href="bills/"><img src="images/16/money.png" border="0" align="absmiddle" /> Traffic Bills</a></li>'); $ifbreak = 1; }

if ($config['enable_pseudowires']) { echo('<li><a href="pseudowires/"><img src="images/16/arrow_switch.png" border="0" align="absmiddle" /> Pseudowires</a></li>'); $ifbreak = 1; }

?>
<?php

if ($_SESSION['userlevel'] >= '5')
{
  echo('<li><hr width="140" /></li>');
  if ($config['int_customers']) { echo('<li><a href="customers/"><img src="images/16/group_link.png" border="0" align="absmiddle" /> Customers</a></li>'); $ifbreak = 1; }
  if ($config['int_l2tp']) { echo('<li><a href="iftype/type=l2tp/"><img src="images/16/user.png" border="0" align="absmiddle" /> L2TP</a></li>'); $ifbreak = 1; }
  if ($config['int_transit']) { echo('<li><a href="iftype/type=transit/"><img src="images/16/lorry_link.png" border="0" align="absmiddle" /> Transit</a></li>');  $ifbreak = 1; }
  if ($config['int_peering']) { echo('<li><a href="iftype/type=peering/"><img src="images/16/bug_link.png" border="0" align="absmiddle" /> Peering</a></li>'); $ifbreak = 1; }
  if ($config['int_peering'] && $config['int_transit']) { echo('<li><a href="iftype/type=peering,transit/"><img src="images/16/world_link.png" border="0" align="absmiddle" /> Peering + Transit</a></li>'); $ifbreak = 1; }
  if ($config['int_core']) { echo('<li><a href="iftype/type=core/"><img src="images/16/brick_link.png" border="0" align="absmiddle" /> Core</a></li>'); $ifbreak = 1; }
}

if ($ifbreak) { echo('<li><hr width="140" /></li>'); }

if (isset($interface_alerts))
{
  echo('<li><a href="ports/alerted=yes/"><img src="images/16/link_error.png" border="0" align="absmiddle" /> Alerts ('.$interface_alerts.')</a></li>');
}

$deleted_ports = 0;
foreach (dbFetchRows("SELECT * FROM `ports` AS P, `devices` as D WHERE P.`deleted` = '1' AND D.device_id = P.device_id") as $interface)
{
  if (port_permitted($interface['interface_id'], $interface['device_id']))
  {
    $deleted_ports++;
  }
}
?>

<li><a href="ports/state=down/"><img src="images/16/if-disconnect.png" border="0" align="absmiddle" /> Down</a></li>
<li><a href="ports/state=admindown/"><img src="images/16/if-disable.png" border="0" align="absmiddle" /> Disabled</a></li>
<?php

if ($deleted_ports) { echo('<li><a href="deleted-ports/"><img src="images/16/cross.png" border="0" align="absmiddle" /> Deleted ('.$deleted_ports.')</a></li>'); }

?>
</ul>
          </div>

          <div id="ports_chart" class="col_3" style="height: 300px";>
          </div>

<script class="code" type="text/javascript">
$(document).ready(function() {
  var data = [
    ['Up', <?php echo($ports['up']); ?>],
    ['Down', <?php echo($ports['down']); ?>],
    ['Shutdown', <?php echo($ports['admindown']); ?>],
    ['Ignored', <?php echo($ports['ignored']); ?>],
    ['Deleted', <?php echo($ports['deleted']); ?>]
  ];
  var plot3 = jQuery.jqplot ('ports_chart', [data],
    {
      seriesDefaults: {
        renderer: jQuery.jqplot.PieRenderer,
        rendererOptions: {
          // Turn off filling of slices.
          fill: true,
          showDataLabels: true,
          // Add a margin to seperate the slices.
          sliceMargin: 0,
          // stroke the slices with a little thicker line.
          lineWidth: 5
        }
      },
      legend: { show:true, location: 'e' }
    }
  );
});
</script>


        </div><!-- End 4 columns container -->

    </li><!-- End 4 columns Item -->


<?php

# FIXME does not check user permissions...
foreach (dbFetchRows("SELECT sensor_class,COUNT(sensor_id) AS c FROM sensors GROUP BY sensor_class ORDER BY sensor_class ") as $row)
{
  $used_sensors[$row['sensor_class']] = $row['c'];
}

# Copy the variable so we can use $used_sensors later in other parts of the code
$menu_sensors = $used_sensors;

?>

    <li><a href="health/" class="drop"><img src="images/icons/sensors.png" border="0" align="absmiddle" /> Health</a><!-- Begin Home Item -->

        <div class="dropdown_1column"><!-- Begin 2 columns container -->
            <div class="col_1">

<ul>
        <li><a href="health/metric=mempool/"><img src="images/icons/memory.png" border="0" align="absmiddle" /> Memory</a></li>
        <li><a href="health/metric=processor/"><img src="images/icons/processor.png" border="0" align="absmiddle" /> Processor</a></li>
        <li><a href="health/metric=storage/"><img src="images/icons/storage.png" border="0" align="absmiddle" /> Storage</a></li>
<?php
if ($menu_sensors)
{
  $sep = 0;
  echo('<li><hr width="97%" /></li>');
}

foreach (array('fanspeed','humidity','temperature') as $item)
{
  if ($menu_sensors[$item])
  {
    echo ('<li><a href="health/metric='.$item.'/"><img src="images/icons/'.$item.'.png" border="0" align="absmiddle" /> '.ucfirst($item).'</a></li>');
    unset($menu_sensors[$item]);$sep++;
  }
}

if ($sep)
{
  echo('<li><hr width="97%" /></li>');
  $sep = 0;
}

foreach (array('current','frequency','power','voltage') as $item)
{
  if ($menu_sensors[$item])
  {
    echo ('<li><a href="health/metric='.$item.'/"><img src="images/icons/'.$item.'.png" border="0" align="absmiddle" /> '.ucfirst($item).'</a></li>');
    unset($menu_sensors[$item]);$sep++;
  }
}

if ($sep && array_keys($menu_sensors))
{
  echo('<li><hr width="97%" /></li>');
  $sep = 0;
}

foreach (array_keys($menu_sensors) as $item)
{
  echo ('<li><a href="health/metric='.$item.'/"><img src="images/icons/'.$item.'.png" border="0" align="absmiddle" /> '.ucfirst($item).'</a></li>');
  unset($menu_sensors[$item]);$sep++;
}

?>
        </ul>

            </div>

        </div><!-- End 4 columns container -->

    </li><!-- End 4 columns Item -->

<?php

$routing_count['bgp']  = dbFetchCell("SELECT COUNT(*) from `bgpPeers`");
$routing_count['ospf'] = dbFetchCell("SELECT COUNT(*) FROM `ospf_instances` WHERE `ospfAdminStat` = 'enabled'");
$routing_count['cef']  = dbFetchCell("SELECT COUNT(*) from `cef_switching`");
$routing_count['vrf']  = dbFetchCell("SELECT COUNT(*) from `vrfs`");

if ($_SESSION['userlevel'] >= '5' && ($routing_count['bgp']+$routing_count['ospf']+$routing_count['cef']+$routing_count['vrf']) > "0")
{

?>

    <li><a href="routing/" class="drop"><img src="images/16/arrow_branch.png" border="0" align="absmiddle" /> Routing</a><!-- Begin Home Item -->

        <div class="dropdown_1column"><!-- Begin 1 column container -->

          <ul>

<?php
  if ($_SESSION['userlevel'] >= '5' && $routing_count['vrf']) { echo('<li><a href="routing/vrf/"><img src="images/16/layers.png" border="0" align="absmiddle" /> VRFs</a></li> <li><hr width=140></li> '); }

  if ($_SESSION['userlevel'] >= '5' && $routing_count['ospf'])
  {
    echo('
        <li><a href="routing/ospf/all/nographs/"><img src="images/16/text_letter_omega.png" border="0" align="absmiddle" /> OSPF Devices </a></li>
        <li><hr width=140></li>
        ');
  }


  ## BGP Sessions
  if ($_SESSION['userlevel'] >= '5' && $routing_count['bgp'])
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


  echo('      </ul>');
?>

        </div><!-- End 4 columns container -->

    </li><!-- End 4 columns Item -->

<?php
}
?>


    <li class="menu_right"><a href="#" class="drop"><img src="images/16/wrench.png" border="0" align="absmiddle" /> System</a><!-- Begin Home Item -->

        <div class="dropdown_3columns align_right"><!-- Begin 2 columns container -->


            <div class="col_3">
                <h2>Observium <?php echo($config['version']); ?> </h2>
            </div>


            <div class="col_2">
                <p>Network Management and Monitoring<br />
                Copyright (C) 2006-<?php echo date("Y"); ?> Adam Armstrong</p>
            </div>

            <div class="col_1">
              <p>
                <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=W2ZJ3JRZR72Z6" class="external text" rel="nofollow">
                <img src="https://www.paypalobjects.com/en_GB/i/btn/btn_donate_LG.gif" alt="btn_donateCC_LG.gif" />
                </a>
              </p>
            </div>

            <div class="col_3">
              <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=W2ZJ3JRZR72Z6" class="external text" rel="nofollow">
                Please donate to support continued development!
              </a>
            </div>

            <div class="col_2">
                <h2>The Team</h2>
                <p>
                  <img src="images/icons/flags/gb.png"> <strong>Adam Armstrong</strong> Project Founder<br />
                  <img src="images/icons/flags/be.png"> <strong>Geert Hauwaerts</strong> Developer<br />
                  <img src="images/icons/flags/be.png"> <strong>Tom Laermans</strong> Developer<br />
                </p>
            </div>


            <div class="col_1">
                <h2>Settings</h2>
<ul>
     <li><a href="about/"><img src="images/16/information.png" border="0" align="absmiddle" /> About</a></li>
     <?php if ($_SESSION['userlevel'] >= '10') {
      echo('
        <li><a href="settings/"><img src="images/16/wrench.png" border="0" align="absmiddle" /> Global Settings</a></li>');
        }
     ?>
      <li><a href="preferences/"><img src="images/16/wrench_orange.png" border="0" align="absmiddle" /> My Settings</a></li>
        </ul>
            </div>


<?php
$apache_version = str_replace("Apache/", "", $_SERVER['SERVER_SOFTWARE']);
$php_version = phpversion();
$mysql_version = dbFetchCell("SELECT version()");
$netsnmp_version = shell_exec($config['snmpget'] . " --version");
?>

            <div class="col_2">
                <h2>Versions</h2>
                <p>
<?php echo("                     <table width=100% cellpadding=3 cellspacing=0 border=0>
      <tr valign=top><td><b>Apache</b></td><td>$apache_version</td></tr>
      <tr valign=top><td><b>PHP</b></td><td>$php_version</td></tr>
      <tr valign=top><td><b>MySQL</b></td><td>$mysql_version</td></tr>
    </table>");
?>
                </p>
                <ul>
                  <li><a href="about/"><img src="images/16/information.png" border="0" align="absmiddle" /> About Observium</a></li>
                </ul>
            </div>

<div class="col_1">
                <h2>Users</h2>
<ul>

    <?php if ($_SESSION['userlevel'] >= '10') {
      if (auth_usermanagement())
      {
      echo('
        <li><a href="adduser/"><img src="images/16/user_add.png" border="0" align="absmiddle" /> Add User</a></li>
        <li><a href="deluser/"><img src="images/16/user_delete.png" border="0" align="absmiddle" /> Remove User</a></li>
        ');
      }
      echo('
        <li><a href="edituser/"><img src="images/16/user_edit.png" border="0" align="absmiddle" /> Edit User</a></li>
        <li><a href="authlog/"><img src="images/16/lock.png" border="0" align="absmiddle" /> Authlog</a></li>');
    } ?>



        </ul>
            </div>


        </div><!-- End 2 columns container -->

    </li><!-- End Home Item -->

</ul>
