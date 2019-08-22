<?php

/**
 * LibreNMS Network Management and Monitoring System
 * Copyright (C) 2006-2011, Observium Developers - http://www.observium.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * See COPYING for more details.
 *
 * @package    LibreNMS
 * @subpackage config
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 * @license    http://gnu.org/copyleft/gpl.html GNU GPL
 */

//
// Please don't edit this file -- make changes to the configuration array in config.php
//

// Default directories
$config['project_name'] = 'LibreNMS';
$config['project_id']   = strtolower($config['project_name']);

$config['temp_dir']    = '/tmp';
$config['log_dir']     = $config['install_dir'].'/logs';

// MySQL Debug level
$config['mysql_log_level']       = 'ERROR';

//MySQL Settings
$config['db_port']               = 3306;
$config['db_socket']             = null;
$config['db_name']               = 'librenms';
$config['db_user']               = 'librenms';
$config['db_pass']               = null;
$config['db_socket']             = null;

// What is my own hostname (used to identify this host in its own database)
$config['own_hostname'] = 'localhost';

// Location of executables
//$config['fping']                    = '/usr/sbin/fping';
//$config['fping6']                   = '/usr/sbin/fping6';
// https://docs.librenms.org/Support/Configuration/#fping
$config['fping_options']['timeout'] = 500;
$config['fping_options']['count']   = 3;
$config['fping_options']['interval'] = 500;
$config['snmpwalk']                  = '/usr/bin/snmpwalk';
$config['snmpget']                   = '/usr/bin/snmpget';
$config['snmpbulkwalk']              = '/usr/bin/snmpbulkwalk';
$config['snmptranslate']             = '/usr/bin/snmptranslate';
$config['whois']          = '/usr/bin/whois';
$config['ping']           = '/bin/ping';
$config['mtr']            = '/usr/bin/mtr';
$config['nmap']           = '/usr/bin/nmap';
$config['nagios_plugins'] = '/usr/lib/nagios/plugins';
$config['ipmitool']       = '/usr/bin/ipmitool';
$config['virsh']          = '/usr/bin/virsh';
$config['dot']            = '/usr/bin/dot';
$config['sfdp']           = '/usr/bin/sfdp';
$config['nfdump']         = '/usr/bin/nfdump';

$config['slow_statistics'] = true;
// THIS WILL CHANGE TO FALSE IN FUTURE
// RRD Format Settings
// These should not normally be changed
// Though one could conceivably increase or decrease the size of each RRA if one had performance problems
// Or if one had a very fast I/O subsystem with no performance worries.
$config['rrd_rra']  = ' RRA:AVERAGE:0.5:1:2016 RRA:AVERAGE:0.5:6:1440 RRA:AVERAGE:0.5:24:1440 RRA:AVERAGE:0.5:288:1440 ';
$config['rrd_rra'] .= ' RRA:MIN:0.5:1:720 RRA:MIN:0.5:6:1440     RRA:MIN:0.5:24:775     RRA:MIN:0.5:288:797 ';
$config['rrd_rra'] .= ' RRA:MAX:0.5:1:720 RRA:MAX:0.5:6:1440     RRA:MAX:0.5:24:775     RRA:MAX:0.5:288:797 ';
$config['rrd_rra'] .= ' RRA:LAST:0.5:1:1440 ';
//$config['rrd']['heartbeat'] = 600;
//$config['rrd']['step'] = 300;

// RRDCacheD - Make sure it can write to your RRD dir!
// $config['rrdcached']    = "unix:/var/run/rrdcached.sock";

// Web Interface Settings
if (isset($_SERVER['SERVER_NAME']) && isset($_SERVER['SERVER_PORT'])) {
    if (strpos($_SERVER['SERVER_NAME'], ':')) {
        // Literal IPv6
        $config['base_url'] = 'http://['.$_SERVER['SERVER_NAME'].']'.($_SERVER['SERVER_PORT'] != 80 ? ':'.$_SERVER['SERVER_PORT'] : '').'/';
    } else {
        $config['base_url'] = 'http://'.$_SERVER['SERVER_NAME'].($_SERVER['SERVER_PORT'] != 80 ? ':'.$_SERVER['SERVER_PORT'] : '').'/';
    }
}

$config['project_home']   = 'http://www.librenms.org/';
$config['project_issues'] = 'https://community.librenms.org/c/help';
$config['github_api']   = 'https://api.github.com/repos/librenms/librenms/';
$config['site_style']     = 'light';
// Options are dark or light
$config['stylesheet']   = 'css/styles.css';
$config['mono_font']    = 'DejaVuSansMono';
$config['favicon']      = '';
$config['page_refresh'] = '300';
// Refresh the page every xx seconds, 0 to disable
$config['front_page'] = 'pages/front/tiles.php';
$config['front_page_settings']['top']['ports']   = 10;
$config['front_page_settings']['top']['devices'] = 10;
$config['front_page_down_box_limit']             = 10;
$config['vertical_summary'] = 0;
// Enable to use vertical summary on front page instead of horizontal
$config['top_ports'] = 1;
// This enables the top X ports box
$config['top_devices'] = 1;
// This enables the top X devices box
$config['page_title_prefix'] = '';
$config['page_title_suffix'] = $config['project_name'];
$config['timestamp_format']  = 'd-m-Y H:i:s';
$config['page_gen']          = 0;
$config['enable_lazy_load']  = true;
// display MySqL & PHP stats in footer?
$config['login_message'] = 'Unauthorised access or use shall render the user liable to criminal and/or civil prosecution.';
$config['public_status'] = false;
// Enable public accessable status page
$config['old_graphs'] = 1;
// RRDfiles from before the great rra reform. This is default for a while.
$config['int_customers'] = 1;
// Enable Customer Port Parsing
$config['customers_descr'] = 'cust';
$config['transit_descr'][]   = 'transit';
// Add custom transit descriptions (can be an string)
$config['peering_descr'][] = 'peering';
// Add custom peering descriptions (can be an string)
$config['core_descr'][] = 'core';
// Add custom core descriptions (can be an string)
$config['custom_descr'][] = '';
// Add custom interface descriptions (can be an string)
$config['int_transit'] = 1;
// Enable Transit Types
$config['int_peering'] = 1;
// Enable Peering Types
$config['int_core'] = 1;
// Enable Core Port Types
$config['int_l2tp'] = 0;
// Enable L2TP Port Types
$config['show_locations'] = 1;
// Enable Locations on menu
$config['show_locations_dropdown'] = 1;
// Enable Locations dropdown on menu
$config['show_services'] = 0;
// Enable Services on menu
$config['ports_page_default'] = 'details';
// eg "details" or "basic"
// Adding Host Settings
$config['addhost_alwayscheckip']   = false;   # TRUE - check for duplicate ips even when adding host by name. FALSE- only check when adding host by ip.
// SNMP Settings - Timeouts/Retries disabled as default
// $config['snmp']['timeout'] = 1;            # timeout in seconds
// $config['snmp']['retries'] = 5;            # how many times to retry the query
$config['snmp']['transports'] = array(
    'udp',
    'udp6',
    'tcp',
    'tcp6',
);

