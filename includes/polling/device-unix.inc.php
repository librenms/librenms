<?php

      if ($device['os'] == "freebsd") {
        $sysDescr = str_replace(" 0 ", " ", $sysDescr);
        list(,,$version) = explode (" ", $sysDescr);
        $hardware = "i386";
        $features = "GENERIC";
      } elseif ($device['os'] == "dragonfly") {
        list(,,$version,,,$features,,$hardware) = explode (" ", $sysDescr);
      } elseif ($device['os'] == "netbsd") {
        list(,,$version,,,$features) = explode (" ", $sysDescr);
        $features = str_replace("(", "", $features);
        $features = str_replace(")", "", $features);
        list(,,$hardware) = explode ("$features", $sysDescr);
      } elseif ($device['os'] == "openbsd" || $device['os'] == "solaris" || $device['os'] == "opensolaris") {
        list(,,$version,$features,$hardware) = explode (" ", $sysDescr);
        $features = str_replace("(", "", $features);
        $features = str_replace(")", "", $features);
      } elseif ($device['os'] == "monowall" || $device['os'] == "Voswall") {
        list(,,$version,$hardware,$freebsda, $freebsdb, $arch) = split(" ", $sysDescr);
        $features = $freebsda . " " . $freebsdb;
        $hardware = "$hardware ($arch)";
        $hardware = str_replace("\"", "", $hardware);
      } elseif ($device['os'] == "linux") {
        list(,,$version) = explode (" ", $sysDescr);
        if(strstr($sysDescr, "386")|| strstr($sysDescr, "486")||strstr($sysDescr, "586")||strstr($sysDescr, "686")) { $hardware = "Generic x86"; }
        else if(strstr($sysDescr, "x86_64")) { $hardware = "Generic x86 64-bit"; }
        else if(strstr($sysDescr, "sparc32")) { $hardware = "Generic SPARC 32-bit"; }
        else if(strstr($sysDescr, "sparc64")) { $hardware = "Generic SPARC 64-bit"; }
        $cmd = $config['snmpget'] . " -m UCD-SNMP-MIB -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port']. " .1.3.6.1.4.1.2021.7890.1.3.1.1.6.100.105.115.116.114.111";
        $features = trim(`$cmd`);
        $features = str_replace("No Such Object available on this agent at this OID", "", $features);
        $features = str_replace("\"", "", $features);
        // Detect Dell hardware via OpenManage SNMP
        $cmd = $config['snmpget'] . " -m MIB-Dell-10892 -O qv -" . $device['snmpver'] . " -c " . $device['community'] . " " . $device['hostname'].":".$device['port'] . " .1.3.6.1.4.1.674.10892.1.300.10.1.9.1";
        $hw = trim(str_replace("\"", "", `$cmd`));
        if(strpos($hw, "No") !== FALSE) { unset($hw); } else { $hardware = "Dell " . $hw; }
      }

echo("$version - $hardware - $features ");

include("ucd-mib.inc.php");
include("hr-mib.inc.php");
