<?php

if ($_GET['id']) { $_GET['id'] = mres($_GET['id']); }

if ($_GET['section'] == "interface" && is_numeric($_GET['opta']) && port_permitted($_GET['opta']))
{
  $check_device = get_device_id_by_interface_id($_GET['opta']);
  $permit_ports = 1;
}

if (device_permitted($_GET['id']) || $check_device == $_GET['id'])
{
  $selected['iface'] = "selected";

  $section = str_replace(".", "", mres($_GET['section']));

  if (!$section)
  {
    $section = "overview";
  }

  $select[$section] = "selected";

  $device = device_by_id_cache($_GET['id']);
  if ($config['os'][$device['os']]['group']) { $device['os_group'] = $config['os'][$device['os']]['group']; }

  echo('<table cellpadding="15" cellspacing="0" class="devicetable" width="100%">');
  include("includes/device-header.inc.php");
  echo('</table>');

  echo('<div class="mainpane">');
  echo('  <ul id="maintab" class="shadetabs">');

  if (device_permitted($device['device_id']))
  {
    if ($config['show_overview_tab'])
    {
      echo('
  <li class="' . $select['overview'] . '">
    <a href="'.$config['base_url'].'/device/' . $device['device_id'] . '/overview/">
      <img src="images/16/server_lightning.png" align="absmiddle" border="0"> Overview
    </a>
  </li>');
    }

    echo('<li class="' . $select['graphs'] . '">
    <a href="'.$config['base_url'].'/device/' . $device['device_id'] . '/graphs/">
      <img src="images/16/server_chart.png" align="absmiddle" border="0"> Graphs
    </a>
  </li>');

    $health =  mysql_result(mysql_query("select count(*) from storage WHERE device_id = '" . $device['device_id'] . "'"), 0) +
               mysql_result(mysql_query("select count(sensor_id) from sensors WHERE device_id = '" . $device['device_id'] . "'"), 0) +
               mysql_result(mysql_query("select count(*) from cempMemPool WHERE device_id = '" . $device['device_id'] . "'"), 0) +
               mysql_result(mysql_query("select count(*) from cpmCPU WHERE device_id = '" . $device['device_id'] . "'"), 0) +
               mysql_result(mysql_query("select count(*) from processors WHERE device_id = '" . $device['device_id'] . "'"), 0) +
               mysql_result(mysql_query("select count(current_id) from current WHERE device_id = '" . $device['device_id'] . "'"), 0) +
               mysql_result(mysql_query("select count(freq_id) from frequencies WHERE device_id = '" . $device['device_id'] . "'"), 0) +
               mysql_result(mysql_query("select count(volt_id) from voltage WHERE device_id = '" . $device['device_id'] . "'"), 0) +
               mysql_result(mysql_query("select count(fan_id) from fanspeed WHERE device_id = '" . $device['device_id'] . "'"), 0);

    if ($health)
    {
      echo('<li class="' . $select['health'] . '">
      <a href="'.$config['base_url'].'/device/' . $device['device_id'] . '/health/">
        <img src="images/icons/sensors.png" align="absmiddle" border="0" /> Health
      </a>
    </li>');
    }

    if (@mysql_result(mysql_query("select count(app_id) from applications WHERE device_id = '" . $device['device_id'] . "'"), 0) > '0')
    {
      echo('<li class="' . $select['apps'] . '">
    <a href="' . $config['base_url'] . '/device/' . $device['device_id'] . '/apps/">
      <img src="images/icons/apps.png" align="absmiddle" border="0" /> Apps
    </a>
  </li>');
    }

    if (is_dir($config['collectd_dir'] . "/" . $device['hostname'] ."/"))
    {
      echo('<li class="' . $select['collectd'] . '">
    <a href="'.$config['base_url'].'/device/' . $device['device_id'] . '/collectd/">
      <img src="images/16/chart_line.png" align="absmiddle" border="0" /> CollectD
    </a>
  </li>');
    }

    if (@mysql_result(mysql_query("select count(interface_id) from ports WHERE device_id = '" . $device['device_id'] . "'"), 0) > '0')
    {
      echo('<li class="' . $select['ports'] . '">
    <a href="'.$config['base_url'].'/device/' . $device['device_id'] . '/ports/' .$config['ports_page_default']. '">
      <img src="images/16/connect.png" align="absmiddle" border="0" /> Ports
    </a>
  </li>');
    }

    if (@mysql_result(mysql_query("select count(vlan_id) from vlans WHERE device_id = '" . $device['device_id'] . "'"), 0) > '0')
    {
      echo('<li class="' . $select['vlans'] . '">
    <a href="'.$config['base_url'].'/device/' . $device['device_id'] . '/vlans/">
      <img src="images/16/vlans.png" align="absmiddle" border="0" /> VLANs
    </a>
  </li>');
    }

    if (@mysql_result(mysql_query("SELECT COUNT(id) FROM vminfo WHERE device_id = '" . $device["device_id"] . "'"), 0) > '0')
    {
      echo('<li class="' . $select['vm'] . '">
    <a href="'.$config['base_url'].'/device/' . $device['device_id'] . '/vm/">
      <img src="images/16/server_cog.png" align="absmiddle" border="0" /> Virtual Machines
    </a>
  </li>');
    }

    if (@mysql_result(mysql_query("select count(*) from vrfs WHERE device_id = '" . $device['device_id'] . "'"), 0) > '0')
    {
      echo('<li class="' . $select['vrfs'] . '">
    <a href="'.$config['base_url'].'/device/' . $device['device_id'] . '/vrfs/">
      <img src="images/16/layers.png" align="absmiddle" border="0" /> VRFs
    </a>
  </li>');
    }

    if ($config['enable_bgp'] && $device['bgpLocalAs'])
    {
      echo('<li class="' . $select['bgp'] . '">
    <a href="'.$config['base_url'].'/device/' . $device['device_id'] . '/bgp/">
      <img src="images/16/link.png" align="absmiddle" border="0" /> BGP
    </a>
  </li>');
    }

    $cef_query = mysql_query("SELECT COUNT(*) FROM `cef_switching` WHERE `device_id` = '".$device['device_id']."'");
    $cef_count = mysql_result($cef_query,0);

    if ($cef_count)
    {
      echo('<li class="' . $select['cefswitching'] . '">
    <a href="'.$config['base_url'].'/device/' . $device['device_id'] . '/cefswitching/">
      <img src="images/16/car.png" align="absmiddle" border="0" /> CEF
    </a>
    </li>');
    }

    if ($_SESSION['userlevel'] >= "5" && mysql_result(mysql_query("SELECT count(*) FROM links AS L, ports AS I WHERE I.device_id = '".$device['device_id']."' AND I.interface_id = L.local_interface_id"),0))
    {
      echo('<li class="' . $select['map'] . '">
    <a href="'.$config['base_url'].'/device/' . $device['device_id'] . '/map/">
      <img src="images/16/chart_organisation.png" align="absmiddle" border="0" /> Map
    </a>
  </li>');
    }

    if ($config['enable_inventory'] && @mysql_result(mysql_query("SELECT * FROM `entPhysical` WHERE device_id = '".$device['device_id']."'"), 0) > '0')
    {
      echo('<li class="' . $select['entphysical'] . '">
    <a href="'.$config['base_url'].'/device/' . $device['device_id'] . '/entphysical/">
      <img src="images/16/bricks.png" align="absmiddle" border="0" /> Inventory
    </a>
  </li>');
    }
    elseif (device_permitted($device['device_id']) && $config['enable_inventory'] && @mysql_result(mysql_query("SELECT * FROM `hrDevice` WHERE device_id = '".$device['device_id']."'"), 0) > '0')
    {
      echo('<li class="' . $select['hrdevice'] . '">
    <a href="'.$config['base_url'].'/device/' . $device['device_id'] . '/hrdevice/">
      <img src="images/16/bricks.png" align="absmiddle" border="0" /> Inventory
    </a>
  </li>');
    }

    if (mysql_result(mysql_query("select count(service_id) from services WHERE device_id = '" . $device['device_id'] . "'"), 0) > '0')
    {
      echo('<li class="' . $select['srv'] . '">
    <a href="'.$config['base_url'].'/device/' . $device['device_id'] . '/services/">
      <img src="images/icons/services.png" align="absmiddle" border="0" /> Services
    </a>
  </li>');
    }

    if (@mysql_result(mysql_query("select count(toner_id) from toner WHERE device_id = '" . $device['device_id'] . "'"), 0) > '0')
    {
      echo('<li class="' . $select['toner'] . '">
    <a href="'.$config['base_url'].'/device/' . $device['device_id'] . '/toner/">
      <img src="images/icons/toner.png" align="absmiddle" border="0" /> Toner
    </a>
  </li>');
    }

    if (device_permitted($device['device_id']))
    {
      echo('<li class="' . $select['events'] . '">
      <a href="'.$config['base_url']. "/device/" . $device['device_id'] . '/events/">
        <img src="images/16/report_magnify.png" align="absmiddle" border="0" /> Events
      </a>
    </li>');
    }

    if ($config['enable_syslog'])
    {
      echo('<li class="' . $select['syslog'] . '">
    <a href="'.$config['base_url']."/device/" . $device['device_id'] . '/syslog/">
      <img src="images/16/printer.png" align="absmiddle" border="0" /> Syslog
    </a>
  </li>');
    }

    if ($_SESSION['userlevel'] >= "7")
    {
      if (!is_array($config['rancid_configs'])) { $config['rancid_configs'] = array($config['rancid_configs']); }
      foreach ($config['rancid_configs'] as $configs)
      {
        if ($configs[strlen($configs)-1] != '/') { $configs .= '/'; }
        if (is_file($configs . $device['hostname'])) { $device_config_file = $configs . $device['hostname']; }
      }
    }

    if ($device_config_file)
    {
      echo('<li class="' . $select['showconfig'] . '">
    <a href="'.$config['base_url']."/device/" . $device['device_id'] . '/showconfig/">
      <img src="images/16/page_white_text.png" align="absmiddle" border="0" /> Config
    </a>
  </li>');
    }

    if ($config['nfsen_enable'])
    {   
      if (!is_array($config['nfsen_rrds'])) { $config['nfsen_rrds'] = array($config['nfsen_rrds']); }
      foreach ($config['nfsen_rrds'] as $nfsenrrds)
      {
        if ($configs[strlen($nfsenrrds)-1] != '/') { $nfsenrrds .= '/'; }
        $nfsensuffix = "";
        if ($config['nfsen_suffix']) { $nfsensuffix = $config['nfsen_suffix']; }
        $basefilename_underscored = preg_replace('/\./', $config['nfsen_split_char'], $device['hostname']);
        $nfsen_filename = (strstr($basefilename_underscored, $nfsensuffix, true));
        if (is_file($nfsenrrds . $nfsen_filename . ".rrd")) { $nfsen_rrd_file = $nfsenrrds . $basefilename_underscored . ".rrd"; }
      }
    }

    if ($nfsen_rrd_file)
    {
      echo('<li class="' . $select['nfsen'] . '">
    <a href="'.$config['base_url']."/device/" . $device['device_id'] . '/nfsen/">
      <img src="images/16/rainbow.png" align="absmiddle" border="0" /> Netflow
    </a>
  </li>');
    }


    if ($_SESSION['userlevel'] >= "7")
    {
      echo('<li class="' . $select['edit'] . '">
    <a href="'.$config['base_url']."/device/" . $device['device_id'] . '/edit/">
      <img src="images/16/server_edit.png" align="absmiddle" border="0" /> Settings
    </a>
  </li>');
    }

    echo("</ul>");
    echo('<div class="contentstyle">');

    include("pages/device/".mres(basename($section)).".inc.php");

    echo("</div>");
  }
}
else
{
  include("includes/error-no-perm.inc.php");
}

?>