$config['snmp']['version'] = ['v2c', 'v3', 'v1'];
// Default version to use
// SNMPv1/2c default settings
$config['snmp']['community'][0] = 'public';
// Communities to try during adding hosts and discovery
$config['snmp']['port'] = 161;
// Port Client SNMP is running on
// SNMPv3 default settings
// The array can be expanded to give another set of parameters
// NOTE: If you change these, also change the equivalents in includes/defaults.inc.php - not sure why they are separate
$config['snmp']['v3'][0]['authlevel'] = 'noAuthNoPriv';
// noAuthNoPriv | authNoPriv | authPriv
$config['snmp']['v3'][0]['authname'] = 'root';
// User Name (required even for noAuthNoPriv)
$config['snmp']['v3'][0]['authpass'] = '';
// Auth Passphrase
$config['snmp']['v3'][0]['authalgo'] = 'MD5';
// MD5 | SHA
$config['snmp']['v3'][0]['cryptopass'] = '';
// Privacy (Encryption) Passphrase
$config['snmp']['v3'][0]['cryptoalgo'] = 'AES';
// AES | DES

// Devices must respond to icmp by default
$config['icmp_check'] = true;

// The amount of time to keep the OS cache
$config['os_def_cache_time'] = 86400;

// Autodiscovery Settings
$config['autodiscovery']['xdp'] = true;
$config['autodiscovery']['xdp_exclude']['sysdesc_regexp'][] = '/-K9W8-/'; // Cisco Lightweight Access Point
$config['autodiscovery']['cdp_exclude']['platform_regexp'][] = '/^Cisco IP Phone/'; //Cisco IP Phone
// Autodiscover hosts via discovery protocols
$config['autodiscovery']['ospf'] = true;
// Autodiscover hosts via OSPF
$config['autodiscovery']['bgp'] = true;
// Autodiscover hosts via BGP
$config['autodiscovery']['snmpscan'] = true;
// autodiscover hosts via SNMP scanning
$config['discover_services'] = false;
// Autodiscover services via SNMP on devices of type "server"
// Networks to exclude from autodiscovery
$config['autodiscovery']['nets-exclude'][] = '0.0.0.0/8';
$config['autodiscovery']['nets-exclude'][] = '127.0.0.0/8';
$config['autodiscovery']['nets-exclude'][] = '169.254.0.0/16';
$config['autodiscovery']['nets-exclude'][] = '224.0.0.0/4';
$config['autodiscovery']['nets-exclude'][] = '240.0.0.0/4';
// Autodiscover by IP
$config['discovery_by_ip'] = false;// Set to true if you want to enable auto discovery by IP.

$config['alerts']['email']['enable'] = false;
// Enable email alerts
$config['alerts']['bgp']['whitelist'] = null;
// Populate as an array() with ASNs to alert on.
$config['alerts']['port']['ifdown'] = false;
// Generate alerts for ports that go down
// Port bandwidth threshold percentage %age utilisation above this will cause an alert
$config['alerts']['port_util_alert'] = false;
// Disabled as default
$config['alerts']['port_util_perc'] = 85;
// %age above which to alert
$config['uptime_warning'] = '84600';
// Time in seconds to display a "Device Rebooted" Alert. 0 to disable warnings.
// Cosmetics
$config['rrdgraph_def_text']  = '-c BACK#EEEEEE00 -c SHADEA#EEEEEE00 -c SHADEB#EEEEEE00 -c CANVAS#FFFFFF00 -c GRID#a5a5a5';
$config['rrdgraph_def_text'] .= ' -c MGRID#FF9999 -c FRAME#5e5e5e -c ARROW#5e5e5e -R normal';
// This is largely for people who are using a dark CSS override system.
// For multi-user installs, likely best to leave it at the default or pick one that works nicely for both.
// If you want a color that works nice for both a dark or light BG, 0000FF seems to.
$config['rrdgraph_def_text_color'] = '000000';
$config['rrdgraph_real_percentile'] = false;
$config['percentile_value'] = 95;
// Set to TRUE if you want to display the 95% based on the highest value. (aka real 95%)
$config['overlib_defaults'] = ",FGCOLOR,'#ffffff', BGCOLOR, '#e5e5e5', BORDER, 5, CELLPAD, 4, CAPCOLOR, '#555555', TEXTCOLOR, '#3e3e3e'";

