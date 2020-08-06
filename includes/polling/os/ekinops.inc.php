<?php
/**
* ekinops.php
*
* Ekinops Optical Networking OS polling
*
**/

$hardware = trim(snmp_get($device, "mgnt4nodeChassisType.1", "-OQv", "EKINOPS-MGNT4NODE-MIB"), '"');

$mgmtCard = trim(snmp_get($device, "mgnt2RinvHwPlatform.0", "-OQv", "EKINOPS-MGNT2-MIB"), '"');
$softInv = trim(snmp_get($device, "mgnt2RinvSoftwarePackage.0", "-OQv", "EKINOPS-MGNT2-MIB"), '"');


$mgmtInfo = ekiParser($mgmtCard);
$serial = $mgmtInfo['Serial Number'];

$softInfo = ekiParser($softInv);
$version = $softInfo['Active Release Name'];

function ekiParser($ekiInfo) {
    $info = explode("\n", $ekiInfo);
    unset($info[0]);

    foreach ($info as $line) {
        list($attr, $value) = explode(":", $line);
        $attr = trim($attr);
        $value = trim($value);
        $inv[$attr] = $value;
    }
    return $inv;
}
