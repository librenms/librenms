<?php

## AFI / SAFI pairs for BGP (and other stuff, perhaps)
$config['afi']['ipv4']['unicast']    = "IPv4";
$config['afi']['ipv4']['multiicast'] = "IPv4 Multicast";
$config['afi']['ipv4']['vpn']        = "VPNv4";
$config['afi']['ipv6']['unicast']    = "IPv6";
$config['afi']['ipv6']['multicast']  = "IPv6 Multicast";

$config['os']['default']['overgraph'][]	= "device_cpu";
$config['os']['default']['overgraph'][] = "device_memory";
$config['os']['default']['overtext']	= "CPU &amp; Memory Usage";

$config['os']['linux']['group'] 	= "unix";
$config['os']['linux']['text']  	= "Linux";
$config['os']['linux']['ifXmcbc']	= 1;

$config['os']['freebsd']['group'] 	= "unix";
$config['os']['freebsd']['text']  	= "FreeBSD";

$config['os']['openbsd']['group'] 	= "unix";
$config['os']['openbsd']['text']  	= "OpenBSD";

$config['os']['netbsd']['group'] 	= "unix";
$config['os']['netbsd']['text']  	= "NetBSD";

$config['os']['dragonfly']['group'] 	= "unix";
$config['os']['dragonfly']['text']  	= "DragonflyBSD";

$config['os']['netware']['text']  	= "Novell Netware";
$config['os']['netware']['icon']  	= "novell";

$config['os']['monowall']['group'] 	= "unix";
$config['os']['monowall']['text']  	= "m0n0wall";
$config['os']['monowall']['type']  	= "firewall";

$config['os']['solaris']['group'] 	= "unix";
$config['os']['solaris']['text']  	= "Sun Solaris";

$config['os']['adva']['group'] 		= "unix";
$config['os']['adva']['text']  		= "Adva";
$config['os']['adva']['ifalias']	= 1;

$config['os']['opensolaris']['group']	= "unix";
$config['os']['opensolaris']['text']	= "Sun OpenSolaris";

$config['os']['ios']['group']		= "ios";
$config['os']['ios']['text']		= "Cisco IOS";
$config['os']['ios']['type']		= "network";
$config['os']['ios']['ifXmcbc']         = 1;

$config['os']['iosxe']['group']		= "ios";
$config['os']['iosxe']['text']		= "Cisco IOS-XE";
$config['os']['iosxe']['type']		= "network";
$config['os']['iosxe']['ifXmcbc']       = 1;

$config['os']['iosxr']['group']		= "ios";
$config['os']['iosxr']['text']		= "Cisco IOS-XR";
$config['os']['iosxr']['type']		= "network";
$config['os']['iosxr']['ifXmcbc']       = 1;

$config['os']['asa']['group']		= "ios";
$config['os']['asa']['text']		= "Cisco ASA";
$config['os']['asa']['ifname']		= 1;
$config['os']['asa']['type']		= "firewall";

$config['os']['pix']['group'] 		= "ios";
$config['os']['pix']['text']		= "Cisco PIX-OS";
$config['os']['pix']['ifname']		= 1;
$config['os']['pix']['type']		= "firewall";

$config['os']['nxos']['group'] 		= "ios";
$config['os']['nxos']['text']  		= "Cisco NX-OS";
$config['os']['nxos']['type']  		= "network";

$config['os']['catos']['group']		= "ios";
$config['os']['catos']['text']		= "Cisco CatOS";
$config['os']['catos']['ifname']	= 1;
$config['os']['catos']['type']		= "network";

$config['os']['junos']['text']		= "Juniper JunOS";
$config['os']['junos']['type']		= "network";

$config['os']['screenos']['text']	= "Juniper ScreenOS";
$config['os']['screenos']['type']	= "firewall";

$config['os']['routeros']['text']	= "Mikrotik RouterOS";
$config['os']['routeros']['type']	= "network";
$config['os']['routeros']['nobulk']	= 1;

$config['os']['junose']['text']		= "Juniper JunOSe";
$config['os']['junose']['type']		= "network";

$config['os']['generic']['text']        = "Generic Device";

$config['os']['ironware']['text']       = "Brocade IronWare";
$config['os']['ironware']['type']       = "network";

$config['os']['extremeware']['text']    = "Extremeware";
$config['os']['extremeware']['type']    = "network";
$config['os']['extremeware']['ifname']  = 1;

