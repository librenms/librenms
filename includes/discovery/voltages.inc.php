<?php

echo("Voltages : ");

## LMSensors Voltages
if ($device['os'] == "linux") 
{
  $oids = snmp_walk($device, "lmVoltSensorsDevice", "-OsqnU", "LM-SENSORS-MIB");
  if ($debug) { echo($oids."\n"); }
  if ($oids) echo("LM-SENSORS ");
  $divisor = 1000;
  $type = "lmsensors";
  foreach(explode("\n", $oids) as $data) 
  {
    $data = trim($data);
    if ($data) 
    {
      list($oid,$descr) = explode(" ", $data,2);
      $split_oid = explode('.',$oid);
      $index = $split_oid[count($split_oid)-1];
      $oid  = "1.3.6.1.4.1.2021.13.16.4.1.3." . $index;
      $current = snmp_get($device, $oid, "-Oqv", "LM-SENSORS-MIB") / $divisor;
      echo discover_sensor($valid_sensor, 'voltage', $device, $oid, $index, $type, $descr, $divisor, '1', NULL, NULL, NULL, NULL, $current);
    }
  }
}

## Areca Voltages
if ($device['os'] == "areca") 
{
  $oids = snmp_walk($device, "1.3.6.1.4.1.18928.1.2.2.1.8.1.2", "-OsqnU", "");
  if ($debug) { echo($oids."\n"); }
  if ($oids) echo("Areca ");
  $divisor = 1000;
  $type = "areca";
  foreach(explode("\n", $oids) as $data) 
  {
    $data = trim($data);
    if ($data) 
    {
      list($oid,$descr) = explode(" ", $data,2);
      $split_oid = explode('.',$oid);
      $index = $split_oid[count($split_oid)-1];
      $oid  = "1.3.6.1.4.1.18928.1.2.2.1.8.1.3." . $index;
      $current = snmp_get($device, $oid, "-Oqv", "") / $divisor;
      if ($descr != '"Battery Status"' || $current != 0.255) # FIXME not sure if this is supposed to be a voltage, but without BBU it's 225, then ignore.
      {
        echo discover_sensor($valid_sensor, 'voltage', $device, $oid, $index, $type, $descr, $divisor, '1', NULL, NULL, NULL, NULL, $current);
      }
    }
  }
}

## APC Voltages
if ($device['os'] == "apc") 
{
  $oids = snmp_walk($device, "1.3.6.1.4.1.318.1.1.8.5.3.3.1.3", "-OsqnU", "");
  if ($debug) { echo($oids."\n"); }
  if ($oids) echo("APC In ");
  $divisor = 1;
  $type = "apc";
  foreach(explode("\n", $oids) as $data) 
  {
    $data = trim($data);
    if ($data) 
    {
      list($oid,$current) = explode(" ", $data,2);
      $split_oid = explode('.',$oid);
      $index = $split_oid[count($split_oid)-3];
      $oid  = "1.3.6.1.4.1.318.1.1.8.5.3.3.1.3." . $index . ".1.1";
      $descr = "Input Feed " . chr(64+$index);
      echo discover_sensor($valid_sensor, 'voltage', $device, $oid, "3.3.1.3.$index", $type, $descr, $divisor, '1', NULL, NULL, NULL, NULL, $current);

    }
  }

  $oids = snmp_walk($device, "1.3.6.1.4.1.318.1.1.8.5.4.3.1.3", "-OsqnU", "");
  if ($debug) { echo($oids."\n"); }
  if ($oids) echo(" APC Out ");
  $divisor = 1;
  $type = "apc";
  foreach(explode("\n", $oids) as $data) 
  {
    $data = trim($data);
    if ($data) 
    {
      list($oid,$current) = explode(" ", $data,2);
      $split_oid = explode('.',$oid);
      $index = $split_oid[count($split_oid)-3];
      $oid  = "1.3.6.1.4.1.318.1.1.8.5.4.3.1.3." . $index . ".1.1";
      $descr = "Output Feed"; if (count(explode("\n", $oids)) > 1) { $descr .= " $index"; }
      echo discover_sensor($valid_sensor, 'voltage', $device, $oid, "4.3.1.3.$index", $type, $descr, $divisor, '1', NULL, NULL, NULL, NULL, $current);
    }
  }

  $oids = snmp_get($device, "1.3.6.1.4.1.318.1.1.1.3.2.1.0", "-OsqnU", "");
  if ($debug) { echo($oids."\n"); }
  if ($oids)
  {
    echo(" APC In ");
    list($oid,$current) = explode(" ",$oids);
    $divisor = 1;
    $type = "apc";
    $index = "3.2.1.0";
    $descr = "Input";
    echo discover_sensor($valid_sensor, 'voltage', $device, $oid, $index, $type, $descr, $divisor, '1', NULL, NULL, NULL, NULL, $current);
  }

  $oids = snmp_get($device, "1.3.6.1.4.1.318.1.1.1.4.2.1.0", "-OsqnU", "");
  if ($debug) { echo($oids."\n"); }
  if ($oids)
  {
    echo(" APC Out ");
    list($oid,$current) = explode(" ",$oids);
    $divisor = 1;
    $type = "apc";
    $index = "4.2.1.0";
    $descr = "Output";
    echo discover_sensor($valid_sensor, 'voltage', $device, $oid, $index, $type, $descr, $divisor, '1', NULL, NULL, NULL, NULL, $current);
  }
}

