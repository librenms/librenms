<?php

// APC
if ($device['os'] == "apc")
{
  # PDU - Phase
  $oids = snmp_walk($device, "rPDUStatusPhaseIndex", "-OsqnU", "PowerNet-MIB");
  if ($oids)
  {
    if ($debug) { echo($oids."\n"); }
    $oids = trim($oids);
    if ($oids) echo("APC PowerNet-MIB Phase ");
    $type = "apc";
    $precision = "10";
    foreach (explode("\n", $oids) as $data)
    {
      $data = trim($data);
      if ($data)
      {
        list($oid,$kind) = explode(" ", $data);
        $split_oid = explode('.',$oid);
        $index = $split_oid[count($split_oid)-1];

        #rPDULoadStatusPhaseNumber
        $phase     = snmp_get($device, "1.3.6.1.4.1.318.1.1.12.2.3.1.1.4.".$index, "-Oqv", "");

        #rPDULoadStatusLoad
        $current   = snmp_get($device, "1.3.6.1.4.1.318.1.1.12.2.3.1.1.2.".$index, "-Oqv", "") / $precision;

        #rPDULoadPhaseConfigOverloadThreshold
        $limit     = snmp_get($device, "1.3.6.1.4.1.318.1.1.12.2.2.1.1.4.".$index, "-Oqv", "");

        #rPDULoadPhaseConfigOverloadThreshold
        $lowlimit  = snmp_get($device, "1.3.6.1.4.1.318.1.1.12.2.2.1.1.2.".$index, "-Oqv", "");

        #rPDULoadPhaseConfigNearOverloadThreshold
        $warnlimit = snmp_get($device, "1.3.6.1.4.1.318.1.1.12.2.2.1.1.3.".$index, "-Oqv", "");

        if (count(explode("\n",$oids)) != 1)
        {
          $descr     = "Phase $phase";
        }
        else
        {
          $descr     = "Output";
        }

        discover_sensor($valid['sensor'], 'current', $device, $current_oid, $index, $type, $descr, '10', '1', $lowlimit, NULL, $warnlimit, $limit, $current);
      }
    }
  }

  unset($oids);

  #v2 firmware- first bank is total, v3 firmware, 3rd bank is total
  $oids = snmp_walk($device, "rPDULoadBankConfigIndex", "-OsqnU", "PowerNet-MIB");        # should work with firmware v2 and v3
  if ($oids)
  {
    echo("APC PowerNet-MIB Banks ");
    if ($debug) { echo($oids."\n"); }
    $oids = trim($oids);
    $type = "apc";
    $precision = "10";

    # version 2 does some stuff differently- total power is first oid in index instead of the last.
    # will look something like "AOS v2.6.4 / App v2.6.5"
    $baseversion = "3";
    if (stristr($device['version'], 'AOS v2') == TRUE) { $baseversion = "2"; }

    foreach (explode("\n", $oids) as $data)
    {
      $data = trim($data);
      if ($data)
      {
        list($oid,$kind) = explode(" ", $data);
        $split_oid = explode('.',$oid);

        $index = $split_oid[count($split_oid)-1];

        $banknum = $index -1;
        $descr = "Bank ".$banknum;
        if ($baseversion == "3")
        {
          if ($index == "1") { $descr = "Bank Total"; }
        }
        if ($baseversion == "2")
        {
          if ($index == "1") { $descr = "Bank Total"; }
        }

        #rPDULoadStatusBankNumber
        $bank      = snmp_get($device, "1.3.6.1.4.1.318.1.1.12.2.3.1.1.5.".$index, "-Oqv", "");

        #rPDULoadStatusLoad
        $current   = snmp_get($device, "1.3.6.1.4.1.318.1.1.12.2.3.1.1.2.".$index, "-Oqv", "") / $precision;

        #rPDULoadBankConfigOverloadThreshold
        $limit     = snmp_get($device, "1.3.6.1.4.1.318.1.1.12.2.4.1.1.4.".$index, "-Oqv", "");

        #rPDULoadBankConfigLowLoadThreshold
        $lowlimit  = snmp_get($device, "1.3.6.1.4.1.318.1.1.12.2.4.1.1.2.".$index, "-Oqv", "");
        
        #rPDULoadBankConfigNearOverloadThreshold
        $warnlimit = snmp_get($device, "1.3.6.1.4.1.318.1.1.12.2.4.1.1.3.".$index, "-Oqv", "");

        if ($limit < 0 and $lowlimit < 0 and $warnlimit < 0){
            #rPDULoadPhaseConfigOverloadThreshold
            $limit     = snmp_get($device, "1.3.6.1.4.1.318.1.1.12.2.2.1.1.4.".$index, "-Oqv", "");

            #rPDULoadPhaseConfigLoLoadThreshold
            $lowlimit  = snmp_get($device, "1.3.6.1.4.1.318.1.1.12.2.2.1.1.2.".$index, "-Oqv", "");

            #rPDULoadPhaseConfigNearOverloadThreshold
            $warnlimit = snmp_get($device, "1.3.6.1.4.1.318.1.1.12.2.2.1.1.3.".$index, "-Oqv", "");

            echo "Phase ";
        }
        discover_sensor($valid['sensor'], 'current', $device, $current_oid, $index, $type, $descr, '10', '1', $lowlimit, NULL, $warnlimit, $limit, $current);
      }
    }

    unset($baseversion);
  }

  unset($oids);

 #Per Outlet Power Bar
  $oids = snmp_walk($device, "1.3.6.1.4.1.318.1.1.26.9.4.3.1.1", "-t 30 -OsqnU", "PowerNet-MIB");
  if ($oids)
  {
    echo("APC PowerNet-MIB Outlets ");
    if ($debug) { echo($oids."\n"); }
    $oids = trim($oids);
    $type = "apc";
    $precision = "10";

    foreach (explode("\n", $oids) as $data)
    {
      $data = trim($data);
      if ($data)
      {
        list($oid,$kind) = explode(" ", $data);
        $split_oid = explode('.',$oid);

        $index = $split_oid[count($split_oid)-1];

        #rPDU2PhaseStatusVoltage
        $voltage   = snmp_get($device, "1.3.6.1.4.1.318.1.1.26.6.3.1.6", "-Oqv", "");

        #rPDU2OutletMeteredStatusCurrent
        $current   = snmp_get($device, "1.3.6.1.4.1.318.1.1.26.9.4.3.1.6.".$index, "-Oqv", "") / $precision;
        
        #rPDU2OutletMeteredConfigOverloadCurrentThreshold
        $limit     = snmp_get($device, "1.3.6.1.4.1.318.1.1.26.9.4.1.1.7.".$index, "-Oqv", "") / $voltage;

        #rPDU2OutletMeteredConfigLowLoadCurrentThreshold
        $lowlimit  = snmp_get($device, "1.3.6.1.4.1.318.1.1.26.9.4.1.1.7.".$index, "-Oqv", "") / $voltage;

        #rPDU2OutletMeteredConfigNearOverloadCurrentThreshold
        $warnlimit = snmp_get($device, "1.3.6.1.4.1.318.1.1.26.9.4.1.1.6.".$index, "-Oqv", "") / $voltage;

        #rPDU2OutletMeteredStatusName
        $descr     = "Outlet " . $index . " - " .  snmp_get($device, "1.3.6.1.4.1.318.1.1.26.9.4.3.1.3.".$index, "-Oqv", "");

        discover_sensor($valid['sensor'], 'current', $device, $current_oid, $index, $type, $descr, '10', '1', $lowlimit, NULL, $warnlimit, $limit, $current);
      }
    }
  }

  unset($oids);

  # ATS
  $oids = snmp_walk($device, "atsConfigPhaseTableIndex", "-OsqnU", "PowerNet-MIB");
  if ($oids)
  {
    $type = "apc";
    if ($debug) { print_r($oids); }
    $oids = trim($oids);
    if ($oids) echo("APC PowerNet-MIB ATS ");
    $index         = 1;

    #atsOutputCurrent
    $current   = snmp_get($device, "1.3.6.1.4.1.318.1.1.8.5.4.3.1.4.1.1.1", "-Oqv", "") / $precision;

    #atsConfigPhaseOverLoadThreshold
    $limit     = snmp_get($device, "1.3.6.1.4.1.318.1.1.8.4.16.1.5.1", "-Oqv", ""); # No / $precision here! Nice, APC!

    #atsConfigPhaseLowLoadThreshold
    $lowlimit  = snmp_get($device, "1.3.6.1.4.1.318.1.1.8.4.16.1.3.1", "-Oqv", ""); # No / $precision here! Nice, APC!

    #atsConfigPhaseNearOverLoadThreshold
    $warnlimit = snmp_get($device, "1.3.6.1.4.1.318.1.1.8.4.16.1.4.1", "-Oqv", ""); # No / $precision here! Nice, APC!

    $descr     = "Output Feed";

    discover_sensor($valid['sensor'], 'current', $device, $current_oid, $index, $type, $descr, '10', '1', $lowlimit, NULL, $warnlimit, $limit, $current);
  }

  unset($oids);

    # UPS

    $oid_array = array(
                     array('HighPrecOid' => 'upsHighPrecOutputCurrent', 'AdvOid' => 'upsAdvOutputCurrent', 'type' => 'apc', 'index' => 0, 'descr' => 'Current Drawn', 'divisor' => 10, 'mib' => '+PowerNet-MIB'),
                 );
    foreach ($oid_array as $item) {
        $low_limit = NULL;
        $low_limit_warn = NULL;
        $warn_limit = NULL;
        $high_limit = NULL;
        $oids = snmp_get($device, $item['HighPrecOid'].'.'.$item['index'], "-OsqnU", $item['mib']);
        if (empty($oids)) {
            $oids = snmp_get($device, $item['AdvOid'].'.'.$item['index'], "-OsqnU", $item['mib']);
            $current_oid = $item['AdvOid'];
        } else {
            $current_oid = $item['HighPrecOid'];
        }
        if (!empty($oids)) {
            if ($debug) {
                print_r($oids);
            }
            $oids = trim($oids);
            if ($oids) {
                echo $item['type'] . ' ' . $item['mib'] . ' UPS';
            }
            if (stristr($current_oid, "HighPrec")) {
                $current = $oids / $item['divisor'];
            } else {
                $current = $oids;
                $item['divisor'] = 1;
            }
            discover_sensor($valid['sensor'], 'current', $device, $current_oid.'.'.$item['index'], $current_oid.'.'.$item['index'], $item['type'], $item['descr'], $item['divisor'], 1, $low_limit, $low_limit_warn, $warn_limit, $high_limit, $current);
        }
    }
}

?>
