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

$os = "vyatta";
$config['os'][$os]['text']              = "Vyatta";
$config['os'][$os]['type']              = "network";
$config['os'][$os]['ifname']            = 1;

$os = "linux";
$config['os'][$os]['type']              = "server";
$config['os'][$os]['group'] 		= "unix";
$config['os'][$os]['text']  		= "Linux";
$config['os'][$os]['ifXmcbc']		= 1;

$os = "freebsd";
$config['os'][$os]['type']              = "server";
$config['os'][$os]['group'] 		= "unix";
$config['os'][$os]['text']  		= "FreeBSD";

$os = "openbsd";
$config['os'][$os]['type']              = "server";
$config['os'][$os]['group'] 		= "unix";
$config['os'][$os]['text']  		= "OpenBSD";

$os = "netbsd";
$config['os'][$os]['type']              = "server";
$config['os'][$os]['group'] 		= "unix";
$config['os'][$os]['text']  		= "NetBSD";

$os = "dragonfly";
$config['os'][$os]['type']              = "server";
$config['os'][$os]['group'] 		= "unix";
$config['os'][$os]['text']  		= "DragonflyBSD";

$os = "netware";
$config['os'][$os]['type']              = "server";
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
$config['os'][$os]['type']              = "server";

$os = "adva";
$config['os'][$os]['type']              = "network";
$config['os'][$os]['text']  		= "Adva Optical";
$config['os'][$os]['over'][0]['graph']  = "device_bits";
$config['os'][$os]['over'][0]['text']   = "Device Traffic";

$os = "opensolaris";
$config['os'][$os]['type']              = "server";
$config['os'][$os]['group']		= "unix";
$config['os'][$os]['text']		= "Sun OpenSolaris";

$os = "openindiana";
$config['os'][$os]['type']              = "server";
$config['os'][$os]['group']             = "unix";
$config['os'][$os]['text']              = "OpenIndiana";

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
$config['os'][$os]['over'][0]['graph']  = "device_bits";
$config['os'][$os]['over'][0]['text']   = "Device Traffic";
$config['os'][$os]['over'][1]['graph']  = "device_processors";
$config['os'][$os]['over'][1]['text']   = "CPU Usage";
$config['os'][$os]['over'][2]['graph']  = "device_mempools";
$config['os'][$os]['over'][2]['text']   = "Memory Usage";

$os = "iosxe";
$config['os'][$os]['group']		= "ios";
$config['os'][$os]['text']		= "Cisco IOS-XE";
$config['os'][$os]['type']		= "network";
$config['os'][$os]['ifXmcbc']       	= 1;
$config['os'][$os]['over'][0]['graph']  = "device_bits";
$config['os'][$os]['over'][0]['text']   = "Device Traffic";
$config['os'][$os]['over'][1]['graph']  = "device_processors";
$config['os'][$os]['over'][1]['text']   = "CPU Usage";
$config['os'][$os]['over'][2]['graph']  = "device_mempools";
$config['os'][$os]['over'][2]['text']   = "Memory Usage";
$config['os'][$os]['icon']		= "cisco";

$os = "iosxr";
$config['os'][$os]['group']		= "ios";
$config['os'][$os]['text']		= "Cisco IOS-XR";
$config['os'][$os]['type']		= "network";
$config['os'][$os]['ifXmcbc']       	= 1;
$config['os'][$os]['icon']		= "cisco";
$config['os'][$os]['over'][0]['graph']  = "device_bits";
$config['os'][$os]['over'][0]['text']   = "Device Traffic";
$config['os'][$os]['over'][1]['graph']  = "device_processors";
$config['os'][$os]['over'][1]['text']   = "CPU Usage";
$config['os'][$os]['over'][2]['graph']  = "device_mempools";
$config['os'][$os]['over'][2]['text']   = "Memory Usage";

