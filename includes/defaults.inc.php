<?php

### Default directories

$config['temp_dir']      = "/tmp";
$config['install_dir']   = "/opt/observium";
$config['html_dir']      = $config['install_dir'] . "/html";
$config['rrd_dir']       = $config['install_dir'] . "/rrd";
$config['log_file']      = $config['install_dir'] . "/observium.log";

### Location of executables

$config['sipcalc']        = "/usr/bin/sipcalc";
$config['rrdtool']        = "/usr/bin/rrdtool";
$config['fping']          = "/usr/bin/fping";
$config['ipcalc']         = "/usr/bin/ipcalc";
$config['snmpwalk']       = "/usr/bin/snmpwalk";
$config['snmpget']        = "/usr/bin/snmpget";
$config['snmpbulkwalk']   = "/usr/bin/snmpbulkwalk";
$config['whois']          = "/usr/bin/whois";
$config['ping']           = "/bin/ping";
$config['mtr']            = "/usr/bin/mtr";
$config['nmap']           = "/usr/bin/nmap";
$config['nagios_plugins'] = "/usr/lib/nagios/plugins";

### RRDCacheD - Make sure it can write to your RRD dir!

#$config['rrdcached']    = "unix:/var/run/rrdcached.sock";

### Web Interface Settings

$config['title_image']      = "images/observium-logo.png";
$config['stylesheet']       = "css/styles.css";
$config['mono_font']        = "DejaVuSansMono";
$config['favicon']          = "images/observium-icon.png";
$config['header_color']     = "#1F334E";
$config['page_refresh']     = "300";  ## Refresh the page every xx seconds
$config['frong_page']       = "pages/front/default.php";
$config['page_title']       = "Observium :: Network Observation and Monitoring";
$config['timestamp_format'] = 'd-m-Y H:i:s';
$config['page_gen']         = 1;
$config['web_header']	    = "header.inc.php";  # in html/includes
$config['login_message']    = "Unauthorised access or use shall render the user liable to criminal and/or civil prosecution.";

$config['int_customers']           = 1;  # Enable Customer Port Parsing
$config['int_transit']             = 1;  # Enable Transit Types
$config['int_peering']             = 1;  # Enable Peering Types
$config['int_core']                = 1;  # Enable Core Port Types
$config['int_l2tp']                = 0;  # Enable L2TP Port Types

$config['show_locations']          = 1;  # Enable Locations on menu
$config['show_locations_dropdown'] = 1;  # Enable Locations dropdown on menu
$config['show_services']           = 1;  # Enable Services on menu
$config['ports_page_default']      = "details/"; ## eg "details/" "graphs/bits/"

### SNMP Settings
$config['snmp']['timeout'] = 300;          # timeout in ms
$config['snmp']['retries'] = 6;            # how many times to retry the query
$config['snmp']['community'][] = "public"; # Communities to try during discovery (dangerous)

### Autodiscovery Settings

$config['cdp_autocreate']         = false; ## Autodiscover unknown hosts seen via CDP
$config['snmp_autodiscovery']     = false; ## Autodiscover hosts on new subnets

### Alerting Settings

$config['alerts']['email']['default']      = NULL;    ## Default alert recipient
$config['alerts']['email']['default_only'] = FALSE;   ## Only use default recipient
$config['alerts']['email']['enable']	   = TRUE;    ## Enable email alerts

### Cosmetics

$config['rrdgraph_def_text']  = "-c BACK#EEEEEE00 -c SHADEA#EEEEEE00 -c SHADEB#EEEEEE00 -c FONT#000000 -c CANVAS#FFFFFF -c GRID#a5a5a5";
$config['rrdgraph_def_text'] .= " -c MGRID#FF9999 -c FRAME#5e5e5e -c ARROW#5e5e5e -R normal";
$config['overlib_defaults']   = ",FGCOLOR,'#ffffff', BGCOLOR, '#e5e5e5', BORDER, 5, CELLPAD, 4, CAPCOLOR, '#555555', TEXTCOLOR, '#3e3e3e'";

