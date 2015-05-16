<?php
if ($device['os'] == 'fiberhome')
{
    $temperature = snmp_get($device, "sysTemperature.0", "-Oqv", "GEPON-OLT-COMMON-MIB");
    if (is_numeric($temperature)) {
        echo("Fiberhome - $temperature \n");
        discover_sensor($valid['sensor'], 'temperature', $device, "GEPON-OLT-COMMON-MIB::sysTemperature", "0", 'fiberhome',"Internal Temperature", '1', '1', "20" , NULL, NULL, "50", $temperature);
    }
}
?>