$os = "asa";
$config['os'][$os]['group']		= "ios";
$config['os'][$os]['text']		= "Cisco ASA";
$config['os'][$os]['ifname']		= 1;
$config['os'][$os]['type']		= "firewall";
$config['os'][$os]['icon']		= "cisco";
$config['os'][$os]['over'][0]['graph']  = "device_bits";
$config['os'][$os]['over'][0]['text']   = "Device Traffic";
$config['os'][$os]['over'][1]['graph']  = "device_processors";
$config['os'][$os]['over'][1]['text']   = "CPU Usage";

$os = "pix";
$config['os'][$os]['group'] 		= "ios";
$config['os'][$os]['text']		= "Cisco PIX-OS";
$config['os'][$os]['ifname']		= 1;
$config['os'][$os]['type']		= "firewall";
$config['os'][$os]['icon']              = "cisco";
$config['os'][$os]['over'][0]['graph']  = "device_bits";
$config['os'][$os]['over'][0]['text']   = "Device Traffic";
$config['os'][$os]['over'][1]['graph']  = "device_processors";
$config['os'][$os]['over'][1]['text']   = "CPU Usage";
$config['os'][$os]['over'][2]['graph']  = "device_mempools";
$config['os'][$os]['over'][2]['text']   = "Memory Usage";

$os = "nxos";
$config['os'][$os]['group'] 		= "ios";
$config['os'][$os]['text']  		= "Cisco NX-OS";
$config['os'][$os]['type']  		= "network";
$config['os'][$os]['icon']              = "cisco";
$config['os'][$os]['over'][0]['graph']  = "device_bits";
$config['os'][$os]['over'][0]['text']   = "Device Traffic";
$config['os'][$os]['over'][1]['graph']  = "device_processors";
$config['os'][$os]['over'][1]['text']   = "CPU Usage";
$config['os'][$os]['over'][2]['graph']  = "device_mempools";
$config['os'][$os]['over'][2]['text']   = "Memory Usage";

$os = "sanos";
$config['os'][$os]['group']             = "ios";
$config['os'][$os]['text']              = "Cisco SAN-OS";
$config['os'][$os]['type']              = "network";
$config['os'][$os]['icon']              = "cisco";
$config['os'][$os]['over'][0]['graph']  = "device_bits";
$config['os'][$os]['over'][0]['text']   = "Device Traffic";
$config['os'][$os]['over'][1]['graph']  = "device_processors";
$config['os'][$os]['over'][1]['text']   = "CPU Usage";
$config['os'][$os]['over'][2]['graph']  = "device_mempools";
$config['os'][$os]['over'][2]['text']   = "Memory Usage";

$os = "catos";
$config['os'][$os]['group']		= "ios";
$config['os'][$os]['text']		= "Cisco CatOS";
$config['os'][$os]['ifname']		= 1;
$config['os'][$os]['type']		= "network";
$config['os'][$os]['icon']              = "cisco-old";
$config['os'][$os]['over'][0]['graph']  = "device_bits";
$config['os'][$os]['over'][0]['text']   = "Device Traffic";
$config['os'][$os]['over'][1]['graph']  = "device_processors";
$config['os'][$os]['over'][1]['text']   = "CPU Usage";
$config['os'][$os]['over'][2]['graph']  = "device_mempools";
$config['os'][$os]['over'][2]['text']   = "Memory Usage";

$os = "vrp";
$config['os'][$os]['group'] 		= "vrp";
$config['os'][$os]['text']  		= "Huawei VRP";
$config['os'][$os]['type']  		= "network";
$config['os'][$os]['icon']  		= "huawei";

$os = "junos";
$config['os'][$os]['text']		= "Juniper JunOS";
$config['os'][$os]['type']		= "network";
$config['os'][$os]['over'][0]['graph']  = "device_bits";
$config['os'][$os]['over'][0]['text']   = "Device Traffic";
$config['os'][$os]['over'][1]['graph']  = "device_processors";
$config['os'][$os]['over'][1]['text']   = "CPU Usage";
$config['os'][$os]['over'][2]['graph']  = "device_mempools";
$config['os'][$os]['over'][2]['text']   = "Memory Usage";

$os = "jwos";
$config['os'][$os]['text']		= "Juniper JWOS";
$config['os'][$os]['type']		= "network";
$config['os'][$os]['icon']  		= "junos";

