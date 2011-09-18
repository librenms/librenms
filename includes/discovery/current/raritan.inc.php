<?php

// blindly copied from sentry3

if ($device['os'] == 'raritan')
{
  $divisor = "1000";
  $outlet_divisor = $divisor;
  $multiplier = "1";

        #################################
        # Check for per-outlet polling
        $outlet_oids = snmp_walk($device, "outletIndex", "-Osqn", "PDU-MIB");
        $outlet_oids = trim($outlet_oids);

        if ($outlet_oids) echo("PDU Outlet ");
        foreach (explode("\n", $outlet_oids) as $outlet_data)
        {
          $outlet_data = trim($outlet_data);
          if ($outlet_data)
          {
            list($outlet_oid,$outlet_descr) = explode(" ", $outlet_data,2);
            $outlet_split_oid = explode('.',$outlet_oid);
            $outlet_index = $outlet_split_oid[count($outlet_split_oid)-1];

            $outletsuffix = "$outlet_index";
            $outlet_insert_index=$outlet_index;

            #outletLoadValue: "A non-negative value indicates the measured load in milli Amps"
            $outlet_oid             = ".1.3.6.1.4.1.13742.4.1.2.2.1.4.$outletsuffix";
            $outlet_descr           = snmp_get($device,"outletLabel.$outletsuffix", "-Ovq", "PDU-MIB");
            $outlet_low_warn_limit  = NULL;
            $outlet_low_limit       = NULL;
            $outlet_high_warn_limit = snmp_get($device,"outletCurrentUpperWarning.$outletsuffix", "-Ovq", "PDU-MIB") / $outlet_divisor;
            $outlet_high_limit      = snmp_get($device,"outletCurrentUpperCritical.$outletsuffix", "-Ovq", "PDU-MIB") / $outlet_divisor;
            $outlet_current         = snmp_get($device,"outletCurrent.$outletsuffix", "-Ovq", "PDU-MIB") / $outlet_divisor;

            if ($outlet_current >= 0) {
              discover_sensor($valid['sensor'], 'current', $device, $outlet_oid, $outlet_insert_index, 'raritan', $outlet_descr, $outlet_divisor, $multiplier, $outlet_low_limit, $outlet_low_warn_limit, $outlet_high_warn_limit, $outlet_high_limit, $outlet_current);
            }
          } // if ($outlet_data)

//          unset($outlet_data);
//          unset($outlet_oids);
//          unset($outlet_oid);
//          unset($outlet_descr);
//          unset($outlet_low_warn_limit);
//          unset($outlet_low_limit);
//          unset($outlet_high_warn_limit);
//          unset($outlet_high_limit);
//          unset($outlet_current);

        } // foreach (explode("\n", $outlet_oids) as $outlet_data)
}

?>