## Supermicro Voltages
if ($device['os'] == "linux") 
{
  $oids = snmp_walk($device, "1.3.6.1.4.1.10876.2.1.1.1.1.3", "-OsqnU", "SUPERMICRO-HEALTH-MIB");
  if ($debug) { echo($oids."\n"); }
  $oids = trim($oids);
  if ($oids) echo("Supermicro ");
  $type = "supermicro";
  $divisor = "1000";
  foreach(explode("\n", $oids) as $data) 
  {
    $data = trim($data);
    if ($data) 
    {
      list($oid,$kind) = explode(" ", $data);
      $split_oid = explode('.',$oid);
      $index = $split_oid[count($split_oid)-1];
      if ($kind == 1)
      {
        $volt_oid     = "1.3.6.1.4.1.10876.2.1.1.1.1.4.".$index;
        $descr_oid    = "1.3.6.1.4.1.10876.2.1.1.1.1.2.".$index;
        $monitor_oid  = "1.3.6.1.4.1.10876.2.1.1.1.1.10.".$index;
        $limit_oid    = "1.3.6.1.4.1.10876.2.1.1.1.1.5.".$index;
        $lowlimit_oid = "1.3.6.1.4.1.10876.2.1.1.1.1.6.".$index;

        $descr    = snmp_get($device, $descr_oid, "-Oqv", "SUPERMICRO-HEALTH-MIB");
        $current  = snmp_get($device, $volt_oid, "-Oqv", "SUPERMICRO-HEALTH-MIB") / $divisor;
        $limit    = snmp_get($device, $limit_oid, "-Oqv", "SUPERMICRO-HEALTH-MIB") / $divisor;
	$lowlimit = snmp_get($device, $lowlimit_oid, "-Oqv", "SUPERMICRO-HEALTH-MIB") / $divisor;
        $monitor  = snmp_get($device, $monitor_oid, "-Oqv", "SUPERMICRO-HEALTH-MIB");
        $descr    = trim(str_ireplace("Voltage", "", $descr));

        if ($monitor == 'true')
        {
          echo discover_sensor($valid_sensor, 'voltage', $device, $volt_oid, $index, $type, $descr, $divisor, '1', $lowlimit, NULL, $limit, NULL, $current);
        }
      }
    }
  }
}

## MGE UPS Voltages
if ($device['os'] == "mgeups") 
{
  echo("MGE ");
  $oids = trim(snmp_walk($device, "1.3.6.1.4.1.705.1.7.1", "-OsqnU"));
  if ($debug) { echo($oids."\n"); }
  list($unused,$numPhase) = explode(' ',$oids);
  for($i = 1; $i <= $numPhase;$i++)
  {
    $volt_oid   = ".1.3.6.1.4.1.705.1.7.2.1.2.$i";
    $descr      = "Output"; if ($numPhase > 1) $descr .= " Phase $i";
    $current    = snmp_get($device, $volt_oid, "-Oqv");
    if (!$current) 
    {
      $volt_oid .= ".0";
      $current    = snmp_get($device, $volt_oid, "-Oqv");
    }
    $current   /= 10;
    $type       = "mge-ups";
    $divisor  = 10;
    $index      = $i;
    echo discover_sensor($valid_sensor, 'voltage', $device, $volt_oid, $index, $type, $descr, $divisor, '1', NULL, NULL, NULL, NULL, $current);
  }
  $oids = trim(snmp_walk($device, "1.3.6.1.4.1.705.1.6.1", "-OsqnU"));
  if ($debug) { echo($oids."\n"); }
  list($unused,$numPhase) = explode(' ',$oids);
  for($i = 1; $i <= $numPhase;$i++)
  {
    $volt_oid   = ".1.3.6.1.4.1.705.1.6.2.1.2.$i";
    $descr      = "Input"; if ($numPhase > 1) $descr .= " Phase $i";
    $current    = snmp_get($device, $volt_oid, "-Oqv");
    if (!$current) 
    {
      $volt_oid .= ".0";
      $current    = snmp_get($device, $volt_oid, "-Oqv");
    }
    $current   /= 10;
    $type       = "mge-ups";
    $divisor  = 10;
    $index      = 100+$i;
    echo discover_sensor($valid_sensor, 'voltage', $device, $volt_oid, $index, $type, $descr, $divisor, '1', NULL, NULL, NULL, NULL, $current);
  }
}