$os = "screenos";
$config['os'][$os]['text']		= "Juniper ScreenOS";
$config['os'][$os]['type']		= "firewall";
$config['os'][$os]['over'][0]['graph']  = "device_bits";
$config['os'][$os]['over'][0]['text']   = "Device Traffic";
$config['os'][$os]['over'][1]['graph']  = "device_processors";
$config['os'][$os]['over'][1]['text']   = "CPU Usage";
$config['os'][$os]['over'][2]['graph']  = "device_mempools";
$config['os'][$os]['over'][2]['text']   = "Memory Usage";

$os = "fortigate";
$config['os'][$os]['text']              = "Fortinet Fortigate";
$config['os'][$os]['type']              = "firewall";
$config['os'][$os]['over'][0]['graph']  = "device_bits";
$config['os'][$os]['over'][0]['text']   = "Device Traffic";
#$config['os'][$os]['over'][1]['graph']  = "device_processors";
#$config['os'][$os]['over'][1]['text']   = "CPU Usage";
#$config['os'][$os]['over'][2]['graph']  = "device_mempools";
#$config['os'][$os]['over'][2]['text']   = "Memory Usage";


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
$config['os'][$os]['over'][0]['graph']  = "device_bits";
$config['os'][$os]['over'][0]['text']   = "Device Traffic";
$config['os'][$os]['over'][1]['graph']  = "device_processors";
$config['os'][$os]['over'][1]['text']   = "CPU Usage";
$config['os'][$os]['over'][2]['graph']  = "device_mempools";
$config['os'][$os]['over'][2]['text']   = "Memory Usage";

$os = "ironware";
$config['os'][$os]['text']       	= "Brocade IronWare";
$config['os'][$os]['type']       	= "network";
$config['os'][$os]['over'][0]['graph']  = "device_bits";
$config['os'][$os]['over'][0]['text']   = "Device Traffic";
$config['os'][$os]['over'][1]['graph']  = "device_processors";
$config['os'][$os]['over'][1]['text']   = "CPU Usage";
$config['os'][$os]['over'][2]['graph']  = "device_mempools";
$config['os'][$os]['over'][2]['text']   = "Memory Usage";

$os = "extremeware";
$config['os'][$os]['text']    		= "Extremeware";
$config['os'][$os]['type']    		= "network";
$config['os'][$os]['ifname']  		= 1;
$config['os'][$os]['icon']    		= "extreme";
$config['os'][$os]['over'][0]['graph']  = "device_bits";
$config['os'][$os]['over'][0]['text']   = "Device Traffic";
$config['os'][$os]['over'][1]['graph']  = "device_processors";
$config['os'][$os]['over'][1]['text']   = "CPU Usage";
$config['os'][$os]['over'][2]['graph']  = "device_mempools";
$config['os'][$os]['over'][2]['text']   = "Memory Usage";

$os = "packetshaper";
$config['os'][$os]['text']   		= "Blue Coat Packetshaper";
$config['os'][$os]['type']   		= "network";

$os = "xos";
$config['os'][$os]['text']    		= "Extreme XOS";
$config['os'][$os]['type']    		= "network";
$config['os'][$os]['ifname']  		= 1;
$config['os'][$os]['group']		= "extremeware";
$config['os'][$os]['icon']		= "extreme";
$config['os'][$os]['over'][0]['graph']  = "device_bits";
$config['os'][$os]['over'][0]['text']   = "Device Traffic";
$config['os'][$os]['over'][1]['graph']  = "device_processors";
$config['os'][$os]['over'][1]['text']   = "CPU Usage";
$config['os'][$os]['over'][2]['graph']  = "device_mempools";
$config['os'][$os]['over'][2]['text']   = "Memory Usage";

$os = "ftos";
$config['os'][$os]['text']		= "Force10 FTOS";
$config['os'][$os]['type']		= "network";
$config['os'][$os]['icon']		= "force10";
$config['os'][$os]['over'][0]['graph']  = "device_bits";
$config['os'][$os]['over'][0]['text']   = "Device Traffic";
$config['os'][$os]['over'][1]['graph']  = "device_processors";
$config['os'][$os]['over'][1]['text']   = "CPU Usage";
$config['os'][$os]['over'][2]['graph']  = "device_mempools";
$config['os'][$os]['over'][2]['text']   = "Memory Usage";

