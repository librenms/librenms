<?php

// Check Inlets
$inlet_oids = snmp_walk($device, 'inletLabel', '-Osqn', 'PDU2-MIB');
$inlet_oids = trim($inlet_oids);
if ($inlet_oids) {
    echo 'PDU Inlet ';
}

foreach (explode("\n", $inlet_oids) as $inlet_data) {
    $inlet_data = trim($inlet_data);
    if ($inlet_data) {
        list($inlet_oid,$inlet_descr) = explode(' ', $inlet_data, 2);
        $inlet_split_oid              = explode('.', $inlet_oid);
        $inlet_index                  = $inlet_split_oid[(count($inlet_split_oid) - 2)].'.'.$inlet_split_oid[(count($inlet_split_oid) - 1)];

        $inlet_oid     = ".1.3.6.1.4.1.13742.6.5.2.3.1.4.$inlet_index.5";
        $inlet_divisor = pow(10, snmp_get($device, "inletSensorDecimalDigits.$inlet_index.activePower", '-Ovq', 'PDU2-MIB'));
        $inlet_power   = (snmp_get($device, "measurementsInletSensorValue.$inlet_index.activePower", '-Ovq', 'PDU2-MIB') / $inlet_divisor);

        if ($inlet_power >= 0) {
            discover_sensor($valid['sensor'], 'power', $device, $inlet_oid, $inlet_index, 'raritan', $inlet_descr, $inlet_divisor, 1, null, null, null, null, $inlet_power);
        }
    }
}

//Check Outlets - added 073278 27/06/219

$model = snmp_get($device, "PDU2-MIB::pduModel.1", "-Osqn", "PDU2-MIB");
$outlet_oids = snmp_walk($device, "PDU2-MIB::outletLabel.1", "-Osqn", "PDU2-MIB");
$outlet_oids = trim($outlet_oids);

if ($outlet_oids) echo("PDU2 Outlet ");

if (strpos($model,'PX3') !== false) { 
    foreach (explode("\n", $outlet_oids) as $outlet_data)
    {
      $outlet_data = trim($outlet_data);
      if ($outlet_data)
      {
        $scale = 0.1;
        list($outlet_oid,$outlet_descr) = explode(" ", $outlet_data,2);
        $outlet_split_oid = explode('.',$outlet_oid);
        $outlet_index = $outlet_split_oid[count($outlet_split_oid)-1];

        $outletsuffix = "$outlet_index";
        $outlet_insert_index = $outlet_index;

        $outlet_descr   = "Outlet $outletsuffix: " . snmp_get($device,"outletName.1.$outletsuffix", "-Ovq", "PDU2-MIB");
        $outlet_oid_pow     = ".1.3.6.1.4.1.13742.6.5.4.3.1.4.1.$outletsuffix.5";    
        $outlet_divisor_pow = pow(10, snmp_get($device, "outletSensorDecimalDigits.$outlet_index.activePower", '-Ovq', 'PDU2-MIB'));
        $outlet_power = (snmp_get($device,"measurementOutletSensorValue.1.$outletsuffix.activePower", "-Ovq", "PDU2-MIB") / $outlet_divisor_pow);

        if ($outlet_power >= 0)
        {
          discover_sensor($valid['sensor'], 'power', $device, $outlet_oid_pow, $outlet_insert_index, 'raritan', $outlet_descr, $outlet_divisor_pow, 1, null, null, null, null, $outlet_power);
        } // outlet ActivePower

      } // if ($outlet_data)
    } // foreach (explode("\n", $outlet_oids) as $outlet_data)
}