// Set this to false if you want to disable the mouseover popup graphs
$config['web_mouseover']    = true;

// list colors
$config['list_colour'] = array(
    'even' => '#ffffff',
    'even_alt' => '#f9f9f9',
    'even_alt2' => '#f0f0f0',
    'odd' => '#eeeeee',
    'odd_alt' => '#f0f0f0',
    'odd_alt2' => '#e3e3e3',
    'highlight' => '#ffcccc',
);
$config['warn_colour'] = '#ffeeee';
$config['warn_colour_alt'] = '#ffcccc';

// $config['graph_colours'] = array("000066","330066","990066","990066","CC0033","FF0000"); // Purple to Red
// $config['graph_colours'] = array("006600","336600","996600","996600","CC3300","FF0000"); // Green to Red
// $config['graph_colours'] = array("002200","004400","006600","008800","00AA00","00CC00"); // Green
// $config['graph_colours'] = array("220000","440000","660000","880000","AA0000","CC0000"); // Red
// $config['graph_colours'] = array("001122","002244","003366","004488","0055AA","0066CC"); // Blue
// $config['graph_colours'] = array("002233","004466","006699","0088CC","0099FF");          // Sky-Blue
// $config['graph_colours'] = array("110022","330066","440088","6600AA","8800FF");          // Purple
// $config['graph_colours'] = array("002200","004400","006600","008800","00AA00","00AA00","00CC00"); // Forest Greens
// $config['graph_colours']['greens']  = array("112200","224400","336600","448800","66AA00","88FF00");          // Grass Greens
// $config['graph_colours']['greens']  = array("95FFA7","4EFF97","33FF66","336600","224400","112200");
// $config['graph_colours']['greens'] = array("B7d6A9","8fcb73","50b91d","3ab419","0a8612","034f11");
// $config['graph_colours']['blues']   = array("b5d7ff","6eb7ff","0064ff","0082ff","0019d5","0016cb","00007d"); // Cold Blues
$config['graph_colours']['mixed']   = array(
    'CC0000',
    '008C00',
    '4096EE',
    '73880A',
    'D01F3C',
    '36393D',
    'FF0084',
    '91B13C',
);
$config['graph_colours']['oranges'] = array(
    'E43C00',
    'E74B00',
    'EB5B00',
    'EF6A00',
    'F37900',
    'F78800',
    'FB9700',
    'FFA700',
);
$config['graph_colours']['greens']  = array(
    'B6D14B',
    '91B13C',
    '6D912D',
    '48721E',
    '24520F',
    '003300',
);
$config['graph_colours']['pinks']   = array(
    'D0558F',
    'B34773',
    '943A57',
    '792C38',
    '5C1F1E',
    '401F10',
);
$config['graph_colours']['blues']   = array(
    'A0A0E5',
    '8080BD',
    '606096',
    '40406F',
    '202048',
    '000033',
);
$config['graph_colours']['purples'] = array(
    'CC7CCC',
    'AF63AF',
    '934A93',
    '773177',
    '5B185B',
    '3F003F',
);
$config['graph_colours']['default'] = $config['graph_colours']['blues'];

// Colour values from http://www.sapdesignguild.org/goodies/diagram_guidelines/color_palettes.html
$config['graph_colours']['manycolours'] = array(
    "FFF8A3", "FAE16B", "F8D753", "F3C01C", "F0B400",   // yellows
    "A9CC8F", "82B16A", "5C9746", "3D8128", "1E6C0B",   // greens
    "B2C8D9", "779DBF", "3E75A7", "205F9A", "00488C",   // blues
    "BEA37A", "907A52", "7A653E", "63522B", "3D3000",   // browns
    "F3AA79", "EB8953", "E1662A", "DC5313", "D84000",   // oranges
    "B5B5A9", "8B8D82", "74796F", "5D645A", "434C43",   // greys
    "E6A4A5", "D6707B", "C4384F", "BC1C39", "B30023",   // pinks
);

// interleaved purple, pink, green, blue, and orange
$config['graph_colours']['psychedelic'] = array(
    'CC7CCC', 'D0558F', 'B6D14B', 'A0A0E5', 'E43C00',
    'AF63AF', 'B34773', '91B13C', '8080BD', 'E74B00',
    '934A93', '943A57', '6D912D', '606096', 'EB5B00',
    '773177', '792C38', '48721E', '40406F', 'EF6A00',
    '5B185B', '5C1F1E', '24520F', '202048', 'F37900',
    '3F003F', '401F10', '003300', '000033', 'F78800',
    'FB9700', 'FFA700'
);

$config['graph_colours']['mega']=array_merge(
    $config['graph_colours']['psychedelic'],
    $config['graph_colours']['manycolours'],
    $config['graph_colours']['default'],
    $config['graph_colours']['mixed']
);

// Map colors
$config['network_map_legend'] = array(
    '0'            => '#008dca',
    '5'            => '#0092a6',
    '10'           => '#009782',
    '15'           => '#009c5f',
    '20'           => '#00a13b',
    '25'           => '#00a617',
    '30'           => '#0bad00',
    '35'           => '#2fb700',
    '40'           => '#53c100',
    '45'           => '#77cc00',
    '50'           => '#9ad600',
    '55'           => '#bee000',
    '60'           => '#e2ea00',
    '65'           => '#ead600',
    '70'           => '#e5b200',
    '75'           => '#e08e00',
    '80'           => '#db6b00',
    '85'           => '#d64700',
    '90'           => '#d12300',
    '95'           => '#cc0000',
    '100'          => '#cc0000',
    'di' => [
        'edge' => '#dddddd88',
        'border' => '#cccccc',
        'node' => '#eeeeee',
    ],
    'dn' => [
        'edge' => '#ff777788',
        'border' => '#ff5555',
        'node' => '#ffdddd',
    ]
);

