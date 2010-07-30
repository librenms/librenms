<?php

## AFI / SAFI pairs for BGP (and other stuff, perhaps)
$config['afi']['ipv4']['unicast']    = "IPv4";
$config['afi']['ipv4']['multiicast'] = "IPv4 Multicast";
$config['afi']['ipv4']['vpn']        = "VPNv4";
$config['afi']['ipv6']['unicast']    = "IPv6";
$config['afi']['ipv6']['multicast']  = "IPv6 Multicast";

$config['os']['default']['over'][0]['graph']	= "device_processors";
$config['os']['default']['over'][0]['text']	= "Processor Usage";
$config['os']['default']['over'][1]['graph'] 	= "device_mempools";
$config['os']['default']['over'][1]['text']	= "Memory Usage";

$os = "generic";
$config['os'][$os]['text']      	= "Generic Device";

$os = "linux";
$config['os'][$os]['group'] 		= "unix";
$config['os'][$os]['text']  		= "Linux";
$config['os'][$os]['ifXmcbc']		= 1;

$os = "freebsd";
$config['os'][$os]['group'] 		= "unix";
$config['os'][$os]['text']  		= "FreeBSD";

$os = "openbsd";
$config['os'][$os]['group'] 		= "unix";
$config['os'][$os]['text']  		= "OpenBSD";

$os = "netbsd";
$config['os'][$os]['group'] 		= "unix";
$config['os'][$os]['text']  		= "NetBSD";

$os = "dragonfly";
$config['os'][$os]['group'] 		= "unix";
$config['os'][$os]['text']  		= "DragonflyBSD";

$os = "netware";
$config['os'][$os]['text']  		= "Novell Netware";
$config['os'][$os]['icon']  		= "novell";

$os = "monowall";
$config['os'][$os]['group'] 		= "unix";
$config['os'][$os]['text']  		= "m0n0wall";
$config['os'][$os]['type']  		= "firewall";
$config['os'][$os]['over'][0]['graph']  = "device_bits";
$config['os'][$os]['over'][0]['text']   = "Device Traffic";
$config['os'][$os]['over'][1]['graph']  = "device_processors";
$config['os'][$os]['over'][1]['text']   = "CPU Usage";

$os = "solaris";
$config['os'][$os]['group'] 		= "unix";
$config['os'][$os]['text']  		= "Sun Solaris";

$os = "adva";
$config['os'][$os]['group'] 		= "unix";
$config['os'][$os]['text']  		= "Adva";
$config['os'][$os]['ifalias']		= 1;

$os = "opensolaris";
$config['os'][$os]['group']		= "unix";
$config['os'][$os]['text']		= "Sun OpenSolaris";

$os = "ios";
$config['os'][$os]['group']		= "ios";
$config['os'][$os]['text']		= "Cisco IOS";
$config['os'][$os]['type']		= "network";
$config['os'][$os]['ifXmcbc']		= 1;
$config['os'][$os]['over'][0]['graph']	= "device_bits";
$config['os'][$os]['over'][0]['text']	= "Device Traffic";
$config['os'][$os]['over'][1]['graph']	= "device_processors";
$config['os'][$os]['over'][1]['text']	= "CPU Usage";
$config['os'][$os]['over'][2]['graph']	= "device_mempools";
$config['os'][$os]['over'][2]['text']	= "Memory Usage";
$config['os'][$os]['icon']              = "cisco";


$os = "cat1900";
$config['os'][$os]['group']		= "cat1900";
$config['os'][$os]['text']		= "Cisco Catalyst 1900";
$config['os'][$os]['type']        	= "network";
$config['os'][$os]['icon']        	= "cisco-old";

$os = "iosxe";
$config['os'][$os]['group']		= "ios";
$config['os'][$os]['text']		= "Cisco IOS-XE";
$config['os'][$os]['type']		= "network";
$config['os'][$os]['ifXmcbc']       	= 1;
$config['os'][$os]['icon']		= "cisco";

$os = "iosxr";
$config['os'][$os]['group']		= "ios";
$config['os'][$os]['text']		= "Cisco IOS-XR";
$config['os'][$os]['type']		= "network";
$config['os'][$os]['ifXmcbc']       	= 1;
$config['os'][$os]['icon']		= "cisco";

$os = "asa";
$config['os'][$os]['group']		= "ios";
$config['os'][$os]['text']		= "Cisco ASA";
$config['os'][$os]['ifname']		= 1;
$config['os'][$os]['type']		= "firewall";
$config['os'][$os]['icon']		= "cisco";

