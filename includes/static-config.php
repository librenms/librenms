<?php

## ifDescr whitelist (used instead of ifName)
$config['ifname']['asa'] = true;
$config['ifname']['catos'] = true;
$config['ifname']['windows'] = true;
$config['ifname']['speedtouch'] = true;
$config['ifname']['dell-laser'] = true;
$config['ifalias']['adva'] = true;
#$config['appendifindex']['adva'] = true;

## AFI / SAFI pairs for BGP (and other stuff, perhaps)
$config['afi']['ipv4']['unicast']    = "IPv4";
$config['afi']['ipv4']['multiicast'] = "IPv4 Multicast";
$config['afi']['ipv4']['vpn']        = "VPNv4";
$config['afi']['ipv6']['unicast']    = "IPv6";
$config['afi']['ipv6']['multicast']  = "IPv6 Multicast";

## Set OS Groups

$os_groups['linux']     = "unix";
$os_groups['freebsd']   = "unix";
$os_groups['openbsd']   = "unix";
$os_groups['netbsd']    = "unix";
$os_groups['dragonfly'] = "unix";
$os_groups['monowall']  = "unix";
$os_groups['solaris']   = "unix";
$os_groups['adva']	= "unix";
$os_groups['opensolaris'] = "unix";

$os_groups['iosxe']     = "ios";
$os_groups['iosxr']     = "ios";
$os_groups['ios']	= "ios";
$os_groups['asa']       = "ios";

$os_groups['printers']  = "dell-laser";

##
$os_text['linux']	 = "Linux";
$os_text['ios']       	 = "Cisco IOS";
$os_text['iosxr']        = "Cisco IOS XE";
$os_text['iosxe']        = "Cisco IOS XR";
$os_text['catos']        = "Cisco CatOS";
$os_text['nxos']         = "Cisco NX-OS";
$os_text['asa']          = "Cisco ASA";
$os_text['pix']          = "Cisco PIX";
$os_text['generic']	 = "Generic Device";
$os_text['ironware']	 = "Brocade IronWare";
$os_text['monowall']	 = "m0n0wall";
$os_text['freebsd']      = "FreeBSD";
$os_text['openbsd']      = "OpenBSD";
$os_text['netbsd']       = "NetBSD";
$os_text['dragonflybsd'] = "DragonFlyBSD";
$os_text['solaris']	 = "Sun Solaris";
$os_text['opensolaris']  = "OpenSolaris";
$os_text['powerconnect'] = "Dell PowerConnect";
$os_text['windows']      = "Microsoft Windows";
$os_text['junos']        = "Juniper JunOS";
$os_text['junose']	 = "Juniper JunOSe";
$os_text['procurve']     = "HP ProCurve";
$os_text['speedtouch']   = "Thomson Speedtouch";
$os_text['dell-laser']   = "Dell Laser Printer";
$os_text['adva']	 = "Adva";
$os_text['allied']	 = "AlliedWare";

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


##############################
# No changes below this line #
##############################

$config['version'] = "0.10.0";

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
