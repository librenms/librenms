<?php

echo("Wireless: ");

if ($device['type'] == 'network')
{

  ##### GENERIC FRAMEWORK, FILLING VARIABLES
  if ($device['os'] == 'airport')
  {

    echo("Checking Airport Wireless clients... ");

    $wificlients1 = snmp_get($device, "wirelessNumber.0", "-OUqnv", "AIRPORT-BASESTATION-3-MIB") +0;

    echo($wificlients1 . " clients\n");

    # FIXME Also interesting to poll? dhcpNumber.0 for number of active dhcp leases
  }
  
  if ($device['os'] == 'ios' and substr($device['hardware'],0,4) == 'AIR-')
  {

    echo("Checking Aironet Wireless clients... ");

    $wificlients1 = snmp_get($device, "cDot11ActiveWirelessClients.1", "-OUqnv", "CISCO-DOT11-ASSOCIATION-MIB");
    $wificlients2 = snmp_get($device, "cDot11ActiveWirelessClients.2", "-OUqnv", "CISCO-DOT11-ASSOCIATION-MIB");
    
    echo(($wificlients1 +0) . " clients on dot11Radio0, " . ($wificlients2 +0) . " clients on dot11Radio1\n");
  }
  
  
  ##### RRD Filling Code

  if (isset($wificlients1) && $wificlients1 != "")
  {
    $wificlientsrrd  = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("wificlients-radio1.rrd");

    if (!is_file($wificlientsrrd)) {
      `rrdtool create $wificlientsrrd \
     --step 300 \
     DS:wificlients:GAUGE:600:-273:1000 \
     RRA:AVERAGE:0.5:1:1200 \
     RRA:MIN:0.5:12:2400 \
     RRA:MAX:0.5:12:2400 \
     RRA:AVERAGE:0.5:12:2400`;
    }

    rrdtool_update($wificlientsrrd,"N:".$wificlients1);

    $graphs['wifi_clients'] = TRUE;

  }

  if (isset($wificlients2) && $wificlients2 != "")
  {
    $wificlientsrrd  = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename("wificlients-radio2.rrd");

    if (!is_file($wificlientsrrd)) {
      `rrdtool create $wificlientsrrd \
     --step 300 \
     DS:wificlients:GAUGE:600:-273:1000 \
     RRA:AVERAGE:0.5:1:1200 \
     RRA:MIN:0.5:12:2400 \
     RRA:MAX:0.5:12:2400 \
     RRA:AVERAGE:0.5:12:2400`;
    }

    rrdtool_update($wificlientsrrd,"N:".$wificlients2);

    $graphs['wifi_clients'] = TRUE;

  }

  unset($wificlients2, $wificlients1);

}

echo("\n");

?>