// Default mini graph time options:
$config['graphs']['mini']['widescreen'] = array(
    'sixhour' => '6 Hours',
    'day' => '24 Hours',
    'twoday' => '48 Hours',
    'week' => 'One Week',
    'twoweek' => 'Two Weeks',
    'month' => 'One Month',
    'twomonth' => 'Two Months',
    'year' => 'One Year',
    'twoyear' => 'Two Years',
);

$config['graphs']['mini']['normal'] = array(
    'day' => '24 Hours',
    'week' => 'One Week',
    'month' => 'One Month',
    'year' => 'One Year',
);

$config['graphs']['row']['normal'] = $config['graphs']['mini']['widescreen'];

// Network Map Items
$config['network_map_items'] = array('xdp','mac');

// Network Map Visualization Options
// See http://visjs.org/docs/network/ for description of these options.
$config['network_map_vis_options'] = '{
  layout:{
      randomSeed:2
  },
  "edges": {
    arrows: {
          to:     {enabled: true, scaleFactor:0.5},
    },
    "smooth": {
        enabled: false
    },
    font: {
        size: 14,
        color: "red",
        face: "sans",
        background: "white",
        strokeWidth:3,
        align: "middle",
        strokeWidth: 2
    }
  },
  "physics": {
     "barnesHut": {
      "gravitationalConstant": -2000,
      "centralGravity": 0.3,
      "springLength": 200,
      "springConstant": 0.04,
      "damping": 0.09,
      "avoidOverlap": 1
    },

     "forceAtlas2Based": {
      "gravitationalConstant": -50,
      "centralGravity": 0.01,
      "springLength": 200,
      "springConstant": 0.08,
      "damping": 0.4,
      "avoidOverlap": 1
    },

     "repulsion": {
      "centralGravity": 0.2,
      "springLength": 250,
      "springConstant": 0.2,
      "nodeDistance": 200,
      "damping": 0.07
    },

     "hierarchicalRepulsion": {
      "nodeDistance": 300,
      "centralGravity": 0.2,
      "springLength": 300,
      "springConstant": 0.2,
      "damping": 0.07
    },

  "maxVelocity": 50,
  "minVelocity": 0.4,
  "solver": "hierarchicalRepulsion",
  "stabilization": {
    "enabled": true,
    "iterations": 1000,
    "updateInterval": 100,
    "onlyDynamicEdges": false,
    "fit": true
  },

  "timestep": 0.4,
 }
}';

// Device page options
$config['show_overview_tab'] = true;

$config['cpu_details_overview'] = false; //By default show only average cpu in device overview

// The device overview page options
$config['overview_show_sysDescr'] = true;

// Enable checking of version in discovery
// Poller/Discovery Modules
$config['enable_bgp'] = 1;
// Enable BGP session collection and display
$config['enable_syslog'] = 0;
// Enable Syslog
$config['enable_inventory'] = 1;
// Enable Inventory
$config['enable_pseudowires'] = 1;
// Enable Pseudowires
$config['enable_vrfs'] = 1;
// Enable VRFs
$config['enable_vrf_lite_cisco'] = 1;
// Enable routes for VRF lite cisco
$config['enable_sla'] = 0;
// Enable Cisco SLA collection and display
// Ports extension modules
$config['port_descr_parser'] = 'includes/port-descr-parser.inc.php';
// Parse port descriptions into fields
$config['enable_ports_etherlike'] = 0;
// Enable Polling EtherLike-MIB (doubles interface processing time)
$config['enable_ports_junoseatmvp'] = 0;
// Enable JunOSe ATM VC Discovery/Poller
$config['enable_ports_adsl'] = 1;
// Enable ADSL-LINE-MIB
$config['enable_ports_poe'] = 0;
// Enable PoE stats collection
// Billing System Configuration
$config['enable_billing'] = 0;
// Enable Billing
$config['billing']['customer_autoadd'] = 0;
// Enable Auto-add bill per customer
$config['billing']['circuit_autoadd'] = 0;
// Enable Auto-add bill per circuit_id
$config['billing']['bill_autoadd'] = 0;
// Enable Auto-add bill per bill_id
$config['billing']['base'] = 1000;
// Set the base to divider bytes to kB, MB, GB ,... (1000|1024)
// External Integration
// $config['rancid_configs'][]             = '/var/lib/rancid/network/configs/';
$config['rancid_repo_type'] = 'svn';
$config['rancid_ignorecomments'] = 0;
// Ignore lines starting with #
// $config['collectd_dir']                 = '/var/lib/collectd/rrd';
// $config['smokeping']['dir']             = "/var/lib/smokeping/";
$config['smokeping']['pings']             = 20;
// $config['oxidized']['enabled']         = FALSE;//Set to TRUE
// $config['oxidized']['url']             = 'http://127.0.0.1:8888';// Set the Oxidized rest URL
// $config['oxidized']['reload_nodes'] = FALSE;//Set to TRUE, check documentation
// NFSen RRD dir.
$config['nfsen_enable'] = 0;
// $config['nfsen_split_char']   = "_";
// $config['nfsen_rrds']   = "/var/nfsen/profiles-stat/live/";
// $config['nfsen_suffix']   = "_yourdomain_com";
$config['nfsen_subdirlayout'] = 1;
$config['nfsen_last_max'] = 153600; // 48 hours ago in seconds
$config['nfsen_top_max'] = 500; // max topN value for stats
$config['nfsen_top_N']=array( 10, 20, 50, 100, 200, 500 );
$config['nfsen_top_default']=20;
$config['nfsen_stat_default']='srcip';
$config['nfsen_order_default']='packets';
$config['nfsen_last_default']=900;
$config['nfsen_lasts']=array(
                            '300'=>'5 minutes',
                            '600'=>'10 minutes',
                            '900'=>'15 minutes',
                            '1800'=>'30 minutes',
                            '3600'=>'1 hour',
                            '9600'=>'3 hours',
                            '38400'=>'12 hours',
                            '76800'=>'24 hours',
                            '115200'=>'36 hours',
                            '153600'=>'48 hours',
                            );
