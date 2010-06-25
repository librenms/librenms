<?php

## ifDescr whitelist (used instead of ifName)
$config['ifname']['asa'] = true;
$config['ifname']['catos'] = true;
$config['ifname']['windows'] = true;
$config['ifname']['speedtouch'] = true;
$config['ifname']['dell-laser'] = true;
$config['ifname']['powerconnect'] = true;
$config['ifalias']['adva'] = true;
#$config['appendifindex']['adva'] = true;

## AFI / SAFI pairs for BGP (and other stuff, perhaps)
$config['afi']['ipv4']['unicast']    = "IPv4";
$config['afi']['ipv4']['multiicast'] = "IPv4 Multicast";
$config['afi']['ipv4']['vpn']        = "VPNv4";
$config['afi']['ipv6']['unicast']    = "IPv6";
$config['afi']['ipv6']['multicast']  = "IPv6 Multicast";

## Don't bulkwalk for these OS types
$config['nobulkwalk'] = array("netmanplus");

$config['os']['linux']['group'] = "unix";
$config['os']['linux']['text']  = "Linux";
$config['os']['freebsd']['group'] = "unix";
$config['os']['freebsd']['text']  = "FreeBSD";
$config['os']['openbsd']['group'] = "unix";
$config['os']['openbsd']['text']  = "OpenBSD";
$config['os']['netbsd']['group'] = "unix";
$config['os']['netbsd']['text']  = "NetBSD";
$config['os']['dragonfly']['group'] = "unix";
$config['os']['dragonfly']['text']  = "DragonflyBSD";
$config['os']['monowall']['group'] = "unix";
$config['os']['monowall']['text']  = "m0n0wall";
$config['os']['solaris']['group'] = "unix";
$config['os']['solaris']['text']  = "Sun Solaris";
$config['os']['opensolaris']['group'] = "unix";
$config['os']['opensolaris']['text']  = "Adva";
$config['os']['opensolaris']['group'] = "unix";
$config['os']['opensolaris']['text']  = "Sun OpenSolaris";

$config['os']['ios']['group'] = "ios";
$config['os']['ios']['text']  = "Cisco IOS";
$config['os']['iosxe']['group'] = "ios";
$config['os']['iosxe']['text']  = "Cisco IOS-XE";
$config['os']['iosxr']['group'] = "ios";
$config['os']['iosxr']['text']  = "Cisco IOS-XR";
$config['os']['asa']['group'] = "ios";
$config['os']['asa']['text']  = "Cisco ASA";
$config['os']['pix']['group'] = "ios";
$config['os']['pix']['text']  = "Cisco PIXOS";
$config['os']['nxos']['group'] = "ios";
$config['os']['nxos']['text']  = "Cisco NX-OS";
$config['os']['catos']['group'] = "ios";
$config['os']['catos']['text']  = "Cisco CatOS";

$config['os']['junos']['text'] = "Juniper JunOS";
$config['os']['junose']['text']  = "Juniper JunOSe";

$config['os']['mgeups']['group'] = "ios";
$config['os']['mgeups']['text']  = "Cisco IOS";
$config['os']['netmanplus']['group'] = "ios";
$config['os']['netmanplus']['text']  = "";
$config['os']['generic']['text']        = "Generic Device";
$config['os']['ironware']['text']       = "Brocade IronWare";
$config['os']['powerconnect']['text']   = "Dell PowerConnect";
$config['os']['windows']['text']        = "Microsoft Windows";
$config['os']['procurve']['text']       = "HP ProCurve";
$config['os']['speedtouch']['text']     = "Thomson Speedtouch";
$config['os']['allied']['text']         = "AlliedWare";

$config['os']['mgeups']['text']         = "MGE UPS";
$config['os']['mgeups']['group']        = "ups";

$config['os']['apc']['text']            = "APC Management Module";
$config['os']['areca']['text']          = "Areca RAID Subsystem";
$config['os']['netmanplus']['text']     = "NetMan Plus";
$config['os']['netmanplus']['group']	= "ups";
$config['os']['akcp']['text']           = "AKCP SensorProbe";
$config['os']['minkelsrms']['text']     = "Minkels RMS";
$config['os']['papouch-tme']['text']    = "Papouch TME";