$os = "pix";
$config['os'][$os]['group'] 		= "ios";
$config['os'][$os]['text']		= "Cisco PIX-OS";
$config['os'][$os]['ifname']		= 1;
$config['os'][$os]['type']		= "firewall";
$config['os'][$os]['icon']              = "cisco";

$os = "nxos";
$config['os'][$os]['group'] 		= "ios";
$config['os'][$os]['text']  		= "Cisco NX-OS";
$config['os'][$os]['type']  		= "network";
$config['os'][$os]['icon']              = "cisco";

$os = "catos";
$config['os'][$os]['group']		= "ios";
$config['os'][$os]['text']		= "Cisco CatOS";
$config['os'][$os]['ifname']		= 1;
$config['os'][$os]['type']		= "network";
$config['os'][$os]['icon']              = "cisco-old";

$os = "vrp";
$config['os'][$os]['group'] 		= "vrp";
$config['os'][$os]['text']  		= "Huawei VRP";
$config['os'][$os]['type']  		= "network";
$config['os'][$os]['icon']  		= "huawei";

$os = "junos";
$config['os'][$os]['text']		= "Juniper JunOS";
$config['os'][$os]['type']		= "network";

$os = "jwos";
$config['os'][$os]['text']		= "Juniper JWOS";
$config['os'][$os]['type']		= "network";
$config['os'][$os]['icon']  		= "junos";

$os = "screenos";
$config['os'][$os]['text']		= "Juniper ScreenOS";
$config['os'][$os]['type']		= "firewall";

$os = "routeros";
$config['os'][$os]['text']		= "Mikrotik RouterOS";
$config['os'][$os]['type']		= "network";
$config['os'][$os]['nobulk']		= 1;
$config['os'][$os]['over'][0]['graph']  = "device_bits";
$config['os'][$os]['over'][0]['text']   = "Device Traffic";
$config['os'][$os]['over'][1]['graph']  = "device_processors";
$config['os'][$os]['over'][1]['text']   = "CPU Usage";
$config['os'][$os]['over'][2]['graph']  = "device_mempools";
$config['os'][$os]['over'][2]['text']   = "Memory Usage";

$os = "junose";
$config['os'][$os]['text']		= "Juniper JunOSe";
$config['os'][$os]['type']		= "network";
$config['os'][$os]['icon']		= "junos";

$os = "ironware";
$config['os'][$os]['text']       	= "Brocade IronWare";
$config['os'][$os]['type']       	= "network";

$os = "extremeware";
$config['os'][$os]['text']    		= "Extremeware";
$config['os'][$os]['type']    		= "network";
$config['os'][$os]['ifname']  		= 1;
$config['os'][$os]['icon']    		= "extreme";

$os = "packetshaper";
$config['os'][$os]['text']   		= "Blue Coat Packetshaper";
$config['os'][$os]['type']   		= "network";

$os = "xos";
$config['os'][$os]['text']    		= "Extreme XOS";
$config['os'][$os]['type']    		= "network";
$config['os'][$os]['ifname']  		= 1;
$config['os'][$os]['group']		= "extremeware";
$config['os'][$os]['icon']		= "extreme";

$os = "ftos";
$config['os'][$os]['text']		= "Force10 FTOS";
$config['os'][$os]['type']		= "network";
$config['os'][$os]['icon']		= "force10";

$os = "arista_eos";
$config['os'][$os]['text']              = "Arista EOS";
$config['os'][$os]['type']              = "network";
$config['os'][$os]['icon']              = "arista";


$os = "powerconnect";
$config['os'][$os]['text']   		= "Dell PowerConnect";
$config['os'][$os]['ifname'] 		= 1;
$config['os'][$os]['type']   		= "network";
$config['os'][$os]['icon']   		= "dell";

$os = "radlan";
$config['os'][$os]['text']         	= "Radlan";
$config['os'][$os]['ifname']       	= 1;
$config['os'][$os]['type']         	= "network";
$config['os'][$os]['over'][0]['graph']  = "device_bits";
$config['os'][$os]['over'][0]['text']   = "Device Traffic";
$config['os'][$os]['over'][1]['graph']  = "device_processors";
$config['os'][$os]['over'][1]['text']   = "CPU Usage";
#$config['os'][$os]['over'][2]['graph']   = "device_mempools";
#$config['os'][$os]['over'][2]['text']    = "Memory Usage";