// Location Mapping
// Use this feature to map ugly locations to pretty locations
// config['location_map']['Under the Sink'] = "Under The Sink, The Office, London, UK";
// Ignores & Allows
// Has to be lowercase
$config['bad_if'][] = 'voip-null';
$config['bad_if'][] = 'virtual-';
$config['bad_if'][] = 'unrouted';
$config['bad_if'][] = 'eobc';
$config['bad_if'][] = 'lp0';
$config['bad_if'][] = '-atm';
$config['bad_if'][] = 'faith0';
$config['bad_if'][] = 'container';
$config['bad_if'][] = 'async';
$config['bad_if'][] = 'plip';
$config['bad_if'][] = '-physical';
$config['bad_if'][] = 'container';
$config['bad_if'][] = 'unrouted';
$config['bad_if'][] = 'bluetooth';
$config['bad_if'][] = 'isatap';
$config['bad_if'][] = 'ras';
$config['bad_if'][] = 'qos';
$config['bad_if'][] = 'span rp';
$config['bad_if'][] = 'span sp';
$config['bad_if'][] = 'sslvpn';
$config['bad_if'][] = 'pppoe-';
$config['bad_if'][] = 'irtual';
// $config['bad_if'][] = "control plane";  // Example for cisco control plane
// Ignore ports based on ifType. Case-sensitive.
$config['bad_iftype'][] = 'voiceEncap';
$config['bad_iftype'][] = 'voiceFXO';
$config['bad_iftype'][] = 'voiceFXS';
$config['bad_iftype'][] = 'voiceOverAtm';
$config['bad_iftype'][] = 'voiceOverFrameRelay';
$config['bad_iftype'][] = 'voiceOverIp';
$config['bad_iftype'][] = 'ds0';
$config['bad_iftype'][] = 'ds1';
$config['bad_iftype'][] = 'ds3';
// $config['bad_iftype'][] = "isdn";     #show signaling traffic
// $config['bad_iftype'][] = "lapd";      #show signaling traffic
$config['bad_iftype'][] = 'sonet';
$config['bad_iftype'][] = 'atmSubInterface';
$config['bad_iftype'][] = 'aal5';
$config['bad_iftype'][] = 'shdsl';
$config['bad_iftype'][] = 'mpls';

$config['bad_if_regexp'][] = '/^ng[0-9]+$/';
$config['bad_if_regexp'][] = '/^sl[0-9]/';

// Rewrite Interfaces
$config['rewrite_if_regexp']['/^cpu interface/'] = 'Mgmt';

// Storage default warning percentage
$config['storage_perc_warn'] = 60;

$config['ignore_mount_removable'] = 1;
// Ignore removable disk storage
$config['ignore_mount_network'] = 1;
// Ignore network mounted storage
$config['ignore_mount_optical'] = 1;
// Ignore mounted optical discs
// Per-device interface graph filters
$config['device_traffic_iftype'][] = '/loopback/';
$config['device_traffic_iftype'][] = '/tunnel/';
$config['device_traffic_iftype'][] = '/virtual/';
$config['device_traffic_iftype'][] = '/mpls/';
$config['device_traffic_iftype'][] = '/ieee8023adLag/';
$config['device_traffic_iftype'][] = '/l2vlan/';
$config['device_traffic_iftype'][] = '/ppp/';

$config['device_traffic_descr'][] = '/loopback/';
$config['device_traffic_descr'][] = '/vlan/';
$config['device_traffic_descr'][] = '/tunnel/';
$config['device_traffic_descr'][] = '/bond/';
$config['device_traffic_descr'][] = '/null/';
$config['device_traffic_descr'][] = '/dummy/';

// IRC Bot configuration
$config['irc_host']         = '';
$config['irc_port']         = '';
$config['irc_maxretry']     = 3;
$config['irc_nick']         = $config['project_name'];
$config['irc_chan'][]       = '##'.$config['project_id'];
$config['irc_pass']         = '';
$config['irc_external']     = '';
$config['irc_authtime']     = 3;
$config['irc_debug']        = false;
$config['irc_alert']        = false;
$config['irc_alert_utf8']   = false;
$config['irc_alert_short']  = false;
$config['irc_ctcp']         = false;
$config['irc_ctcp_version'] = "LibreNMS IRCbot. https://www.librenms.org/";

// Authentication
$config['allow_unauth_graphs'] = false;
// Allow graphs to be viewed by anyone
$config['allow_unauth_graphs_cidr'] = array();
// Allow graphs to be viewed without authorisation from certain IP ranges
$config['auth_mechanism'] = 'mysql';
// Available mechanisms: mysql (default), ldap, http-auth
$config['auth_remember'] = '30';
// This is how long in days to remember users who select remember me
// LDAP Authentication
$config['auth_ldap_version'] = 3;
// v2 or v3
$config['auth_ldap_server'] = 'ldap.example.com';
$config['auth_ldap_port']   = 389;
$config['auth_ldap_prefix'] = 'uid=';
$config['auth_ldap_suffix'] = ',ou=People,dc=example,dc=com';
$config['auth_ldap_group']  = 'cn=groupname,ou=groups,dc=example,dc=com';
$config['auth_ldap_uid_attribute'] = 'uidnumber';