$config['os']['powerconnect']['text']   = "Dell PowerConnect";
$config['os']['powerconnect']['ifname'] = 1;
$config['os']['powerconnect']['type']   = "network";
$config['os']['powerconnect']['icon']   = "dell";

$config['os']['powervault']['text']     = "Dell PowerVault";
$config['os']['powervault']['icon']     = "dell";

$config['os']['drac']['text']           = "Dell DRAC";
$config['os']['drac']['icon']           = "dell";

$config['os']['bcm963']['text']		= "Broadcom BCM963xxx";
$config['os']['bcm963']['icon']		= "broadcom";

$config['os']['netopia']['text']        = "Motorola Netopia";
$config['os']['netopia']['type']        = "network";

$config['os']['dlink']['text']          = "D-Link Switch";
$config['os']['dlink']['type']          = "network";
$config['os']['dlink']['icon']          = "dlink";

$config['os']['dlinkap']['text']        = "D-Link Access Point";
$config['os']['dlinkap']['type']        = "network";
$config['os']['dlinkap']['icon']        = "dlink";

$config['os']['axiscam']['text']        = "AXIS Network Camera";

$config['os']['axisdocserver']['text']  = "AXIS Network Document Server";

$config['os']['gamatronicups']['text']  = "Gamatronic UPS Stack";
$config['os']['gamatronicups']['type']  = "power";

$config['os']['airport']['type'] 	= "network";
$config['os']['airport']['text']  	= "Apple AirPort";

$config['os']['windows']['text']        = "Microsoft Windows";
$config['os']['windows']['ifname']	= 1;

$config['os']['procurve']['text']       = "HP ProCurve";
$config['os']['procurve']['type']       = "network";

$config['os']['speedtouch']['text']     = "Thomson Speedtouch";
$config['os']['speedtouch']['ifname']	= 1;
$config['os']['speedtouch']['type']     = "network";

$config['os']['sonicwal']['text']     	= "SonicWALL";
$config['os']['sonicwal']['type']     	= "firewall";
$config['os']['sonicwal']['overgraph'][]  = "device_bits";
$config['os']['sonicwal']['overtext']     = "Traffic";

$config['os']['zywall']['text']     	= "ZyXEL ZyWALL";
$config['os']['zywall']['type']     	= "firewall";
$config['os']['zywall']['overgraph'][]  = "device_bits";
$config['os']['zywall']['overtext']     = "Traffic";
$config['os']['zywall']['icon']         = "zyxel";

$config['os']['prestige']['text']     	= "ZyXEL Prestige";
$config['os']['prestige']['type']     	= "network";
$config['os']['prestige']['icon']       = "zyxel";

$config['os']['zyxeles']['text']     	= "ZyXEL Ethernet Switch";
$config['os']['zyxeles']['type']     	= "network";
$config['os']['zyxeles']['icon']        = "zyxel";

$config['os']['ies']['text']     	= "ZyXEL IES DSLAM";
$config['os']['ies']['type']     	= "network";
$config['os']['ies']['icon']            = "zyxel";

$config['os']['allied']['text']         = "AlliedWare";
$config['os']['allied']['type']         = "network";

$config['os']['mgeups']['text']         = "MGE UPS";
$config['os']['mgeups']['group']        = "ups";
$config['os']['mgeups']['overgraph'][]	= "device_current";
$config['os']['mgeups']['overtext']	= "Current";
$config['os']['mgeups']['type']		= "power";
$config['os']['mgeups']['icon']		= "mge";

$config['os']['mgepdu']['text']         = "MGE PDU";
$config['os']['mgepdu']['type']		= "power";
$config['os']['mgepdu']['icon']		= "mge";

$config['os']['apc']['text']            = "APC Management Module";
$config['os']['apc']['overgraph'][]	= "device_current";
$config['os']['apc']['overtext']	= "Current";
$config['os']['apc']['type']		= "power";

$config['os']['areca']['text']          = "Areca RAID Subsystem";
$config['os']['areca']['overgraph'][]	= "";
$config['os']['areca']['overtext']	= "";

$config['os']['netmanplus']['text']        = "NetMan Plus";
$config['os']['netmanplus']['group']	   = "ups";
$config['os']['netmanplus']['nobulk']	   = 1;
$config['os']['netmanplus']['overgraph'][] = "device_current";
$config['os']['netmanplus']['overtext']    = "Current";
$config['os']['netmanplus']['type']	   = "power";