$os = "powervault";
$config['os'][$os]['text']     		= "Dell PowerVault";
$config['os'][$os]['icon']     		= "dell";

$os = "drac";
$config['os'][$os]['text']           	= "Dell DRAC";
$config['os'][$os]['icon']           	= "dell";

$os = "bcm963";
$config['os'][$os]['text']		= "Broadcom BCM963xx";
$config['os'][$os]['icon']		= "broadcom";
$config['os'][$os]['over'][0]['graph']  = "device_bits";
$config['os'][$os]['over'][0]['text']   = "Device Traffic";

$os = "netopia";
$config['os'][$os]['text']        	= "Motorola Netopia";
$config['os'][$os]['type']        	= "network";

$os = "tranzeo";
$config['os'][$os]['text']              = "Tranzeo";
$config['os'][$os]['over'][0]['graph']  = "device_bits";
$config['os'][$os]['over'][0]['text']   = "Device Traffic";

$os = "dlink";
$config['os'][$os]['text']          	= "D-Link Switch";
$config['os'][$os]['type']          	= "network";
$config['os'][$os]['icon']          	= "dlink";

$os = "dlinkap";
$config['os'][$os]['text']        	= "D-Link Access Point";
$config['os'][$os]['type']        	= "network";
$config['os'][$os]['icon']        	= "dlink";

$os = "axiscam";
$config['os'][$os]['text']        	= "AXIS Network Camera";
$config['os'][$os]['icon']        	= "axis";

$os = "axisdocserver";
$config['os'][$os]['text']  		= "AXIS Network Document Server";
$config['os'][$os]['icon']  		= "axis";

$os = "gamatronicups";
$config['os'][$os]['text']  		= "Gamatronic UPS Stack";
$config['os'][$os]['type']  		= "power";

$os = "airport";
$config['os'][$os]['type'] 		= "network";
$config['os'][$os]['text']  		= "Apple AirPort";
$config['os'][$os]['icon']  		= "apple";

$os = "windows";
$config['os'][$os]['text']        	= "Microsoft Windows";
$config['os'][$os]['ifname']		= 1;

$os = "procurve";
$config['os'][$os]['text']       	= "HP ProCurve";
$config['os'][$os]['type']       	= "network";
$config['os'][$os]['icon']       	= "hp";
$config['os'][$os]['over'][0]['graph']  = "device_bits";
$config['os'][$os]['over'][0]['text']   = "Traffic";
$config['os'][$os]['over'][0]['graph']  = "device_processors";
$config['os'][$os]['over'][0]['text']   = "CPU Usage";
$config['os'][$os]['over'][0]['graph']  = "device_mempools";
$config['os'][$os]['over'][0]['text']   = "Memory Usage";


$os = "speedtouch";
$config['os'][$os]['text']     		= "Thomson Speedtouch";
$config['os'][$os]['ifname']		= 1;
$config['os'][$os]['type']     		= "network";
$config['os'][$os]['over'][0]['graph']  = "device_bits";
$config['os'][$os]['over'][0]['text']   = "Traffic";


$os = "sonicwal";
$config['os'][$os]['text']     		= "SonicWALL";
$config['os'][$os]['type']     		= "firewall";
$config['os'][$os]['over'][0]['graph'] 	= "device_bits";
$config['os'][$os]['over'][0]['text']  	= "Traffic";

$os = "zywall";
$config['os'][$os]['text']		= "ZyXEL ZyWALL";
$config['os'][$os]['type']		= "firewall";
$config['os'][$os]['over'][0]['graph']	= "device_bits";
$config['os'][$os]['over'][0]['text']	= "Traffic";
$config['os'][$os]['icon']         	= "zyxel";

$os = "prestige";
$config['os'][$os]['text']     		= "ZyXEL Prestige";
$config['os'][$os]['type']     		= "network";
$config['os'][$os]['icon']       	= "zyxel";

$os = "zyxeles";
$config['os'][$os]['text']     		= "ZyXEL Ethernet Switch";
$config['os'][$os]['type']     		= "network";
$config['os'][$os]['icon']        	= "zyxel";

$os = "ies";
$config['os'][$os]['text']     		= "ZyXEL IES DSLAM";
$config['os'][$os]['type']     		= "network";
$config['os'][$os]['icon']            	= "zyxel";

$os = "allied";
$config['os'][$os]['text']         	= "AlliedWare";
$config['os'][$os]['type']         	= "network";
$config['os'][$os]['ifname']		= 1;
$config['os'][$os]['over'][0]['graph']  = "device_bits";
$config['os'][$os]['over'][0]['text']   = "Traffic";