$config['auth_ldap_attr']['uid'] = "uid";
$config['auth_ldap_groupbase']                  = 'ou=group,dc=example,dc=com';
$config['auth_ldap_groups']['admin']['level']   = 10;
$config['auth_ldap_groups']['pfy']['level']     = 7;
$config['auth_ldap_groups']['support']['level'] = 1;
$config['auth_ldap_groupmemberattr']            = 'memberUid';
$config['auth_ldap_emailattr']                  = 'mail';
$config['auth_ldap_cache_ttl'] = 300;
// How long in seconds should ldap* module cache user information in $_SESSION
$config['auth_ldap_userdn']                     = false;
// Uses a users full DN as the value of the member attribute in a group (instead of member: username, it’s member: uid=username,ou=groups,dc=domain,dc=com).

// Active Directory Authentication
$config['auth_ad_user_filter'] = "(objectclass=user)";
$config['auth_ad_group_filter'] = "(objectclass=group)";

// Single sign-on defaults
$config['sso']['create_users'] = true;
$config['sso']['update_users'] = true;
$config['sso']['user_attr'] = 'REMOTE_USER';

// Sensors
$config['allow_entity_sensor']['amperes']     = 1;
$config['allow_entity_sensor']['celsius']     = 1;
$config['allow_entity_sensor']['dBm']         = 1;
$config['allow_entity_sensor']['voltsDC']     = 1;
$config['allow_entity_sensor']['voltsAC']     = 1;
$config['allow_entity_sensor']['watts']       = 1;
$config['allow_entity_sensor']['truthvalue']  = 1;
$config['allow_entity_sensor']['specialEnum'] = 1;

// Filesystems
$config['ignore_mount'][] = '/kern';
$config['ignore_mount'][] = '/mnt/cdrom';
$config['ignore_mount'][] = '/proc';
$config['ignore_mount'][] = '/dev';
$config['ignore_mount'][] = '/compat/linux/proc';
$config['ignore_mount'][] = '/compat/linux/sys';

$config['ignore_mount_string'][] = 'packages';
$config['ignore_mount_string'][] = 'devfs';
$config['ignore_mount_string'][] = 'procfs';
$config['ignore_mount_string'][] = 'linprocfs';
$config['ignore_mount_string'][] = 'linsysfs';
$config['ignore_mount_string'][] = 'UMA';
$config['ignore_mount_string'][] = 'MALLOC';

$config['ignore_mount_regexp'][] = '/on: \/packages/';
$config['ignore_mount_regexp'][] = '/on: \/dev/';
$config['ignore_mount_regexp'][] = '/on: \/proc/';
$config['ignore_mount_regexp'][] = '/on: \/junos^/';
$config['ignore_mount_regexp'][] = '/on: \/junos\/dev/';
$config['ignore_mount_regexp'][] = '/on: \/jail\/dev/';
$config['ignore_mount_regexp'][] = '/^(dev|proc)fs/';
$config['ignore_mount_regexp'][] = '/^\/dev\/md0/';
$config['ignore_mount_regexp'][] = '/^\/var\/dhcpd\/dev/';
$config['ignore_mount_regexp'][] = '/UMA/';
$config['ignore_mount_regexp'][] = "/^\/Volumes\/OS X Base System/";

$config['ignore_mount_removable'] = 1;
// Ignore removable disk storage
$config['ignore_mount_network'] = 1;
// Ignore network mounted storage
// Syslog Settings
// Entries older than this will be removed
$config['syslog_filter'][] = 'last message repeated';
$config['syslog_filter'][] = 'Connection from UDP: [';
$config['syslog_filter'][] = 'ipSystemStatsTable node ipSystemStatsOutFragOKs not implemented';
$config['syslog_filter'][] = 'diskio.c';
// Ignore some crappy stuff from SNMP daemon
// Virtualization
$config['enable_libvirt'] = 0;
// Enable Libvirt VM support
$config['libvirt_protocols'] = array(
    'qemu+ssh',
    'xen+ssh',
);
// Mechanisms used, add or remove if not using this on any of your machines.
// Hardcoded ASN descriptions
$config['astext'][65332] = 'Cymru FullBogon Feed';
$config['astext'][65333] = 'Cymru Bogon Feed';

// Nicer labels for the SLA types
$config['sla_type_labels']['echo']              = 'ICMP ping';
$config['sla_type_labels']['pathEcho']          = 'Path ICMP ping';
$config['sla_type_labels']['fileIO']            = 'File I/O';
$config['sla_type_labels']['script']            = 'Script';
$config['sla_type_labels']['udpEcho']           = 'UDP ping';
$config['sla_type_labels']['tcpConnect']        = 'TCP connect';
$config['sla_type_labels']['http']              = 'HTTP';
$config['sla_type_labels']['dns']               = 'DNS';
$config['sla_type_labels']['jitter']            = 'Jitter';
$config['sla_type_labels']['dlsw']              = 'DLSW';
$config['sla_type_labels']['dhcp']              = 'DHCP';
$config['sla_type_labels']['ftp']               = 'FTP';
$config['sla_type_labels']['voip']              = 'VoIP';
$config['sla_type_labels']['rtp']               = 'RTP';
$config['sla_type_labels']['lspGroup']          = 'LSP group';
$config['sla_type_labels']['icmpjitter']        = 'ICMP jitter';
$config['sla_type_labels']['lspPing']           = 'LSP ping';
$config['sla_type_labels']['lspTrace']          = 'LSP trace';
$config['sla_type_labels']['ethernetPing']      = 'Ethernet ping';
$config['sla_type_labels']['ethernetJitter']    = 'Ethernet jitter';
$config['sla_type_labels']['lspPingPseudowire'] = 'LSP Pseudowire ping';

