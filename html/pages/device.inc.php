<?php

if ($vars['tab'] == 'port' && is_numeric($vars['device']) && port_permitted($vars['port'])) {
    $check_device = get_device_id_by_port_id($vars['port']);
    $permit_ports = 1;
}

if (!is_numeric($vars['device'])) {
    $vars['device'] = device_by_name($vars['device']);
}

if (device_permitted($vars['device']) || $check_device == $vars['device']) {
    $selected['iface'] = 'selected';

    $tab = str_replace('.', '', mres($vars['tab']));

    if (!$tab) {
        $tab = 'overview';
    }

    $select[$tab] = 'active';

    $device  = device_by_id_cache($vars['device']);
    $attribs = get_dev_attribs($device['device_id']);
    $device['attribs'] = $attribs;
    load_os($device);

    $entity_state = get_dev_entity_state($device['device_id']);

    // print_r($entity_state);
    $pagetitle[] = $device['hostname'];

    $component = new LibreNMS\Component();
    $component_count = $component->getComponentCount($device['device_id']);

    echo '<div class="panel panel-default">';
        echo '<table class="device-header-table" style="margin: 0px 7px 7px 7px;" cellspacing="0" class="devicetable" width="99%">';
        require 'includes/device-header.inc.php';
    echo '</table>';
    echo '</div>';


    if (device_permitted($device['device_id'])) {
        echo '<ul class="nav nav-tabs">';

        if ($config['show_overview_tab']) {
            echo '
                <li class="'.$select['overview'].'">
                <a href="'.generate_device_url($device, array('tab' => 'overview')).'">
		<i class="fa fa-lightbulb-o fa-lg icon-theme" aria-hidden="true"></i> Overview
                </a>
                </li>';
        }

        echo '<li class="'.$select['graphs'].'">
            <a href="'.generate_device_url($device, array('tab' => 'graphs')).'">
            <i class="fa fa-area-chart fa-lg icon-theme" aria-hidden="true"></i> Graphs
            </a>
            </li>';

        $health =  dbFetchCell("SELECT COUNT(*) FROM storage WHERE device_id = '" . $device['device_id'] . "'") +
                   dbFetchCell("SELECT COUNT(sensor_id) FROM sensors WHERE device_id = '" . $device['device_id'] . "'") +
                   dbFetchCell("SELECT COUNT(*) FROM mempools WHERE device_id = '" . $device['device_id'] . "'") +
                   dbFetchCell("SELECT COUNT(*) FROM processors WHERE device_id = '" . $device['device_id'] . "'") +
                   count_mib_health($device);

        if ($health) {
            echo '<li class="'.$select['health'].'">
                <a href="'.generate_device_url($device, array('tab' => 'health')).'">
                <i class="fa fa-heartbeat fa-lg icon-theme" aria-hidden="true"></i> Health
                </a>
                </li>';
        }

        if (@dbFetchCell("SELECT COUNT(app_id) FROM applications WHERE device_id = '".$device['device_id']."'") > '0') {
            echo '<li class="'.$select['apps'].'">
                <a href="'.generate_device_url($device, array('tab' => 'apps')).'">
                <i class="fa fa-cubes fa-lg icon-theme" aria-hidden="true"></i> Apps
                </a>
                </li>';
        }

        if (@dbFetchCell("SELECT 1 FROM processes WHERE device_id = '".$device['device_id']."'") > '0') {
            echo '<li class="'.$select['processes'].'">
                <a href="'.generate_device_url($device, array('tab' => 'processes')).'">
                <i class="fa fa-microchip fa-lg icon-theme" aria-hidden="true"></i> Processes
                </a>
                </li>';
        }

        if (isset($config['collectd_dir']) && is_dir($config['collectd_dir'].'/'.$device['hostname'].'/')) {
            echo '<li class="'.$select['collectd'].'">
                <a href="'.generate_device_url($device, array('tab' => 'collectd')).'">
                <i class="fa fa-pie-chart fa-lg icon-theme" aria-hidden="true"></i> CollectD
                </a>
                </li>';
        }

        if (@dbFetchCell("SELECT COUNT(mplug_id) FROM munin_plugins WHERE device_id = '".$device['device_id']."'") > '0') {
            echo '<li class="'.$select['munin'].'">
                <a href="'.generate_device_url($device, array('tab' => 'munin')).'">
                <i class="fa fa-pie-chart fa-lg icon-theme" aria-hidden="true"></i> Munin
                </a>
                </li>';
        }

        if (@dbFetchCell("SELECT COUNT(port_id) FROM ports WHERE device_id = '".$device['device_id']."'") > '0') {
            echo '<li class="'.$select['ports'].$select['port'].'">
                <a href="'.generate_device_url($device, array('tab' => 'ports')).'">
                <i class="fa fa-link fa-lg icon-theme" aria-hidden="true"></i> Ports
                </a>
                </li>';
        }

        if (@dbFetchCell("SELECT COUNT(sla_id) FROM slas WHERE device_id = '".$device['device_id']."'") > '0') {
            echo '<li class="'.$select['slas'].$select['sla'].'">
                <a href="'.generate_device_url($device, array('tab' => 'slas')).'">
                <i class="fa fa-flag fa-lg icon-theme" aria-hidden="true"></i> SLAs
                </a>
                </li>';
        }

        if (@dbFetchCell("SELECT COUNT(accesspoint_id) FROM access_points WHERE device_id = '".$device['device_id']."'") > '0') {
            echo '<li class="'.$select['accesspoints'].'">
                <a href="'.generate_device_url($device, array('tab' => 'accesspoints')).'">
                <i class="fa fa-wifi fa-lg icon-theme"  aria-hidden="true"></i> Access Points
                </a>
                </li>';
        }

        $smokeping_files = get_smokeping_files($device);

        if (count($smokeping_files['in'][$device['hostname']]) || count($smokeping_files['out'][$device['hostname']])) {
            echo '<li class="'.$select['latency'].'">
                <a href="'.generate_device_url($device, array('tab' => 'latency')).'">
                <i class="fa fa-crosshairs fa-lg icon-theme"  aria-hidden="true"></i> Ping
                </a>
                </li>';
        }

        if (@dbFetchCell("SELECT COUNT(vlan_id) FROM vlans WHERE device_id = '".$device['device_id']."'") > '0') {
            echo '<li class="'.$select['vlans'].'">
                <a href="'.generate_device_url($device, array('tab' => 'vlans')).'">
                <i class="fa fa-tasks fa-lg icon-theme"  aria-hidden="true"></i> VLANs
                </a>
                </li>';
        }

        if (@dbFetchCell("SELECT COUNT(id) FROM vminfo WHERE device_id = '".$device['device_id']."'") > '0') {
            echo '<li class="'.$select['vm'].'">
                <a href="'.generate_device_url($device, array('tab' => 'vm')).'">
                <i class="fa fa-cog fa-lg icon-theme"  aria-hidden="true"></i> Virtual Machines
                </a>
                </li>';
        }

        // $loadbalancer_tabs is used in device/loadbalancer/ to build the submenu. we do it here to save queries
        if ($device['os'] == 'netscaler') {
            // Netscaler
            $device_loadbalancer_count['netscaler_vsvr'] = dbFetchCell('SELECT COUNT(*) FROM `netscaler_vservers` WHERE `device_id` = ?', array($device['device_id']));
            if ($device_loadbalancer_count['netscaler_vsvr']) {
                $loadbalancer_tabs[] = 'netscaler_vsvr';
            }
        }

        if ($device['os'] == 'acsw') {
            // Cisco ACE
            $device_loadbalancer_count['loadbalancer_vservers'] = dbFetchCell('SELECT COUNT(*) FROM `loadbalancer_vservers` WHERE `device_id` = ?', array($device['device_id']));
            if ($device_loadbalancer_count['loadbalancer_vservers']) {
                $loadbalancer_tabs[] = 'loadbalancer_vservers';
            }
        }

        // F5 LTM
        if (isset($component_count['f5-ltm-vs'])) {
            $device_loadbalancer_count['ltm_vs'] = $component_count['f5-ltm-vs'];
            $loadbalancer_tabs[] = 'ltm_vs';
        }
        if (isset($component_count['f5-ltm-pool'])) {
            $device_loadbalancer_count['ltm_pool'] = $component_count['f5-ltm-pool'];
            $loadbalancer_tabs[] = 'ltm_pool';
        }

        if (is_array($loadbalancer_tabs)) {
            echo '<li class="'.$select['loadbalancer'].'">
                <a href="'.generate_device_url($device, array('tab' => 'loadbalancer')).'">
                <i class="fa fa-balance-scale fa-lg icon-theme"  aria-hidden="true"></i> Load Balancer
                </a>
                </li>';
        }

        // $routing_tabs is used in device/routing/ to build the tabs menu. we built it here to save some queries
        $device_routing_count['loadbalancer_rservers'] = dbFetchCell('SELECT COUNT(*) FROM `loadbalancer_rservers` WHERE `device_id` = ?', array($device['device_id']));
        if ($device_routing_count['loadbalancer_rservers']) {
            $routing_tabs[] = 'loadbalancer_rservers';
        }

        $device_routing_count['ipsec_tunnels'] = dbFetchCell('SELECT COUNT(*) FROM `ipsec_tunnels` WHERE `device_id` = ?', array($device['device_id']));
        if ($device_routing_count['ipsec_tunnels']) {
            $routing_tabs[] = 'ipsec_tunnels';
        }

        $device_routing_count['bgp'] = dbFetchCell('SELECT COUNT(*) FROM `bgpPeers` WHERE `device_id` = ?', array($device['device_id']));
        if ($device_routing_count['bgp']) {
            $routing_tabs[] = 'bgp';
        }

        $device_routing_count['ospf'] = dbFetchCell("SELECT COUNT(*) FROM `ospf_instances` WHERE `ospfAdminStat` = 'enabled' AND `device_id` = ?", array($device['device_id']));
        if ($device_routing_count['ospf']) {
            $routing_tabs[] = 'ospf';
        }

        $device_routing_count['cef'] = dbFetchCell('SELECT COUNT(*) FROM `cef_switching` WHERE `device_id` = ?', array($device['device_id']));
        if ($device_routing_count['cef']) {
            $routing_tabs[] = 'cef';
        }

        $device_routing_count['vrf'] = @dbFetchCell('SELECT COUNT(*) FROM `vrfs` WHERE `device_id` = ?', array($device['device_id']));
        if ($device_routing_count['vrf']) {
            $routing_tabs[] = 'vrf';
        }

        $device_routing_count['cisco-otv'] = $component_count['Cisco-OTV'];
        if ($device_routing_count['cisco-otv'] > 0) {
            $routing_tabs[] = 'cisco-otv';
        }

        if (is_array($routing_tabs)) {
            echo '<li class="'.$select['routing'].'">
                <a href="'.generate_device_url($device, array('tab' => 'routing')).'">
                <i class="fa fa-random fa-lg icon-theme"  aria-hidden="true"></i> Routing
                </a>
                </li>';
        }

        $device_pw_count = @dbFetchCell('SELECT COUNT(*) FROM `pseudowires` WHERE `device_id` = ?', array($device['device_id']));
        if ($device_pw_count) {
            echo '<li class="'.$select['pseudowires'].'">
                <a href="'.generate_device_url($device, array('tab' => 'pseudowires')).'">
                <i class="fa fa-arrows-alt fa-lg icon-theme"  aria-hidden="true"></i> Pseudowires
                </a>
                </li>';
        }

        echo('<li class="' . $select['map'] . '">
                <a href="'.generate_device_url($device, array('tab' => 'map')).'">
                  <i class="fa fa-sitemap fa-lg icon-theme"  aria-hidden="true"></i> Map
                </a>
              </li>');

        if (@dbFetchCell("SELECT 1 FROM stp WHERE device_id = '".$device['device_id']."'")) {
            echo '<li class="'.$select['stp'].'">
                <a href="'.generate_device_url($device, array('tab' => 'stp')).'">
                <i class="fa fa-sitemap fa-lg icon-theme"  aria-hidden="true"></i> STP
                </a>
                </li>';
        }

        if (@dbFetchCell("SELECT COUNT(*) FROM `packages` WHERE device_id = '".$device['device_id']."'") > '0') {
            echo '<li class="'.$select['packages'].'">
                <a href="'.generate_device_url($device, array('tab' => 'packages')).'">
                <i class="fa fa-folder fa-lg icon-theme"  aria-hidden="true"></i> Pkgs
                </a>
                </li>';
        }

        if ($config['enable_inventory'] && @dbFetchCell("SELECT * FROM `entPhysical` WHERE device_id = '".$device['device_id']."'") > '0') {
            echo '<li class="'.$select['entphysical'].'">
                <a href="'.generate_device_url($device, array('tab' => 'entphysical')).'">
                <i class="fa fa-cube fa-lg icon-theme"  aria-hidden="true"></i> Inventory
                </a>
                </li>';
        } elseif (device_permitted($device['device_id']) && $config['enable_inventory'] && @dbFetchCell("SELECT * FROM `hrDevice` WHERE device_id = '".$device['device_id']."'") > '0') {
            echo '<li class="'.$select['hrdevice'].'">
                <a href="'.generate_device_url($device, array('tab' => 'hrdevice')).'">
                <i class="fa fa-cube fa-lg icon-theme"  aria-hidden="true"></i> Inventory
                </a>
                </li>';
        }

        if (dbFetchCell("SELECT COUNT(service_id) FROM services WHERE device_id = '".$device['device_id']."'") > '0') {
            echo '<li class="'.$select['services'].'">
                <a href="'.generate_device_url($device, array('tab' => 'services')).'">
                <i class="fa fa-cogs fa-lg icon-theme"  aria-hidden="true"></i> Services
                </a>
                </li>';
        }

        if (@dbFetchCell("SELECT COUNT(toner_id) FROM toner WHERE device_id = '".$device['device_id']."'") > '0') {
            echo '<li class="'.$select['toner'].'">
                <a href="'.generate_device_url($device, array('tab' => 'toner')).'">
                <i class="fa fa-print fa-lg icon-theme"  aria-hidden="true"></i> Toner
                </a>
                </li>';
        }

        if (device_permitted($device['device_id'])) {
            echo '<li class="'.$select['logs'].'">
                <a href="'.generate_device_url($device, array('tab' => 'logs')).'">
                <i class="fa fa-sticky-note fa-lg icon-theme"  aria-hidden="true"></i> Logs
                </a>
                </li>';
        }

        if (device_permitted($device['device_id'])) {
            echo '<li class="'.$select['alerts'].'">
                <a href="'.generate_device_url($device, array('tab' => 'alerts')).'">
                <i class="fa fa-exclamation-circle fa-lg icon-theme"  aria-hidden="true"></i> Alerts
                </a>
                </li>';
        }

        if (device_permitted($device['device_id'])) {
            echo '<li class="'.$select['alert-stats'].'">
                <a href="'.generate_device_url($device, array('tab' => 'alert-stats')).'">
                <i class="fa fa-bar-chart fa-lg icon-theme"  aria-hidden="true"></i> Alert Stats
                </a>
                </li>';
        }

        if (is_admin()) {
            if (!is_array($config['rancid_configs'])) {
                $config['rancid_configs'] = array($config['rancid_configs']);
            }

            foreach ($config['rancid_configs'] as $configs) {
                if ($configs[(strlen($configs) - 1)] != '/') {
                    $configs .= '/';
                }

                if (is_file($configs.$device['hostname'])) {
                    $device_config_file = $configs.$device['hostname'];
                } elseif (is_file($configs.strtok($device['hostname'], '.'))) { // Strip domain
                    $device_config_file = $configs.strtok($device['hostname'], '.');
                } else {
                    if (!empty($config['mydomain'])) { // Try with domain name if set
                        if (is_file($configs.$device['hostname'].'.'.$config['mydomain'])) {
                            $device_config_file = $configs.$device['hostname'].'.'.$config['mydomain'];
                        }
                    }
                } // end if
            }

            if ($config['oxidized']['enabled'] === true && !in_array($device['type'], $config['oxidized']['ignore_types']) && isset($config['oxidized']['url'])) {
                $device_config_file = true;
            }
        }

        if ($device_config_file) {
            if (dbFetchCell("SELECT COUNT(device_id) FROM devices_attribs WHERE device_id = ? AND attrib_type = 'override_Oxidized_disable' AND attrib_value='true'", array($device['device_id'])) == '0') {
                echo '<li class="'.$select['showconfig'].'">
                    <a href="'.generate_device_url($device, array('tab' => 'showconfig')).'">
                    <i class="fa fa-align-justify fa-lg icon-theme"  aria-hidden="true"></i> Config
                    </a>
                    </li>';
            }
        }

        if ($config['nfsen_enable']) {
            if (!is_array($config['nfsen_rrds'])) {
                $config['nfsen_rrds'] = array($config['nfsen_rrds']);
            }

            foreach ($config['nfsen_rrds'] as $nfsenrrds) {
                if ($nfsenrrds[(strlen($nfsenrrds) - 1)] != '/') {
                    $nfsenrrds .= '/';
                }

                $nfsensuffix = '';
                if ($config['nfsen_suffix']) {
                    $nfsensuffix = $config['nfsen_suffix'];
                }

                $basefilename_underscored = preg_replace('/\./', $config['nfsen_split_char'], $device['hostname']);
                $nfsen_filename           = preg_replace('/'.$nfsensuffix.'/', '', $basefilename_underscored);
                if (is_file($nfsenrrds.$nfsen_filename.'.rrd')) {
                    $nfsen_rrd_file = $nfsenrrds.$nfsen_filename.'.rrd';
                }
            }
        }//end if

        if ($nfsen_rrd_file) {
            echo '<li class="'.$select['nfsen'].'">
                <a href="'.generate_device_url($device, array('tab' => 'nfsen')).'">
                <i class="fa fa-tint fa-lg icon-theme"  aria-hidden="true"></i> Netflow
                </a>
                </li>';
        }

        if (can_ping_device($attribs) === true) {
            echo '<li class="'.$select['performance'].'">
                <a href="'.generate_device_url($device, array('tab' => 'performance')).'">
                <i class="fa fa-line-chart fa-lg icon-theme"  aria-hidden="true"></i> Performance
                </a>
                </li>';
        }

        echo '<li class="'.$select['notes'].'">
            <a href="'.generate_device_url($device, array('tab' => 'notes')).'">
            <i class="fa fa-file-text-o fa-lg icon-theme"  aria-hidden="true"></i> Notes
            </a>
            </li>';

        if (device_permitted($device['device_id']) && is_mib_poller_enabled($device)) {
            echo '<li class="'.$select['mib'].'">
                <a href="'.generate_device_url($device, array('tab' => 'mib')).'">
                <i class="fa fa-file-text-o fa-lg icon-theme"  aria-hidden="true"></i> MIB
                </a>
                </li>';
        }


        echo '<div class="dropdown pull-right">
              <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown"><i class="fa fa-cog fa-lg icon-theme"  aria-hidden="true"></i>
              <span class="caret"></span></button>
              <ul class="dropdown-menu">
                <li><a href="https://'.$device['hostname'].'" target="_blank" rel="noopener"><i class="fa fa-globe fa-lg icon-theme"  aria-hidden="true"></i> Web</a></li>
                <li><a href="ssh://'.$device['hostname'].'" target="_blank" rel="noopener"><i class="fa fa-lock fa-lg icon-theme"  aria-hidden="true"></i> SSH</a></li>
                 <li><a href="telnet://'.$device['hostname'].'" target="_blank" rel="noopener"><i class="fa fa-terminal fa-lg icon-theme"  aria-hidden="true"></i> Telnet</a></li>';
        if (is_admin()) {
            echo '<li>
                <a href="'.generate_device_url($device, array('tab' => 'edit')).'">
                <i class="fa fa-pencil fa-lg icon-theme"  aria-hidden="true"></i> Edit </a>
                </li>';

            echo '<li><a href="'.generate_device_url($device, array('tab' => 'capture')).'">
                <i class="fa fa-bug fa-lg icon-theme"  aria-hidden="true"></i> Capture
                </a></li>';
        }
              echo '</ul>
            </div>';
        echo '</ul>';
    }//end if

    if (device_permitted($device['device_id']) || $check_device == $vars['device']) {
        echo '<div class="tabcontent">';

        require 'pages/device/'.mres(basename($tab)).'.inc.php';

        echo '</div>';
    } else {
        require 'includes/error-no-perm.inc.php';
    }
}//end if
