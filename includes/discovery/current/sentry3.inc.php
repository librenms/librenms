<?php

global $valid_sensor;

if ($device['os'] == 'sentry3')
{
  $oids = snmp_walk($device, "infeedPower", "-Osqn", "Sentry3-MIB");
  $tower_count = snmp_get($device,"systemTowerCount.0", "-Ovq", "Sentry3-MIB");

  $towers=1;
  while($towers <= $tower_count) {

    $divisor = "100";
    $outlet_divisor = $divisor;
    $multiplier = "1";
    if ($debug) { echo($oids."\n"); }
    $oids = trim($oids);
    if ($oids) echo("ServerTech Sentry Infeed ");
    foreach (explode("\n", $oids) as $data)
    {
      $data = trim($data);
      if ($data)
      {
        list($oid,$descr) = explode(" ", $data,2);
        $split_oid = explode('.',$oid);
        $index = $split_oid[count($split_oid)-1];

        #infeedLoadValue
        $infeed_oid      = "1.3.6.1.4.1.1718.3.2.2.1.7." . $towers . "." . $index;

        $descr_string    = snmp_get($device,"infeedID.$towers.$index", "-Ovq", "Sentry3-MIB");
        #$descr           = "Infeed " . $towers . " " . $descr_string;
        $descr           = "Infeed $descr_string";
        $low_warn_limit  = NULL;
        $low_limit       = NULL;
        $high_warn_limit = snmp_get($device,"infeedLoadHighThresh.$towers.$index", "-Ovq", "Sentry3-MIB");
        $high_limit      = snmp_get($device,"infeedCapacity.$towers.$index", "-Ovq", "Sentry3-MIB");
        $current         = snmp_get($device,"$infeed_oid", "-Ovq", "Sentry3-MIB") / $divisor;

        discover_sensor($valid_sensor, 'current', $device, $infeed_oid, $index, 'sentry3', $descr, $divisor, $multiplier, $low_limit, $low_warn_limit, $high_warn_limit, $high_limit, $current);

        # Check for per-outlet polling
        $outlet_oids = snmp_walk($device, "outletLoadValue.$towers.$index", "-Osqn", "Sentry3-MIB");
        $outlet_oids = trim($outlet_oids);
        if ($outlet_oids) echo("ServerTech Sentry Outlet ");
        foreach (explode("\n", $outlet_oids) as $outlet_data)
        {
          $data = trim($outlet_data);
          if ($outlet_data)
          {
            list($outlet_oid,$outlet_descr) = explode(" ", $outlet_data,2);
            $outlet_split_oid = explode('.',$outlet_oid);
            $outlet_index = $outlet_split_oid[count($outlet_split_oid)-1];

            $outletsuffix = "$towers.$index.$outlet_index";
            $outlet_insert_index=$index . $outlet_index;

            #outletLoadValue: "A non-negative value indicates the measured load in hundredths of Amps"
            $outlet_oid             = "1.3.6.1.4.1.1718.3.2.3.1.7.$outletsuffix";
            $outlet_descr_string    = snmp_get($device,"outletID.$outletsuffix", "-Ovq", "Sentry3-MIB");
            $outlet_descr           = "Outlet $outlet_descr_string";
            $outlet_low_warn_limit  = NULL;
            $outlet_low_limit       = NULL;
            $outlet_high_warn_limit = NULL;
            # Should be "outletCapacity" but is always -1. According to MIB: "A negative value indicates that the capacity was not available."
            $outlet_high_limit = snmp_get($device,"outletLoadHighThresh.$outletsuffix", "-Ovq", "Sentry3-MIB");
            $outlet_current         = snmp_get($device,"$outlet_oid", "-Ovq", "Sentry3-MIB") / $outlet_divisor;

            discover_sensor($valid_sensor, 'current', $device, $outlet_oid, $outlet_insert_index, 'sentry3', $outlet_descr, $outlet_divisor, $multiplier, $outlet_low_limit, $outlet_low_warn_limit, $outlet_high_warn_limit, $outlet_high_limit, $outlet_current);

          } // if ($outlet_data)

          unset($outlet_data);

        } // foreach (explode("\n", $outlet_oids) as $outlet_data)

        unset($outlet_oids);
        unset($outlet_oid);
        unset($outlet_descr_string);
        unset($outlet_descr);
        unset($outlet_low_warn_limit);
        unset($outlet_low_limit);
        unset($outlet_high_warn_limit);
        unset($outlet_high_limit);
        unset($outlet_current);

      } //if($data)

      unset($data);

    } //foreach (explode("\n", $oids) as $data)

    unset($oids);
    unset($oid);
    unset($descr_string);
    unset($descr);
    unset($low_warn_limit);
    unset($low_limit);
    unset($high_warn_limit);
    unset($high_limit);
    unset($current);

    $towers++;

  } // while($towers <= $tower_count)

}

?>