$os = "arista_eos";
$config['os'][$os]['text']              = "Arista EOS";
$config['os'][$os]['type']              = "network";
$config['os'][$os]['icon']              = "arista";
$config['os'][$os]['over'][0]['graph']  = "device_bits";
$config['os'][$os]['over'][0]['text']   = "Device Traffic";
$config['os'][$os]['over'][1]['graph']  = "device_processors";
$config['os'][$os]['over'][1]['text']   = "CPU Usage";
$config['os'][$os]['over'][2]['graph']  = "device_mempools";
$config['os'][$os]['over'][2]['text']   = "Memory Usage";


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

$os = "powerware";
$config['os'][$os]['text']  		= "Powerware UPS";
$config['os'][$os]['type']  		= "power";
$config['os'][$os]['icon']  		= "eaton";
$config['os'][$os]['over'][0]['graph']  = "device_voltages";
$config['os'][$os]['over'][0]['text']   = "Voltages";
$config['os'][$os]['over'][1]['graph']  = "device_current";
$config['os'][$os]['over'][1]['text']   = "Current";
$config['os'][$os]['over'][2]['graph']  = "device_frequencies";
$config['os'][$os]['over'][2]['text']   = "Frequencies";

$os = "deltaups";
$config['os'][$os]['text']  		= "Delta UPS";
$config['os'][$os]['type']  		= "power";
$config['os'][$os]['icon']  		= "delta";

$os = "engenius";
$config['os'][$os]['type'] 		= "network";
$config['os'][$os]['text']  		= "EnGenius Access Point";
$config['os'][$os]['icon']  		= "engenius";

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
$config['os'][$os]['over'][1]['graph']  = "device_processors";
$config['os'][$os]['over'][1]['text']   = "CPU Usage";
$config['os'][$os]['over'][2]['graph']  = "device_mempools";
$config['os'][$os]['over'][2]['text']   = "Memory Usage";

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
$config['os'][$os]['text']     		= "ZyXEL DSLAM";
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

$os = "netvision";
$config['os'][$os]['text']             = "Socomec Net Vision";
$config['os'][$os]['type']             = "power";
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
$config['os'][$os]['over'][0]['graph']  = "device_temperatures";
$config['os'][$os]['over'][0]['text']   = "Temperatures";

$os = "minkelsrms";
$config['os'][$os]['text']        	= "Minkels RMS";
$config['os'][$os]['type']        	= "environment";
$config['os'][$os]['over'][0]['graph']  = "device_temperatures";
$config['os'][$os]['over'][0]['text']   = "Temperatures";

$os = "ipoman";
$config['os'][$os]['text']              = "Ingrasys iPoMan";
$config['os'][$os]['type']              = "power";
$config['os'][$os]['over'][0]['graph']  = "device_current";
$config['os'][$os]['over'][0]['text']   = "Current";

$os = "wxgoos";
$config['os'][$os]['text']              = "ITWatchDogs Goose";
$config['os'][$os]['type']              = "environment";
$config['os'][$os]['over'][0]['graph']  = "device_temperatures";
$config['os'][$os]['over'][0]['text']   = "Temperatures";

$os = "papouch-tme";
$config['os'][$os]['text']       	= "Papouch TME";
$config['os'][$os]['type']       	= "environment";
$config['os'][$os]['over'][0]['graph']  = "device_temperatures";
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

$os = "sentry3";
$config['os'][$os]['text']            	= "ServerTech Sentry3";
$config['os'][$os]['type']		= "power";
$config['os'][$os]['over'][0]['graph']  = "device_current";
$config['os'][$os]['over'][0]['text']   = "Current";
$config['os'][$os]['icon']  		= "servertech";

$device_types = array('server', 'network', 'firewall', 'workstation', 'printer', 'power', 'environment');

