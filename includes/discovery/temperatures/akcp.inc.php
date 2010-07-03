<?php


if($device['os'] == "akcp" || $device['os'] == "minkelsrms")
{
    $oids = snmp_walk($device, ".1.3.6.1.4.1.3854.1.2.2.1.16.1.4", "-Osqn", "");
    if ($debug) { echo($oids."\n"); }
    $oids = trim($oids);
    if ($oids) echo("AKCP ");
    foreach(explode("\n", $oids) as $data)
    {
      $data = trim($data);
      if ($data)
      {
        list($oid,$status) = explode(" ", $data,2);
        if ($status == 2) # 2 = normal, 0 = not connected
        {
          $split_oid = explode('.',$oid);
          $temp_id = $split_oid[count($split_oid)-1];
          $descr_oid = ".1.3.6.1.4.1.3854.1.2.2.1.16.1.1.$temp_id";
          $temp_oid = ".1.3.6.1.4.1.3854.1.2.2.1.16.1.3.$temp_id";
          $warnlimit_oid = ".1.3.6.1.4.1.3854.1.2.2.1.16.1.7.$temp_id";
          $limit_oid = ".1.3.6.1.4.1.3854.1.2.2.1.16.1.8.$temp_id";
          # .9 = low warn limit
          $lowlimit_oid = ".1.3.6.1.4.1.3854.1.2.2.1.16.1.10.$temp_id";

          $descr = trim(snmp_get($device, $descr_oid, "-Oqv", ""),'"');
          $temp = snmp_get($device, $temp_oid, "-Oqv", "");
          $warnlimit = snmp_get($device, $warnlimit_oid, "-Oqv", "");
          $limit = snmp_get($device, $limit_oid, "-Oqv", "");
          $lowlimit = snmp_get($device, $lowlimit_oid, "-Oqv", "");

          # FIXME no warnlimit in table/discover function yet...
          discover_temperature($valid_temp, $device, $temp_oid, $temp_id, "akcp", $descr, 1, $lowlimit, $limit, $temp);
        }
      }
    }
}

?>