$config['os']['akcp']['text']              = "AKCP SensorProbe";
$config['os']['akcp']['overgraph'][]	   = "device_temperatures";
$config['os']['akcp']['overtext']          = "Temperature";
$config['os']['akcp']['type']              = "environment";

$config['os']['minkelsrms']['text']        = "Minkels RMS";
$config['os']['minkelsrms']['overgraph'][] = "device_temperatures";
$config['os']['minkelsrms']['overtext']    = "Temperature";
$config['os']['minkelsrms']['type']        = "environment";

$config['os']['papouch-tme']['text']       = "Papouch TME";
$config['os']['papouch-tme']['overgraph'][] = "device_temperatures";
$config['os']['papouch-tme']['overtext']   = "Temperature";
$config['os']['papouch-tme']['type']       = "environment";

$config['os']['dell-laser']['group'] 	   = "printer";
$config['os']['dell-laser']['text']  	   = "Dell Laser";
$config['os']['dell-laser']['overgraph'][] = "device_toner";
$config['os']['dell-laser']['overtext']    = "Toner";
$config['os']['dell-laser']['ifname']	   = 1;
$config['os']['dell-laser']['type']        = "printer";
$config['os']['dell-laser']['icon']        = "dell";

$config['os']['xerox']['group'] 	   = "printer";
$config['os']['xerox']['text']  	   = "Xerox Printer";
$config['os']['xerox']['overgraph'][]      = "device_toner";
$config['os']['xerox']['overtext']         = "Toner";
$config['os']['xerox']['ifname']	   = 1;
$config['os']['xerox']['type']             = "printer";

$config['os']['jetdirect']['group'] 	   = "printer";
$config['os']['jetdirect']['text']  	   = "HP Printer";
$config['os']['jetdirect']['overgraph'][]  = "device_toner";
$config['os']['jetdirect']['overtext']     = "Toner";
$config['os']['jetdirect']['ifname']	   = 1;
$config['os']['jetdirect']['type']         = "printer";

$config['os']['ricoh']['group'] 	   = "printer";
$config['os']['ricoh']['text']  	   = "Ricoh Printer";
$config['os']['ricoh']['overgraph'][]      = "device_toner";
$config['os']['ricoh']['overtext']         = "Toner";
$config['os']['ricoh']['type']             = "printer";

$config['os']['brother']['group'] 	   = "printer";
$config['os']['brother']['text']  	   = "Brother Printer";
$config['os']['brother']['overgraph'][]    = "device_toner";
$config['os']['brother']['overtext']       = "Toner";
$config['os']['brother']['type']           = "printer";

$config['os']['konica']['group'] 	   = "printer";
$config['os']['konica']['text']  	   = "Konica-Minolta Printer";
$config['os']['konica']['overgraph'][]     = "device_toner";
$config['os']['konica']['overtext']        = "Toner";
$config['os']['konica']['ifname']	   = 1;
$config['os']['konica']['type']            = "printer";

$config['os']['kyocera']['group'] 	   = "printer";
$config['os']['kyocera']['text']  	   = "Kyocera Mita Printer";
$config['os']['kyocera']['overgraph'][]    = "device_toner";
$config['os']['kyocera']['overtext']       = "Toner";
$config['os']['kyocera']['ifname']	   = 1;
$config['os']['kyocera']['type']           = "printer";

$config['os']['3com']['text']              = "3Com";
$config['os']['3com']['overgraph'][]       = "";
$config['os']['3com']['overtext']          = "";
$config['os']['3com']['type']              = "network";

$device_types = array('server','network','firewall','workstation','printer','power', 'environment');

##############################
# No changes below this line #
##############################

$config['version'] = "0.10";

$config['rrdgraph_def_text'] = str_replace("  ", " ", $config['rrdgraph_def_text']);
$config['rrd_opts_array'] = explode(" ", trim($config['rrdgraph_def_text']));

if(!$config['mibdir']) 
{
  $config['mibdir'] =  $config['install_dir']."/mibs/";
}

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
$observium_link = mysql_pconnect($config['db_host'], $config['db_user'], $config['db_pass']);
if (!$observium_link) {
        echo "<h2>Observer MySQL Error</h2>";
        echo mysql_error();
        die;
}
$observium_db = mysql_select_db($config['db_name'], $observium_link);

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