### Graph Types

$config['graph_sections'] = array('system', 'firewall', 'netstats', 'wireless', 'storage');

$config['graph_types']['device']['aironet_wifi_clients']['section'] = 'wireless';
$config['graph_types']['device']['aironet_wifi_clients']['order'] = '0';
$config['graph_types']['device']['aironet_wifi_clients']['descr'] = 'Wireless Clients';

$config['graph_types']['device']['cipsec_flow_bits']['section'] = 'firewall';
$config['graph_types']['device']['cipsec_flow_bits']['order'] = '0';
$config['graph_types']['device']['cipsec_flow_bits']['descr'] = 'IPSec Tunnel Traffic Volume';
$config['graph_types']['device']['cipsec_flow_pkts']['section'] = 'firewall';
$config['graph_types']['device']['cipsec_flow_pkts']['order'] = '0';
$config['graph_types']['device']['cipsec_flow_pkts']['descr'] = 'IPSec Tunnel Traffic Packets';
$config['graph_types']['device']['cipsec_flow_stats']['section'] = 'firewall';
$config['graph_types']['device']['cipsec_flow_stats']['order'] = '0';
$config['graph_types']['device']['cipsec_flow_stats']['descr'] = 'IPSec Tunnel Statistics';
$config['graph_types']['device']['cipsec_flow_tunnels']['section'] = 'firewall';
$config['graph_types']['device']['cipsec_flow_tunnels']['order'] = '0';
$config['graph_types']['device']['cipsec_flow_tunnels']['descr'] = 'IPSec Active Tunnels';
$config['graph_types']['device']['cras_sessions']['section'] = 'firewall';
$config['graph_types']['device']['cras_sessions']['order'] = '0';
$config['graph_types']['device']['cras_sessions']['descr'] = 'Remote Access Sessions';
$config['graph_types']['device']['fortigate_sessions']['section'] = 'firewall';
$config['graph_types']['device']['fortigate_sessions']['order'] = '0';
$config['graph_types']['device']['fortigate_sessions']['descr'] = 'Active Sessions';
$config['graph_types']['device']['screenos_sessions']['section'] = 'firewall';
$config['graph_types']['device']['screenos_sessions']['order'] = '0';
$config['graph_types']['device']['screenos_sessions']['descr'] = 'Active Sessions';

$config['graph_types']['device']['bits']['section'] = 'netstats';
$config['graph_types']['device']['bits']['order'] = '0';
$config['graph_types']['device']['bits']['descr'] = 'Total Traffic';
$config['graph_types']['device']['ipsystemstats_ipv4']['section'] = 'netstats';
$config['graph_types']['device']['ipsystemstats_ipv4']['order'] = '0';
$config['graph_types']['device']['ipsystemstats_ipv4']['descr'] = 'IPv4 Packet Statistics';
$config['graph_types']['device']['ipsystemstats_ipv4_frag']['section'] = 'netstats';
$config['graph_types']['device']['ipsystemstats_ipv4_frag']['order'] = '0';
$config['graph_types']['device']['ipsystemstats_ipv4_frag']['descr'] = 'IPv4 Fragmentation Statistics';
$config['graph_types']['device']['ipsystemstats_ipv6']['section'] = 'netstats';
$config['graph_types']['device']['ipsystemstats_ipv6']['order'] = '0';
$config['graph_types']['device']['ipsystemstats_ipv6']['descr'] = 'IPv6 Packet Statistics';
$config['graph_types']['device']['ipsystemstats_ipv6_frag']['section'] = 'netstats';
$config['graph_types']['device']['ipsystemstats_ipv6_frag']['order'] = '0';
$config['graph_types']['device']['ipsystemstats_ipv6_frag']['descr'] = 'IPv6 Fragmentation Statistics';
$config['graph_types']['device']['netstat_icmp_info']['section'] = 'netstats';
$config['graph_types']['device']['netstat_icmp_info']['order'] = '0';
$config['graph_types']['device']['netstat_icmp_info']['descr'] = 'ICMP Informational Statistics';
$config['graph_types']['device']['netstat_icmp']['section'] = 'netstats';
$config['graph_types']['device']['netstat_icmp']['order'] = '0';
$config['graph_types']['device']['netstat_icmp']['descr'] = 'ICMP Statistics';
$config['graph_types']['device']['netstat_ip']['section'] = 'netstats';
$config['graph_types']['device']['netstat_ip']['order'] = '0';
$config['graph_types']['device']['netstat_ip']['descr'] = 'IP Statistics';
$config['graph_types']['device']['netstat_ip_frag']['section'] = 'netstats';
$config['graph_types']['device']['netstat_ip_frag']['order'] = '0';
$config['graph_types']['device']['netstat_ip_frag']['descr'] = 'IP Fragmentation Statistics';
$config['graph_types']['device']['netstat_snmp']['section'] = 'netstats';
$config['graph_types']['device']['netstat_snmp']['order'] = '0';
$config['graph_types']['device']['netstat_snmp']['descr'] = 'SNMP Statistics';
$config['graph_types']['device']['netstat_snmp_pkt']['section'] = 'netstats';
$config['graph_types']['device']['netstat_snmp_pkt']['order'] = '0';
$config['graph_types']['device']['netstat_snmp_pkt']['descr'] = 'SNMP Packet Type Statistics';