$list_colour_a = "#ffffff";
$list_colour_b = "#eeeeee";
$list_colour_a_a = "#f9f9f9";
$list_colour_a_b = "#f0f0f0";
$list_colour_b_a = "#f0f0f0";
$list_colour_b_b = "#e3e3e3";
$list_highlight  = "#ffcccc";
$warn_colour_a = "#ffeeee";
$warn_colour_b = "#ffcccc";

#$config['graph_colours'] = array("000066","330066","990066","990066","CC0033","FF0000"); ## Purple to Red
#$config['graph_colours'] = array("006600","336600","996600","996600","CC3300","FF0000"); ## Green to Red
#$config['graph_colours'] = array("002200","004400","006600","008800","00AA00","00CC00"); ## Green
#$config['graph_colours'] = array("220000","440000","660000","880000","AA0000","CC0000"); ## Red
#$config['graph_colours'] = array("001122","002244","003366","004488","0055AA","0066CC"); ## Blue
#$config['graph_colours'] = array("002233","004466","006699","0088CC","0099FF");          ## Sky-Blue
#$config['graph_colours'] = array("110022","330066","440088","6600AA","8800FF");          ## Purple
#$config['graph_colours'] = array("002200","004400","006600","008800","00AA00","00AA00","00CC00"); ## Forest Greens
#$config['graph_colours']['greens']  = array("112200","224400","336600","448800","66AA00","88FF00");          ## Grass Greens
#$config['graph_colours']['greens']  = array("95FFA7","4EFF97","33FF66","336600","224400","112200");
#$config['graph_colours']['greens'] = array("B7d6A9","8fcb73","50b91d","3ab419","0a8612","034f11");
#$config['graph_colours']['blues']   = array("b5d7ff","6eb7ff","0064ff","0082ff","0019d5","0016cb","00007d"); ## Cold Blues

$config['graph_colours']['mixed']   = array('CC0000','008C00','4096EE','73880A','D01F3C','36393D','FF0084');
$config['graph_colours']['greens']  = array('B6D14B','91B13C','6D912D','48721E','24520F','003300');
$config['graph_colours']['pinks']   = array('D0558F','B34773','943A57','792C38','5C1F1E','401F10');
$config['graph_colours']['blues']   = array('A0A0E5','8080BD','606096','40406F','202048','000033');
$config['graph_colours']['purples'] = array('CC7CCC','AF63AF','934A93','773177','5B185B','3F003F');
$config['graph_colours']['default'] = $config['graph_colours']['blues'];

### Device page options

$config['show_overview_tab'] = true;

### The device overview page options

$config['overview_show_sysDescr'] = true;

### Poller/Discovery Modules

$config['enable_port_Xbcmc']    	= 1; # Enable ifXEntry broadcast/multicast
$config['port_descr_parser']		= "includes/port-descr-parser.inc.php"; # Parse port descriptions into fields
$config['enable_bgp']           	= 1; # Enable BGP session collection and display
$config['enable_syslog']        	= 0; # Enable Syslog
$config['enable_billing']       	= 0; # Enable Billing
$config['enable_inventory']     	= 1; # Enable Inventory
$config['enable_etherlike']     	= 1; # Enable Etherlike
$config['enable_port_Xbcmc']    	= 1; # Enable ifXEntry broadcast/multicast
$config['enable_pseudowires']   	= 1; # Enable Pseudowires
$config['enable_ports_junoseatmvp'] 	= 0; # Enable JunOSe ATM VC Discovery/Poller
$config['enable_ports_etherlike'] 	= 0; # Enable Polling EtherLike-MIB (doubles interface processing time)
$config['enable_printers']  		= 0;  # Enable Printer support
$config['enable_ports_adsl']            = 1; # Enable ADSL-LINE-MIB

### External Integration

