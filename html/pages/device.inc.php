<?php

if ($vars['tab'] == "port" && is_numeric($vars['device']) && port_permitted($vars['port']))
{
  $check_device = get_device_id_by_port_id($vars['port']);
  $permit_ports = 1;
}


if (device_permitted($vars['device']) || $check_device == $vars['device'])
{
  $selected['iface'] = "selected";

  $tab = str_replace(".", "", mres($vars['tab']));

  if (!$tab)
  {
    $tab = "overview";
  }

  $select[$tab] = "active";

  $device  = device_by_id_cache($vars['device']);
  $attribs = get_dev_attribs($device['device_id']);

  $entity_state = get_dev_entity_state($device['device_id']);

#  print_r($entity_state);

  $pagetitle[] = $device['hostname'];

  if ($config['os'][$device['os']]['group']) { $device['os_group'] = $config['os'][$device['os']]['group']; }

  echo('<table style="margin: 0px 7px 7px 7px;" cellspacing="0" class="devicetable" width="99%">');
  #include("includes/hostbox.inc.php");
  include("includes/device-header.inc.php");

  echo('</table>');


  if (device_permitted($device['device_id']))
  {
  echo('<ul class="nav nav-tabs">');

   if ($config['show_overview_tab'])
    {
      echo('
  <li class="' . $select['overview'] . '">
    <a href="'.generate_device_url($device, array('tab' => 'overview')).'">
      <img src="images/16/server_lightning.png" align="absmiddle" border="0"> Overview
    </a>
  </li>');
    }

    echo('<li class="' . $select['graphs'] . '">
    <a href="'.generate_device_url($device, array('tab' => 'graphs')).'">
      <img src="images/16/server_chart.png" align="absmiddle" border="0"> Graphs
    </a>
  </li>');

    $health =  dbFetchCell("SELECT COUNT(*) FROM storage WHERE device_id = '" . $device['device_id'] . "'") +
               dbFetchCell("SELECT COUNT(sensor_id) FROM sensors WHERE device_id = '" . $device['device_id'] . "'") +
               dbFetchCell("SELECT COUNT(*) FROM mempools WHERE device_id = '" . $device['device_id'] . "'") +
               dbFetchCell("SELECT COUNT(*) FROM processors WHERE device_id = '" . $device['device_id'] . "'");

    if ($health)
    {
      echo('<li class="' . $select['health'] . '">
      <a href="'.generate_device_url($device, array('tab' => 'health')).'">
        <img src="images/icons/sensors.png" align="absmiddle" border="0" /> Health
      </a>
    </li>');
    }

    if (@dbFetchCell("SELECT COUNT(app_id) FROM applications WHERE device_id = '" . $device['device_id'] . "'") > '0')
    {
      echo('<li class="' . $select['apps'] . '">
    <a href="'.generate_device_url($device, array('tab' => 'apps')).'">
      <img src="images/icons/apps.png" align="absmiddle" border="0" /> Apps
    </a>
  </li>');
    }

    if (@dbFetchCell("SELECT 1 FROM processes WHERE device_id = '" . $device['device_id'] . "'") > '0')
    {
      echo('<li class="' . $select['processes'] . '">
    <a href="'.generate_device_url($device, array('tab' => 'processes')).'">
      <img src="images/16/application_osx_terminal.png" align="absmiddle" border="0" /> Processes
    </a>
  </li>');
    }

    if (isset($config['collectd_dir']) && is_dir($config['collectd_dir'] . "/" . $device['hostname'] ."/"))
    {
      echo('<li class="' . $select['collectd'] . '">
    <a href="'.generate_device_url($device, array('tab' => 'collectd')).'">
      <img src="images/16/chart_line.png" align="absmiddle" border="0" /> CollectD
    </a>
  </li>');
    }

    if (@dbFetchCell("SELECT COUNT(mplug_id) FROM munin_plugins WHERE device_id = '" . $device['device_id'] . "'") > '0')
    {
      echo('<li class="' . $select['munin'] . '">
    <a href="'.generate_device_url($device, array('tab' => 'munin')). '">
      <img src="images/16/chart_line.png" align="absmiddle" border="0" /> Munin
    </a>
  </li>');
    }

    if (@dbFetchCell("SELECT COUNT(port_id) FROM ports WHERE device_id = '" . $device['device_id'] . "'") > '0')
    {
      echo('<li class="' . $select['ports'] . $select['port'] . '">
    <a href="'.generate_device_url($device, array('tab' => 'ports')). '">
      <img src="images/16/connect.png" align="absmiddle" border="0" /> Ports
    </a>
  </li>');
    }

    if (@dbFetchCell("SELECT COUNT(sla_id) FROM slas WHERE device_id = '" . $device['device_id'] . "'") > '0')
    {
      echo('<li class="' . $select['slas'] . $select['sla'] . '">
    <a href="'.generate_device_url($device, array('tab' => 'slas')). '">
      <img src="images/16/chart_line.png" align="absmiddle" border="0" /> SLAs
    </a>
  </li>');
    }

    if (@dbFetchCell("SELECT COUNT(accesspoint_id) FROM access_points WHERE device_id = '" . $device['device_id'] . "'") > '0')
    {
      echo('<li class="' . $select['accesspoints'] . '">
    <a href="'.generate_device_url($device, array('tab' => 'accesspoints')). '">
      <img src="images/icons/wireless.png" align="absmiddle" border="0" /> Access Points
    </a>
  </li>');
    }

    if (isset($config['smokeping']['dir']))
    {
      $smokeping_files = array();
      if ($handle = opendir($config['smokeping']['dir']))
      {
        while (false !== ($file = readdir($handle)))
        {
          if ($file != "." && $file != "..")
          {
            if (eregi(".rrd", $file))
            {
              if (eregi("~", $file))
              {
                list($target,$slave) = explode("~", str_replace(".rrd", "", $file));
                $target = str_replace("_", ".", $target);
                $smokeping_files['in'][$target][$slave] = $file;
                $smokeping_files['out'][$slave][$target] = $file;
              } else {
                $target = str_replace(".rrd", "", $file);
                $target = str_replace("_", ".", $target);
                $smokeping_files['in'][$target][$config['own_hostname']] = $file;
                $smokeping_files['out'][$config['own_hostname']][$target] = $file;
              }
            }
          }
        }
      }
    }

    if (count($smokeping_files['in'][$device['hostname']]) || count($smokeping_files['out'][$device['hostname']]))
    {
      echo('<li class="' . $select['latency'] . '">
    <a href="'.generate_device_url($device, array('tab' => 'latency')).'">
      <img src="images/16/arrow_undo.png" align="absmiddle" border="0" /> Ping
    </a>
  </li>');
    }

    if (@dbFetchCell("SELECT COUNT(vlan_id) FROM vlans WHERE device_id = '" . $device['device_id'] . "'") > '0')
    {
      echo('<li class="' . $select['vlans'] . '">
    <a href="'.generate_device_url($device, array('tab' => 'vlans')).'">
      <img src="images/16/vlans.png" align="absmiddle" border="0" /> VLANs
    </a>
  </li>');
    }

    if (@dbFetchCell("SELECT COUNT(id) FROM vminfo WHERE device_id = '" . $device["device_id"] . "'") > '0')
    {
      echo('<li class="' . $select['vm'] . '">
    <a href="'.generate_device_url($device, array('tab' => 'vm')).'">
      <img src="images/16/server_cog.png" align="absmiddle" border="0" /> Virtual Machines
    </a>
  </li>');
    }

    // $loadbalancer_tabs is used in device/loadbalancer/ to build the submenu. we do it here to save queries

    if ($device['os'] == "netscaler") // Netscaler
    {
      $device_loadbalancer_count['netscaler_vsvr'] = dbFetchCell("SELECT COUNT(*) FROM `netscaler_vservers` WHERE `device_id` = ?", array($device['device_id']));
      if ($device_loadbalancer_count['netscaler_vsvr']) { $loadbalancer_tabs[] = 'netscaler_vsvr'; }
    }

    if ($device['os'] == "acsw")  // Cisco ACE
    {
      $device_loadbalancer_count['loadbalancer_vservers'] = dbFetchCell("SELECT COUNT(*) FROM `loadbalancer_vservers` WHERE `device_id` = ?", array($device['device_id']));
      if ($device_loadbalancer_count['loadbalancer_vservers']) { $loadbalancer_tabs[] = 'loadbalancer_vservers'; }
    }

    if (is_array($loadbalancer_tabs))
    {
      echo('<li class="' . $select['loadbalancer'] . '">
    <a href="'.generate_device_url($device, array('tab' => 'loadbalancer')).'">
      <img src="images/icons/loadbalancer.png" align="absmiddle" border="0" /> Load Balancer
    </a>
  </li>');
    }

    // $routing_tabs is used in device/routing/ to build the tabs menu. we built it here to save some queries

    $device_routing_count['loadbalancer_rservers'] = dbFetchCell("SELECT COUNT(*) FROM `loadbalancer_rservers` WHERE `device_id` = ?", array($device['device_id']));
    if ($device_routing_count['loadbalancer_rservers']) { $routing_tabs[] = 'loadbalancer_rservers'; }

    $device_routing_count['ipsec_tunnels'] = dbFetchCell("SELECT COUNT(*) FROM `ipsec_tunnels` WHERE `device_id` = ?", array($device['device_id']));
    if ($device_routing_count['ipsec_tunnels']) { $routing_tabs[] = 'ipsec_tunnels'; }

    $device_routing_count['bgp'] = dbFetchCell("SELECT COUNT(*) FROM `bgpPeers` WHERE `device_id` = ?", array($device['device_id']));
    if ($device_routing_count['bgp']) { $routing_tabs[] = 'bgp'; }

    $device_routing_count['ospf'] = dbFetchCell("SELECT COUNT(*) FROM `ospf_instances` WHERE `ospfAdminStat` = 'enabled' AND `device_id` = ?", array($device['device_id']));
    if ($device_routing_count['ospf']) { $routing_tabs[] = 'ospf'; }

    $device_routing_count['cef'] = dbFetchCell("SELECT COUNT(*) FROM `cef_switching` WHERE `device_id` = ?", array($device['device_id']));
    if ($device_routing_count['cef']) { $routing_tabs[] = 'cef'; }

    $device_routing_count['vrf'] = @dbFetchCell("SELECT COUNT(*) FROM `vrfs` WHERE `device_id` = ?", array($device['device_id']));
    if ($device_routing_count['vrf']) { $routing_tabs[] = 'vrf'; }

    if (is_array($routing_tabs))
    {
      echo('<li class="' . $select['routing'] . '">
    <a href="'.generate_device_url($device, array('tab' => 'routing')).'">
      <img src="images/16/arrow_branch.png" align="absmiddle" border="0" /> Routing
    </a>
  </li>');
    }

    $device_pw_count = @dbFetchCell("SELECT COUNT(*) FROM `pseudowires` WHERE `device_id` = ?", array($device['device_id']));
    if ($device_pw_count)
    {
      echo('<li class="' . $select['pseudowires'] . '">
    <a href="'.generate_device_url($device, array('tab' => 'pseudowires')).'">
      <img src="images/16/arrow_switch.png" align="absmiddle" border="0" /> Pseudowires
    </a>
  </li>');
    }

    if ($_SESSION['userlevel'] >= "5" && dbFetchCell("SELECT COUNT(*) FROM links AS L, ports AS I WHERE I.device_id = '".$device['device_id']."' AND I.port_id = L.local_port_id"))
    {
      $discovery_links = TRUE;
      echo('<li class="' . $select['map'] . '">
    <a href="'.generate_device_url($device, array('tab' => 'map')).'">
      <img src="images/16/chart_organisation.png" align="absmiddle" border="0" /> Map
    </a>
  </li>');
    }

    if (@dbFetchCell("SELECT COUNT(*) FROM `packages` WHERE device_id = '".$device['device_id']."'") > '0')
    {
      echo('<li class="' . $select['packages'] . '">
    <a href="'.generate_device_url($device, array('tab' => 'packages')).'">
      <img src="images/16/package.png" align="absmiddle" border="0" /> Pkgs
    </a>
  </li>');
    }

    if ($config['enable_inventory'] && @dbFetchCell("SELECT * FROM `entPhysical` WHERE device_id = '".$device['device_id']."'") > '0')
    {
      echo('<li class="' . $select['entphysical'] . '">
    <a href="'.generate_device_url($device, array('tab' => 'entphysical')).'">
      <img src="images/16/bricks.png" align="absmiddle" border="0" /> Inventory
    </a>
  </li>');
    }
    elseif (device_permitted($device['device_id']) && $config['enable_inventory'] && @dbFetchCell("SELECT * FROM `hrDevice` WHERE device_id = '".$device['device_id']."'") > '0')
    {
      echo('<li class="' . $select['hrdevice'] . '">
    <a href="'.generate_device_url($device, array('tab' => 'hrdevice')).'">
      <img src="images/16/bricks.png" align="absmiddle" border="0" /> Inventory
    </a>
  </li>');
    }

    if (dbFetchCell("SELECT COUNT(service_id) FROM services WHERE device_id = '" . $device['device_id'] . "'") > '0')
    {
      echo('<li class="' . $select['services'] . '">
    <a href="'.generate_device_url($device, array('tab' => 'services')).'">
      <img src="images/icons/services.png" align="absmiddle" border="0" /> Services
    </a>
  </li>');
    }

    if (@dbFetchCell("SELECT COUNT(toner_id) FROM toner WHERE device_id = '" . $device['device_id'] . "'") > '0')
    {
      echo('<li class="' . $select['toner'] . '">
    <a href="'.generate_device_url($device, array('tab' => 'toner')).'">
      <img src="images/icons/toner.png" align="absmiddle" border="0" /> Toner
    </a>
  </li>');
    }

    if (device_permitted($device['device_id']))
    {
      echo('<li class="' . $select['logs'] . '">
      <a href="'.generate_device_url($device, array('tab' => 'logs')).'">
        <img src="images/16/report_magnify.png" align="absmiddle" border="0" /> Logs
      </a>
    </li>');
    }

    if (device_permitted($device['device_id']))
    {
      echo('<li class="' . $select['alerts'] . '">
      <a href="'.generate_device_url($device, array('tab' => 'alerts')).'">
        <img src="images/16/bell.png" align="absmiddle" border="0" /> Alerts
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
    <a href="'.generate_device_url($device, array('tab' => 'showconfig')).'/">
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
    <a href="'.generate_device_url($device, array('tab' => 'nfsen')).'">
      <img src="images/16/rainbow.png" align="absmiddle" border="0" /> Netflow
    </a>
  </li>');
    }

    if ($_SESSION['userlevel'] >= "7")
    {
      echo('<li class="' . $select['edit'] . '" style="float: right;">
    <a href="'.generate_device_url($device, array('tab' => 'edit')).'">
      <img src="images/16/wrench.png" align="absmiddle" border="0" />
    </a>
  </li>');
    }
     echo("</ul>");
 }

  if (device_permitted($device['device_id']) || $check_device == $vars['device']) {
    echo('<div class="tabcontent">');

    include("pages/device/".mres(basename($tab)).".inc.php");

    echo("</div>");
  } else {
    include("includes/error-no-perm.inc.php");
  }
}

?>