$os = "mgeups";
$config['os'][$os]['text']         	= "MGE UPS";
$config['os'][$os]['group']        	= "ups";
$config['os'][$os]['type']		= "power";
$config['os'][$os]['icon']		= "mge";
$config['os'][$os]['over'][0]['graph']  = "device_current";
$config['os'][$os]['over'][0]['text']   = "Current";

$os = "mgepdu";
$config['os'][$os]['text']         	= "MGE PDU";
$config['os'][$os]['type']		= "power";
$config['os'][$os]['icon']		= "mge";

$os = "apc";
$config['os'][$os]['text']            	= "APC Management Module";
$config['os'][$os]['type']		= "power";
$config['os'][$os]['over'][0]['graph']  = "device_current";
$config['os'][$os]['over'][0]['text']   = "Current";

$os = "areca";
$config['os'][$os]['text']          	= "Areca RAID Subsystem";
$config['os'][$os]['over'][0]['graph']  = "";
$config['os'][$os]['over'][0]['text']   = "";

$os = "netmanplus";
$config['os'][$os]['text']        	= "NetMan Plus";
$config['os'][$os]['group']	   	= "ups";
$config['os'][$os]['nobulk']	   	= 1;
$config['os'][$os]['type']	   	= "power";
$config['os'][$os]['over'][0]['graph']  = "device_current";
$config['os'][$os]['over'][0]['text']   = "Current";

$os = "akcp";
$config['os'][$os]['text']              = "AKCP SensorProbe";
$config['os'][$os]['type']              = "environment";
$config['os'][$os]['over'][0]['graph']  = "device_temperature";
$config['os'][$os]['over'][0]['text']   = "Temperatures";

$os = "minkelsrms";
$config['os'][$os]['text']        	= "Minkels RMS";
$config['os'][$os]['type']        	= "environment";
$config['os'][$os]['over'][0]['graph']  = "device_temperature";
$config['os'][$os]['over'][0]['text']   = "Temperatures";

$os = "wxgoos";
$config['os'][$os]['text']              = "ITWatchDogs Goose";
$config['os'][$os]['type']              = "environment";
$config['os'][$os]['over'][0]['graph']  = "device_temperature";
$config['os'][$os]['over'][0]['text']   = "Temperatures";

$os = "papouch-tme";
$config['os'][$os]['text']       	= "Papouch TME";
$config['os'][$os]['type']       	= "environment";
$config['os'][$os]['over'][0]['graph']  = "device_temperature";
$config['os'][$os]['over'][0]['text']   = "Temperatures";

$os = "dell-laser";
$config['os'][$os]['group'] 	   	= "printer";
$config['os'][$os]['text']  	   	= "Dell Laser";
$config['os'][$os]['ifname']	   	= 1;
$config['os'][$os]['type']        	= "printer";
$config['os'][$os]['icon']        	= "dell";
$config['os'][$os]['over'][0]['graph']  = "device_toner";
$config['os'][$os]['over'][0]['text']   = "Toner";

$os = "ricoh";
$config['os'][$os]['group'] 	   	= "printer";
$config['os'][$os]['text']  	   	= "Ricoh Printer";
$config['os'][$os]['type']        	= "printer";
$config['os'][$os]['icon']        	= "ricoh";
$config['os'][$os]['over'][0]['graph']  = "device_toner";
$config['os'][$os]['over'][0]['text']   = "Toner";

$os = "xerox";
$config['os'][$os]['group'] 	   	= "printer";
$config['os'][$os]['text']  	   	= "Xerox Printer";
$config['os'][$os]['ifname']	   	= 1;
$config['os'][$os]['type']             	= "printer";
$config['os'][$os]['over'][0]['graph']  = "device_toner";
$config['os'][$os]['over'][0]['text']   = "Toner";

$os = "jetdirect";
$config['os'][$os]['group'] 	   	= "printer";
$config['os'][$os]['text']  	   	= "HP Print server";
$config['os'][$os]['ifname']	   	= 1;
$config['os'][$os]['type']         	= "printer";
$config['os'][$os]['icon']         	= "hp";
$config['os'][$os]['over'][0]['graph']  = "device_toner";
$config['os'][$os]['over'][0]['text']   = "Toner";