$config['os']['dell-laser']['group'] = "printer";
$config['os']['dell-laser']['text']  = "Dell Laser";


if(!$config['graph_colours']['greens']) {
  $config['graph_colours']['greens']  = array('B6D14B','91B13C','6D912D','48721E','24520F','003300');
}
if(!$config['graph_colours']['pinks']) {
  $config['graph_colours']['pinks']   = array('D0558F','B34773','943A57','792C38','5C1F1E','401F10');
}
if(!$config['graph_colours']['blues']) {
  $config['graph_colours']['blues']   = array('A0A0E5','8080BD','606096','40406F','202048','000033');
}
if(!$config['graph_colours']['purples']) {
  $config['graph_colours']['purples'] = array('CC7CCC','AF63AF','934A93','773177','5B185B','3F003F');
}
if(!$config['graph_colours']['default']) {
  $config['graph_colours']['default'] = $config['graph_colours']['blues'];
}
if(!$config['graph_colours']['mixed']) {
  $config['graph_colours']['mixed']  = array("CC0000", "008C00", "4096EE", "73880A", "D01F3C", "36393D", "FF0084");
}

$device_types = array('server','network','firewall','workstation','printer','power', 'environment');

## Popup graphs

# Default
$popgraph['default'][] = 'device_cpu';
$popgraph['default'][] = 'device_memory';
$popdescr['default'] = "CPU &amp; Memory Usage";

$popgraph['apc'][] = 'device_current';
$popdescr['apc'] = "Current";

$popgraph['mgeups'][] = 'device_current';
$popdescr['mgeups'] = "Current";

$popgraph['dell-laser'][] = 'device_toner';
$popdescr['dell-laser'] = "Toner";

#$popgraph['areca'][] = 'device_';
#$popdescr['areca'] = "";

$popgraph['netmanplus'][] = 'device_current';
$popdescr['netmanplus'] = "Current";

$popgraph['akcp'][] = 'device_temperatures';
$popdescr['akcp'] = "Temperature";

$popgraph['minkelsrms'] = $popgraph['akcp'];
$popdescr['minkelsrms'] = $popdescr['akcp'];

$popgraph['papouch-tme'][] = 'device_temperatures';
$popdescr['papouch-tme'] = "Temperature";

##############################
# No changes below this line #
##############################

$config['version'] = "0.10";

$config['rrd_opts_array'] = explode(" ", trim($config['rrdgraph_def_text']));

if(isset($config['enable_nagios']) && $config['enable_nagios']) {
  $nagios_link = mysql_connect($config['nagios_db_host'], $config['nagios_db_user'], $config['nagios_db_pass']);
  if (!$nagios_link) {
    echo "<h2>Nagios MySQL Error</h2>";
    die;
}
$nagios_db = mysql_select_db($config['nagios_db_name'], $nagios_link);
}

# If we're on SSL, let's properly detect it
if(isset($_SERVER['HTTPS'])) {
  $config['base_url'] = preg_replace('/^http:/','https:', $config['base_url']);
}

### Connect to database
$observer_link = mysql_pconnect($config['db_host'], $config['db_user'], $config['db_pass']);
if (!$observer_link) {
        echo "<h2>Observer MySQL Error</h2>";
        echo mysql_error();
        die;
}
$observer_db = mysql_select_db($config['db_name'], $observer_link);

# Set some times needed by loads of scripts (it's dynamic, so we do it here!)

$now = time();
$day = time() - (24 * 60 * 60);
$twoday = time() - (2 * 24 * 60 * 60);
$week = time() - (7 * 24 * 60 * 60);
$month = time() - (31 * 24 * 60 * 60);
$year = time() - (365 * 24 * 60 * 60);

$config['now'] = $now;
$config['day'] = $day;
$config['twoday'] = $twoday;
$config['week'] = $week;
$config['month'] = $month;
$config['year'] = $year;


?>