$config['graph_types']['device']['netstat_tcp']['section'] = 'netstats';
$config['graph_types']['device']['netstat_tcp']['order'] = '0';
$config['graph_types']['device']['netstat_tcp']['descr'] = 'TCP Statistics';
$config['graph_types']['device']['netstat_udp']['section'] = 'netstats';
$config['graph_types']['device']['netstat_udp']['order'] = '0';
$config['graph_types']['device']['netstat_udp']['descr'] = 'UDP Statistics';

$config['graph_types']['device']['fdb_count']['section'] = 'system';
$config['graph_types']['device']['fdb_count']['order'] = '0';
$config['graph_types']['device']['fdb_count']['descr'] = 'MAC Addresses Learnt';
$config['graph_types']['device']['hr_processes']['section'] = 'system';
$config['graph_types']['device']['hr_processes']['order'] = '0';
$config['graph_types']['device']['hr_processes']['descr'] = 'Running Processes';
$config['graph_types']['device']['hr_users']['section'] = 'system';
$config['graph_types']['device']['hr_users']['order'] = '0';
$config['graph_types']['device']['hr_users']['descr'] = 'Users Logged In';
$config['graph_types']['device']['mempools']['section'] = 'system';
$config['graph_types']['device']['mempools']['order'] = '0';
$config['graph_types']['device']['mempools']['descr'] = 'Memory Pool Usage';
$config['graph_types']['device']['processors']['section'] = 'system';
$config['graph_types']['device']['processors']['order'] = '0';
$config['graph_types']['device']['processors']['descr'] = 'Processor Usage';
$config['graph_types']['device']['storage']['section'] = 'system';
$config['graph_types']['device']['storage']['order'] = '0';
$config['graph_types']['device']['storage']['descr'] = 'Filesystem Usage';
$config['graph_types']['device']['temperatures']['section'] = 'system';
$config['graph_types']['device']['temperatures']['order'] = '0';
$config['graph_types']['device']['temperatures']['descr'] = 'Temperatures';
$config['graph_types']['device']['ucd_cpu']['section'] = 'system';
$config['graph_types']['device']['ucd_cpu']['order'] = '0';
$config['graph_types']['device']['ucd_cpu']['descr'] = 'Detailed Processor Usage';
$config['graph_types']['device']['ucd_load']['section'] = 'system';
$config['graph_types']['device']['ucd_load']['order'] = '0';
$config['graph_types']['device']['ucd_load']['descr'] = 'Load Averages';
$config['graph_types']['device']['ucd_memory']['section'] = 'system';
$config['graph_types']['device']['ucd_memory']['order'] = '0';
$config['graph_types']['device']['ucd_memory']['descr'] = 'Detailed Memory Usage';
$config['graph_types']['device']['ucd_swap_io']['section'] = 'system';
$config['graph_types']['device']['ucd_swap_io']['order'] = '0';
$config['graph_types']['device']['ucd_swap_io']['descr'] = 'Swap I/O Activity';
$config['graph_types']['device']['ucd_io']['section'] = 'system';
$config['graph_types']['device']['ucd_io']['order'] = '0';
$config['graph_types']['device']['ucd_io']['descr'] = 'System I/O Activity';
$config['graph_types']['device']['ucd_contexts']['section'] = 'system';
$config['graph_types']['device']['ucd_contexts']['order'] = '0';
$config['graph_types']['device']['ucd_contexts']['descr'] = 'Context Switches';
$config['graph_types']['device']['ucd_interrupts']['section'] = 'system';
$config['graph_types']['device']['ucd_interrupts']['order'] = '0';
$config['graph_types']['device']['ucd_interrupts']['descr'] = 'Interrupts';
$config['graph_types']['device']['uptime']['section'] = 'system';
$config['graph_types']['device']['uptime']['order'] = '0';
$config['graph_types']['device']['uptime']['descr'] = 'System Uptime';