$os = "richoh";
$config['os'][$os]['group'] 	   	= "printer";
$config['os'][$os]['text']  	   	= "Ricoh Printer";
$config['os'][$os]['type']		= "printer";
$config['os'][$os]['over'][0]['graph']  = "device_toner";
$config['os'][$os]['over'][0]['text']   = "Toner";

$os = "okilan";
$config['os'][$os]['group'] 	   	= "printer";
$config['os'][$os]['text']  	   	= "OKI Printer";
$config['os'][$os]['overgraph'][]       = "device_toner";
$config['os'][$os]['overtext']          = "Toner";
$config['os'][$os]['type']              = "printer";
$config['os'][$os]['icon']              = "oki";

$os = "brother";
$config['os'][$os]['group'] 	   	= "printer";
$config['os'][$os]['text']  	   	= "Brother Printer";
$config['os'][$os]['type']           	= "printer";
$config['os'][$os]['over'][0]['graph']  = "device_toner";
$config['os'][$os]['over'][0]['text']   = "Toner";

$os = "konica";
$config['os'][$os]['group'] 	   	= "printer";
$config['os'][$os]['text']  	   	= "Konica-Minolta Printer";
$config['os'][$os]['type']            	= "printer";
$config['os'][$os]['over'][0]['graph']  = "device_toner";
$config['os'][$os]['over'][0]['text']   = "Toner";

$os = "kyocera";
$config['os'][$os]['group'] 	   	= "printer";
$config['os'][$os]['text']  	   	= "Kyocera Mita Printer";
$config['os'][$os]['over'][0]['graph'] 	= "device_toner";
$config['os'][$os]['over'][0]['text']  	= "Toner";
$config['os'][$os]['ifname']	   	= 1;
$config['os'][$os]['type']           	= "printer";

$os = "3com";
$config['os'][$os]['text']              = "3Com";
$config['os'][$os]['over'][0]['graph']  = "device_bits";
$config['os'][$os]['over'][0]['text']   = "Traffic";
$config['os'][$os]['type']              = "network";

$device_types = array('server', 'network', 'firewall', 'workstation', 'printer', 'power', 'environment');

$config['graph']['device']['bits']		= "Total Traffic";
$config['graph']['device']['hrusers']   	= "Users Logged In";
$config['graph']['device']['temperatures']	= "Temperatures";
$config['graph']['device']['memory']  		= "Memory Usage";
$config['graph']['device']['processors']      	= "Processor Usage";
$config['graph']['device']['cpu']        	= "Processor Usage";
$config['graph']['device']['storage']        	= "Disk Usage";

$config['graph']['device']['ucd_load']		= "Load Averages";
$config['graph']['device']['ucd_cpu']           = "Detailed Processor Usage";
$config['graph']['device']['ucd_mem']           = "Detailed Memory Usage";
$config['graph']['device']['netstats_tcp']      = "TCP Statistics";
$config['graph']['device']['netstats_icmp_info'] = "ICMP Informational Statistics";
$config['graph']['device']['netstats_icmp_stat'] = "ICMP Statistics";
$config['graph']['device']['netstats_ip']        = "IP Statistics";
$config['graph']['device']['netstats_ip_frag']   = "IP Fragmentation Statistics";
$config['graph']['device']['netstats_udp']       = "UDP Statistics";
$config['graph']['device']['netstats_snmp']      = "SNMP Statistics";



##############################
# No changes below this line #
##############################

$config['version'] = "0.10";

$config['rrdgraph_def_text'] = str_replace("  ", " ", $config['rrdgraph_def_text']);
$config['rrd_opts_array'] = explode(" ", trim($config['rrdgraph_def_text']));

if(!isset($config['log_file'])) 
{
  $config['log_file']     = $config['install_dir'] . "/observium.log";
}

if(!$config['mibdir']) 
{
  $config['mibdir'] =  $config['install_dir']."/mibs/";
}
$config['mib_dir'] = $config['mibdir'];

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

$config['now']        = time();
$config['day']        = time() - (24 * 60 * 60);
$config['twoday']     = time() - (2 * 24 * 60 * 60);
$config['week']       = time() - (7 * 24 * 60 * 60);
$config['twoweek']    = time() - (2 * 7 * 24 * 60 * 60);
$config['month']      = time() - (31 * 24 * 60 * 60);
$config['twomonth']   = time() - (2 * 31 * 24 * 60 * 60);
$config['threemonth'] = time() - (3 * 31 * 24 * 60 * 60);
$config['year']       = time() - (365 * 24 * 60 * 60);


?>