// Warnings on front page
$config['warn']['ifdown'] = true;
// Show down interfaces
// List of poller modules. Need to be in the array to be
// considered for execution.
$config['poller_modules']['unix-agent']                  = false;
$config['poller_modules']['os']                          = true;
$config['poller_modules']['ipmi']                        = true;
$config['poller_modules']['sensors']                     = true;
$config['poller_modules']['processors']                  = true;
$config['poller_modules']['mempools']                    = true;
$config['poller_modules']['storage']                     = true;
$config['poller_modules']['netstats']                    = true;
$config['poller_modules']['hr-mib']                      = true;
$config['poller_modules']['ucd-mib']                     = true;
$config['poller_modules']['ipSystemStats']               = true;
$config['poller_modules']['ports']                       = true;
$config['poller_modules']['bgp-peers']                   = true;
$config['poller_modules']['junose-atm-vp']               = false;
$config['poller_modules']['toner']                       = false;
$config['poller_modules']['ucd-diskio']                  = true;
$config['poller_modules']['wifi']                        = false;
$config['poller_modules']['wireless']                    = true;
$config['poller_modules']['ospf']                        = true;
$config['poller_modules']['cisco-ipsec-flow-monitor']    = false;
$config['poller_modules']['cisco-remote-access-monitor'] = false;
$config['poller_modules']['cisco-cef']                   = false;
$config['poller_modules']['cisco-sla']                   = false;
$config['poller_modules']['cisco-mac-accounting']        = false;
$config['poller_modules']['cipsec-tunnels']              = false;
$config['poller_modules']['cisco-ace-loadbalancer']      = false;
$config['poller_modules']['cisco-ace-serverfarms']       = false;
$config['poller_modules']['cisco-asa-firewall']          = false;
$config['poller_modules']['cisco-voice']                 = false;
$config['poller_modules']['cisco-cbqos']                 = false;
$config['poller_modules']['cisco-otv']                   = false;
$config['poller_modules']['cisco-vpdn']                  = false;
$config['poller_modules']['nac']                         = false;
$config['poller_modules']['netscaler-vsvr']              = false;
$config['poller_modules']['aruba-controller']            = false;
$config['poller_modules']['entity-physical']             = true;
$config['poller_modules']['entity-state']                = false;
$config['poller_modules']['applications']                = true;
$config['poller_modules']['mib']                         = false;
$config['poller_modules']['stp']                         = true;
$config['poller_modules']['ntp']                         = true;
$config['poller_modules']['loadbalancers']               = false;
$config['poller_modules']['mef']                         = false;
$config['poller_modules']['mpls']                        = true;

// List of discovery modules. Need to be in this array to be
// considered for execution.
$config['discovery_modules']['os']                   = true;
$config['discovery_modules']['ports']                = true;
$config['discovery_modules']['ports-stack']          = true;
$config['discovery_modules']['entity-physical']      = true;
$config['discovery_modules']['entity-state']         = false;
$config['discovery_modules']['processors']           = true;
$config['discovery_modules']['mempools']             = true;
$config['discovery_modules']['cisco-vrf-lite']       = true;
$config['discovery_modules']['cisco-mac-accounting'] = false;
$config['discovery_modules']['cisco-pw']             = false;
$config['discovery_modules']['vrf']                  = false;
$config['discovery_modules']['cisco-cef']            = false;
$config['discovery_modules']['cisco-sla']            = false;
$config['discovery_modules']['cisco-cbqos']          = false;
$config['discovery_modules']['cisco-otv']            = false;
$config['discovery_modules']['ipv4-addresses']       = true;
$config['discovery_modules']['ipv6-addresses']       = true;
$config['discovery_modules']['route']                = false;
$config['discovery_modules']['sensors']              = true;
$config['discovery_modules']['storage']              = true;
$config['discovery_modules']['hr-device']            = true;
$config['discovery_modules']['discovery-protocols']  = true;
$config['discovery_modules']['arp-table']            = true;
$config['discovery_modules']['discovery-arp']        = false;
$config['discovery_modules']['junose-atm-vp']        = false;
$config['discovery_modules']['bgp-peers']            = true;
$config['discovery_modules']['vlans']                = true;
$config['discovery_modules']['vmware-vminfo']        = false;
$config['discovery_modules']['libvirt-vminfo']       = false;
$config['discovery_modules']['toner']                = false;
$config['discovery_modules']['ucd-diskio']           = true;
$config['discovery_modules']['applications']         = false;
$config['discovery_modules']['services']             = true;
$config['discovery_modules']['stp']                  = true;
$config['discovery_modules']['ntp']                  = true;
$config['discovery_modules']['loadbalancers']        = false;
$config['discovery_modules']['mef']                  = false;
$config['discovery_modules']['wireless']             = true;
$config['discovery_modules']['fdb-table']            = true;
$config['discovery_modules']['mpls']                 = true;
// Enable daily updates
$config['update'] = 1;

// Sets automatic sensor limits when no values are returned by the device.
$config['sensors']['guess_limits']                   = true;

// Purge syslog and eventlog
$config['syslog_purge'] = 30;
// Number in days of how long to keep syslog entries for.
$config['eventlog_purge'] = 30;
// Number in days of how long to keep eventlog entries for.
$config['authlog_purge'] = 30;
// Number in days of how long to keep authlog entries for.
$config['perf_times_purge'] = 30;
// Number in days of how long to keep performace polling stats  entries for.
$config['device_perf_purge'] = 7;
// Number in days of how long to keep device performance data for.
$config['alert_log_purge'] = 365;
// Number in days of how long to keep alert log data for.
$config['ports_fdb_purge'] = 10;
// Number in days of how long to keep fdb table data for.

