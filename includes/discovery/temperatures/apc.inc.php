<?php

if ($device['os'] == "apc")
{
  $oids = snmp_get($device, "1.3.6.1.4.1.318.1.1.1.2.2.2.0", "-OsqnU", "");
  if ($debug) { echo($oids."\n"); }
  if ($oids)
  {
    echo("APC UPS Internal ");
    list($oid,$current) = explode(' ',$oids);
    $precision = 1;
    $type = "apc";
    $index = 0;
    $descr = "Internal Temperature";

    discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'apc', $descr, '1', '1', NULL, NULL, NULL, NULL, $current);
  }
  # InRow Chiller.
  # A silly check to find out if it's the right hardware.
  $oids = snmp_get($device, "airIRRCGroupSetpointsCoolMetric.0", "-OsqnU", "PowerNet-MIB");
  if ($oids)
  {
    echo("APC InRow Chiller ");
    $temps = array();
    $temps['airIRRCUnitStatusRackInletTempMetric'] = "Rack Inlet";
    $temps['airIRRCUnitStatusSupplyAirTempMetric'] = "Supply Air";
    $temps['airIRRCUnitStatusReturnAirTempMetric'] = "Return Air";
    $temps['airIRRCUnitStatusEnteringFluidTemperatureMetric'] = "Entering Fluid";
    $temps['airIRRCUnitStatusLeavingFluidTemperatureMetric'] = "Leaving Fluid";
    foreach ($temps as $obj => $descr)
    {
      $oids = snmp_get($device, $obj . ".0", "-OsqnU", "PowerNet-MIB");
      list($oid,$current) = explode(' ',$oids);
      $divisor = 10;
      $sensorType = substr($descr, 0, 2);
      echo(discover_sensor($valid['sensor'], 'temperature', $device, $oid, '0', $sensorType, $descr, $divisor, '1', NULL, NULL, NULL, NULL, $current));
    }
  }
}

?>