## Riello UPS Voltages
if ($device['os'] == "netmanplus") 
{
  echo("NetMan Plus ");
  
  $oids = snmp_walk($device, "1.3.6.1.2.1.33.1.2.5", "-Osqn", "UPS-MIB");
  if ($debug) { echo($oids."\n"); }
  $oids = trim($oids);
  foreach(explode("\n", $oids) as $data) 
  {
    $data = trim($data);
    if ($data) 
    {
      list($oid,$descr) = explode(" ", $data,2);
      $split_oid = explode('.',$oid);
      $volt_id = $split_oid[count($split_oid)-1];
      $volt_oid  = "1.3.6.1.2.1.33.1.2.5.$volt_id";
      $divisor = 10;
      $volt = snmp_get($device, $volt_oid, "-O vq") / $divisor;
      #$volt = trim(shell_exec($config['snmpget'] . " -O qv -$snmpver -c $community $hostname:$port $volt_oid")) / $divisor;
      $descr = "Battery" . (count(explode("\n",$oids)) == 1 ? '' : ' ' . ($volt_id+1));
      $type = "netmanplus";
      $index = 500+$volt_id;
      echo discover_sensor($valid_sensor, 'voltage', $device, $volt_oid, $index, $type, $descr, $divisor, '1', NULL, NULL, NULL, NULL, $volt);
    }
  }

  $oids = trim(snmp_walk($device, "1.3.6.1.2.1.33.1.4.3.0", "-OsqnU"));
  if ($debug) { echo($oids."\n"); }
  list($unused,$numPhase) = explode(' ',$oids);
  for($i = 1; $i <= $numPhase;$i++)
  {
    $volt_oid   = ".1.3.6.1.2.1.33.1.4.4.1.2.$i";
    $descr      = "Output"; if ($numPhase > 1) $descr .= " Phase $i";
    $current    = snmp_get($device, $volt_oid, "-Oqv");
    $type       = "netmanplus";
    $divisor  = 1;
    $index      = $i;
    echo discover_sensor($valid_sensor, 'voltage', $device, $volt_oid, $index, $type, $descr, $divisor, '1', NULL, NULL, NULL, NULL, $current);
  }

  $oids = trim(snmp_walk($device, "1.3.6.1.2.1.33.1.3.2.0", "-OsqnU"));
  if ($debug) { echo($oids."\n"); }
  list($unused,$numPhase) = explode(' ',$oids);
  for($i = 1; $i <= $numPhase;$i++)
  {
    $volt_oid   = "1.3.6.1.2.1.33.1.3.3.1.3.$i";
    $descr      = "Input"; if ($numPhase > 1) $descr .= " Phase $i";
    $current    = snmp_get($device, $volt_oid, "-Oqv");
    $type       = "netmanplus";
    $divisor  = 1;
    $index      = 100+$i;
    echo discover_sensor($valid_sensor, 'voltage', $device, $volt_oid, $index, $type, $descr, $divisor, '1', NULL, NULL, NULL, NULL, $current);
  }

  $oids = trim(snmp_walk($device, "1.3.6.1.2.1.33.1.5.2.0", "-OsqnU"));
  if ($debug) { echo($oids."\n"); }
  list($unused,$numPhase) = explode(' ',$oids);
  for($i = 1; $i <= $numPhase;$i++)
  {
    $volt_oid   = "1.3.6.1.2.1.33.1.5.3.1.2.$i";
    $descr      = "Bypass"; if ($numPhase > 1) $descr .= " Phase $i";
    $current    = snmp_get($device, $volt_oid, "-Oqv");
    $type       = "netmanplus";
    $divisor  = 1;
    $index      = 200+$i;
    echo discover_sensor($valid_sensor, 'voltage', $device, $volt_oid, $index, $type, $descr, $divisor, '1', NULL, NULL, NULL, NULL, $current);
  }
}

if ($device['os'] == "gamatronicups") 
{
  for($i = 1; $i <= 3 ;$i++) 
  {
    $volt_oid   = "GAMATRONIC-MIB::gamatronicLTD.5.4.1.1.2.$i";
    $descr = "Input Phase $i";
    $volt = snmp_get($device, $volt_oid, "-Oqv");
    $type = "gamatronicups";
    $divisor = 1;
    $index = $i;
    $lowlimit = 0;
    $limit = NULL;
    echo discover_sensor($valid_sensor, 'voltage', $device, $volt_oid, $index, $type, $descr, $divisor, '1', NULL, NULL, NULL, NULL, $volt);
  }

  for($i = 1; $i <= 3 ;$i++) 
  {
    $volt_oid   = "GAMATRONIC-MIB::gamatronicLTD.5.5.1.1.2.$i";
    $descr = "Output Phase $i";
    $volt = snmp_get($device, $volt_oid, "-Oqv");
    $type = "gamatronicups";
    $divisor = 1;
    $index = 100+$i;
    $lowlimit = 0;
    $limit = NULL;
    echo discover_sensor($valid_sensor, 'voltage', $device, $volt_oid, $index, $type, $descr, $divisor, '1', NULL, NULL, NULL, NULL, $volt);
  }

}

if($debug) { print_r($valid['voltage']); }

check_valid_sensors($device, 'voltage', $valid_sensor);

echo("\n");

?>