// Date format for PHP date()s
$config['dateformat']['long'] = 'r';
// RFC2822 style
$config['dateformat']['compact']  = 'Y-m-d H:i:s';
$config['dateformat']['byminute'] = 'Y-m-d H:i';
$config['dateformat']['time']     = 'H:i:s';

// Date format for MySQL DATE_FORMAT
$config['dateformat']['mysql']['compact'] = '%Y-%m-%d %H:%i:%s';
$config['dateformat']['mysql']['date']    = '%Y-%m-%d';
$config['dateformat']['mysql']['time']    = '%H:%i:%s';

$config['enable_clear_discovery'] = 1;
// Set this to 0 if you want to disable the web option to rediscover devices
$config['force_ip_to_sysname']          = false;// Set to true if you want to use sysName in place of IPs
$config['force_hostname_to_sysname']    = false;// Set to true if you want to use sysNAme in place of a hostname, ie Dynamic DNS

// Allow duplicate devices by sysName
$config['allow_duplicate_sysName'] = false;// Set to true if you want to allow duplicate sysName's

$config['enable_port_relationship'] = true;
// Set this to false to not display neighbour relationships for ports
$config['enable_footer'] = false;
// Set this to true if you want to enable the footer in the web interface
$config['api_demo'] = 0;
// Set this to 1 if you want to disable some untrusting features for the API
// Distributed Poller-Settings
$config['distributed_poller']                = false;
$config['distributed_poller_name']           = php_uname('n');
$config['distributed_poller_group']          = 0;
$config['distributed_poller_memcached_host'] = 'example.net';
$config['distributed_poller_memcached_port'] = '11211';

// BETA polller service config options.
// See https://docs.librenms.org/Extensions/Poller-Service/ for more information
//$config['service_poller_workers']              = 24;     # Processes spawned for polling
//$config['service_services_workers']            = 8;      # Processes spawned for service polling
//$config['service_discovery_workers']           = 16;     # Processes spawned for discovery
// Optional BETA polller service Settings
//$config['service_poller_frequency']            = 300;    # Seconds between polling attempts
//$config['service_services_frequency']          = 300;    # Seconds between service polling attempts
//$config['service_discovery_frequency']         = 21600;  # Seconds between discovery runs
//$config['service_billing_frequency']           = 300;    # Seconds between billing calculations
//$config['service_billing_calculate_frequency'] = 60;     # Billing interval
//$config['service_poller_down_retry']           = 60;     # Seconds between failed polling attempts
//$config['service_loglevel']                    = 'INFO'; # Must be one of 'DEBUG', 'INFO', 'WARNING', 'ERROR', 'CRITICAL'

// Stats callback system
$config['callback_post']  = 'https://stats.librenms.org/log.php';
$config['callback_clear'] = 'https://stats.librenms.org/clear.php';

// Stat graphs
$config['alert_graph_date_format'] = '%Y-%m-%d %H:%i';

// IPMI type
$config['ipmi']['type'][] = 'lanplus';
$config['ipmi']['type'][] = 'lan';
$config['ipmi']['type'][] = 'imb';
$config['ipmi']['type'][] = 'open';

// Options needed for dynamic config - do NOT edit
$config['email_backend_options'] = array(
    'mail',
    'sendmail',
    'smtp',
);
$config['email_smtp_secure_options'] = array(
    '',
    'tls',
    'ssl',
);

// Unix-agent poller module config settings
$config['unix-agent-connection-time-out'] = 10;
// seconds
$config['unix-agent-read-time-out'] = 10;
// seconds

// Lat / Lon support for maps
#$config['geoloc']['latlng']                             = true; // True to enable translation of location to latlng co-ordinates
#$config['geoloc']['engine']                             = 'google';
$config['map']['engine']                                = 'leaflet';
$config['mapael']['default_map']                        = 'maps/world_countries.js';
$config['leaflet']['default_lat']                       = '51.4800';
$config['leaflet']['default_lng']                       = '0';
$config['leaflet']['default_zoom']                      = 2;
$config['leaflet']['tile_url']                          = "{s}.tile.openstreetmap.org";

// General GUI options
$config['gui']['network-map']['style']                  = 'new';//old is also valid

// Show errored ports in the summary table on the dashboard
$config['summary_errors']                               = 0;

// Default width of the availability map's tiles
$config['availability-map-width']                       = 25;

// Default notifications Feed
$config['notifications']['LibreNMS']                    = 'http://www.librenms.org/notifications.rss';
$config['notifications']['local']                       = 'misc/notifications.rss';

// Update channel (Can be 'master' or 'release')
$config['update_channel']                               = 'master';

// Default port association mode
$config['default_port_association_mode'] = 'ifIndex';
// Ignore ports which can't be mapped using a devices port_association_mode
// See include/polling/ports.inc.php for a lenghty explanation.
$config['ignore_unmapable_port'] = false;

// InfluxDB default configuration
$config['influxdb']['timeout']      = 0;
$config['influxdb']['verifySSL']    = false;

// Prometheus Push Default configuration
$config['prometheus']['job']        = 'librenms';

// Xirrus - Disable station/client polling if true as it may take a long time on larger/heavily used APs.
$config['xirrus_disable_stations']  = false;

// Graphite default port
$config['graphite']['port']         = 2003;

// API config
$config['api']['cors']['enabled'] = false;
$config['api']['cors']['origin'] = '*';
$config['api']['cors']['maxage'] = '86400';
$config['api']['cors']['allowmethods'] = array('POST', 'GET', 'PUT', 'DELETE', 'PATCH');
$config['api']['cors']['allowheaders'] = array('Origin', 'X-Requested-With', 'Content-Type', 'Accept', 'X-Auth-Token');

// Disk
$config['bad_disk_regexp'] = [];

// Snmptrap logging: none, unhandled, all
$config['snmptraps']['eventlog'] = 'unhandled';
