<?php

 $sensor_value = trim(str_replace('"', '', snmp_get($device, $sensor['sensor_oid'], '-OUqnv', 'SNMPv2-MIB:WHISP-BOX-MIBV2-MIB')));