#$config['rancid_configs']       	= '/var/lib/rancid/network/configs/';
$config['rancid_ignorecomments'] 	= 0; # Ignore lines starting with #
#$config['collectd_dir']         	= '/var/lib/collectd/rrd';


### Ignores & Allows

$config['bad_if'] = array("voip-null", "virtual-", "unrouted", "eobc", "mpls", "sl0", "lp0", "faith0",
             "-atm layer", "-atm subif", "-shdsl", "-adsl", "-aal5", "-atm", "container",
             "async", "plip", "-physical", "-signalling", "control", "container", "unrouted",
             "bri", "-bearer", "bluetooth", "isatap", "ras", "qos", "miniport", "sonet/sdh",
             "span rp", "span sp", "sslvpn");

$config['bad_if_regexp'] = array("/serial[0-9]:/");

$config['processor_filter'][] = "An electronic chip that makes the computer work.";

$config['ignore_mount_removable'] = 1; # Ignore removable disk storage
$config['ignore_mount_network']   = 1; # Ignore network mounted storage
$config['ignore_mount_optical']   = 1; # Ignore mounted optical discs

$config['device_traffic_iftype']  = array('/loopback/','/tunnel/','/virtual/','/mpls/');
$config['device_traffic_descr']   = array('/loopback/','/vlan/','/tunnel/','/:\d+/');

### Authentication

$config['allow_unauth_graphs']	  = 0; ## Allow graphs to be viewed by anyone
$config['auth_mechanism']         = "mysql";   # Auth Type.

### Sensors

$config['allow_entity_sensor']['amperes'] = 1;
$config['allow_entity_sensor']['celsius'] = 1;
$config['allow_entity_sensor']['dBm'] = 1;
$config['allow_entity_sensor']['voltsDC'] = 1;
$config['allow_entity_sensor']['voltsAC'] = 1;
$config['allow_entity_sensor']['watts'] = 1;
$config['allow_entity_sensor']['truthvalue'] = 1;
$config['allow_entity_sensor']['specialEnum'] = 1;

### Set default alert limits for various sensors and metrics

$config['limit']['fan']  = "1000";
$config['limit']['temp'] = "60";

### Filesystems

$config['ignore_mount'] = array("/kern", "/mnt/cdrom", "/proc", "/dev");
$config['ignore_mount_string'] = array("packages", "devfs", "procfs", "UMA", "MALLOC");
$config['ignore_mount_regexp'] = array();
$config['ignore_mount_removable'] = 1; # Ignore removable disk storage
$config['ignore_mount_network']   = 1; # Ignore network mounted storage
$config['ignore_junos_os_drives'] = array("/on: \/packages/", "/on: \/dev/", "/on: \/proc/", "/on: \/junos^/", "/on: \/junos\/dev/", "/on: \/jail\/dev/", "/^(dev|proc)fs/", "/^\/dev\/md0/"); # Ignore JunOS partitions who are always 100%
$config['ignore_bsd_os_drives']   = array("/^\/dev,/", "/^\/var\/dhcpd\/dev,/", "/UMA/");  # Ignore BSD partitions who are always 100%

### Poller/Discovery

$config['enable_ports_etherlike']       = 0; # Enable EtherLike-MIB
$config['enable_ports_junoseatmvp']     = 0; # Enable JunOSe ATM VC Discovery/Poller

### Syslog Settings

$config['syslog_age']       = "1 month";        ## Entries older than this will be removed
$config['syslog_filter'] = array("last message repeated", "Connection from UDP: [",
                                 "ipSystemStatsTable node ipSystemStatsOutFragOKs not implemented",
                                 "diskio.c: don't know how to handle");  ## Ignore some crappy stuff from SNMP daemon

### LDAP Authentication

$config['auth_ldap_version'] = 3; # v2 or v3

### Hardcoded ASN descriptions
$config['astext'][65333] = "Cymru Bogon Feed";

### Default warning settings

$config['defaults']['temp_limit'] = 60;

### What should we warn about?
$config['warn']['ifdown'] = false;


?>
