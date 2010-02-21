<?php

### Temporary Directory for graph generation
$config['temp_dir']     = "/tmp";

### Location of executables

$config['sipcalc']      = "/usr/bin/sipcalc";
$config['rrdtool']      = "/usr/bin/rrdtool";
$config['fping']        = "/usr/bin/fping";
$config['ipcalc']       = "/usr/bin/ipcalc";
$config['snmpwalk']     = "/usr/bin/snmpwalk";
$config['snmpget']      = "/usr/bin/snmpget";
$config['snmpbulkwalk'] = "/usr/bin/snmpbulkwalk";

$config['nagios_plugins'] = "/usr/lib/nagios/plugins";

### Local Specifics

$config['title_image']  = "images/observer-logo.gif";
$config['stylesheet']   = "css/styles.css";
$config['mono_font']    = "DejaVuSansMono";
$config['favicon']      = "favicon.ico";
$config['header_color'] = "#1F334E";
$config['page_refresh'] = "30";  ## Refresh the page every xx seconds
$config['frong_page']   = "default.php";
$config['page_title']   = "ObserverNMS";
$config['syslog_age']    = "1 month";

### Cosmetics

$config['rrdgraph_def_text']  =  " -c BACK#EEEEEE00 -c SHADEA#EEEEEE00 -c SHADEB#EEEEEE00 -c FONT#000000 -c CANVAS#FFFFFF -c GRID#a5a5a5";
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

$config['graph_colours']['mixed']  = array("CC0000", "008C00", "4096EE", "73880A", "D01F3C", "36393D", "FF0084");
$config['graph_colours']['greens']  = array('B6D14B','91B13C','6D912D','48721E','24520F','003300');
$config['graph_colours']['pinks']   = array('D0558F','B34773','943A57','792C38','5C1F1E','401F10');
$config['graph_colours']['blues']   = array('A0A0E5','8080BD','606096','40406F','202048','000033');
$config['graph_colours']['purples'] = array('CC7CCC','AF63AF','934A93','773177','5B185B','3F003F');
$config['graph_colours']['default'] = $config['graph_colours']['blues'];

### Ignores & Allows

$config['bad_if'] = array("voip-null", "virtual-", "unrouted", "eobc", "mpls", "sl0", "lp0", "faith0",
             "-atm layer", "-atm subif", "-shdsl", "-adsl", "-aal5", "-atm", "container",
             "async", "plip", "-physical", "-signalling", "control", "container", "unrouted",
             "bri", "-bearer", "ng", "bluetooth", "isatap", "ras", "qos", "miniport", "sonet/sdh",
             "span rp", "span sp", "sslvpn");

$config['bad_if_regexp'] = array("/serial[0-9]:/");

$config['processor_filter'][] = "An electronic chip that makes the computer work.";


# Sensors

$config['allow_entity_sensor']['amperes'] = 1;
$config['allow_entity_sensor']['celsius'] = 1;
$config['allow_entity_sensor']['dBm'] = 1;
$config['allow_entity_sensor']['voltsDC'] = 1;
$config['allow_entity_sensor']['voltsAC'] = 1;
$config['allow_entity_sensor']['watts'] = 1;
$config['allow_entity_sensor']['truthvalue'] = 1;
$config['allow_entity_sensor']['specialEnum'] = 1;

# Set default alert limits for various sensors and metrics

$config['limit']['fan']  = "1000";
$config['limit']['temp'] = "60";

# Filesystems

$config['ignore_mount'] = array("/kern", "/mnt/cdrom", "/proc");
$config['ignore_mount_string'] = array("packages", "devfs", "procfs", "UMA", "MALLOC");
$config['ignore_mount_regexp'] = array();
$config['ignore_mount_removable'] = 1; # Ignore removable disk storage
$config['ignore_mount_network']   = 1; # Ignore network mounted storage
$config['ignore_junos_os_drives'] = array("/, \/packages/", "/, \/dev/", "/, \/proc/"); # Ignore JunOS partitions who are always 100%

?>