### Device Types

$i = 0;
$config['device_types'][$i]['text'] = 'Servers';
$config['device_types'][$i]['type'] = 'server';
$config['device_types'][$i]['icon'] = 'server.png';

$i++;
$config['device_types'][$i]['text'] = 'Network';
$config['device_types'][$i]['type'] = 'network';
$config['device_types'][$i]['icon'] = 'network.png';

$i++;
$config['device_types'][$i]['text'] = 'Firewalls';
$config['device_types'][$i]['type'] = 'firewall';
$config['device_types'][$i]['icon'] = 'firewall.png';

$i++;
$config['device_types'][$i]['text'] = 'Power';
$config['device_types'][$i]['type'] = 'power';
$config['device_types'][$i]['icon'] = 'power.png';

$i++;
$config['device_types'][$i]['text'] = 'Environment';
$config['device_types'][$i]['type'] = 'environment';
$config['device_types'][$i]['icon'] = 'environment.png';

if (isset($config['enable_printers']) && $config['enable_printers'])
{
  $i++;
  $config['device_types'][$i]['text'] = 'Printers';
  $config['device_types'][$i]['type'] = 'printer';
  $config['device_types'][$i]['icon'] = 'printer.png';
}



##############################
# No changes below this line #
##############################

$config['version'] = "0.10";

if (isset($config['rrdgraph_def_text']))
{
  $config['rrdgraph_def_text'] = str_replace("  ", " ", $config['rrdgraph_def_text']);
  $config['rrd_opts_array'] = explode(" ", trim($config['rrdgraph_def_text']));
}

if (!isset($config['log_file']))
{
  $config['log_file']     = $config['install_dir'] . "/observium.log";
}

if (!isset($config['mibdir']))
{
  $config['mibdir'] =  $config['install_dir']."/mibs/";
}
$config['mib_dir'] = $config['mibdir'];

if (isset($config['enable_nagios']) && $config['enable_nagios']) {
  $nagios_link = mysql_connect($config['nagios_db_host'], $config['nagios_db_user'], $config['nagios_db_pass']);
  if (!$nagios_link) {
    echo("<h2>Nagios MySQL Error</h2>");
    die;
}
$nagios_db = mysql_select_db($config['nagios_db_name'], $nagios_link);
}

# If we're on SSL, let's properly detect it
if (isset($_SERVER['HTTPS']))
{
  $config['base_url'] = preg_replace('/^http:/','https:', $config['base_url']);
}

### Connect to database
$observium_link = mysql_pconnect($config['db_host'], $config['db_user'], $config['db_pass']);
if (!$observium_link)
{
        echo("<h2>Observer MySQL Error</h2>");
        echo(mysql_error());
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

# IPMI sensor type mappings
$ipmi_unit['Volts']     = 'voltage';
$ipmi_unit['degrees C'] = 'temperature';
$ipmi_unit['RPM']       = 'fanspeed';
$ipmi_unit['Watts']     = '';
$ipmi_unit['discrete']  = '';

?>
