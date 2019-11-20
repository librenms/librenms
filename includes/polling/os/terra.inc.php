<?php

$query = array(
  array("sda410C", "5"),
  array("sta410C", "6"),
  array("saa410C", "7"),
  array("sdi410C", "8"),
  array("sti410C", "9"),
  array("sai410C", "10"),
  array("ttd440",  "14"),
  array("ttx410C", "15"),
  array("tdx410C", "16"),
  array("sdi480",  "17"),
  array("sti440",  "18")
);

foreach ($query as $row) {
    if (strpos($device["sysDescr"], $row[0]) !== false) {
        $oid_terra = ".1.3.6.1.4.1.30631.1.";
        $oid = array($oid_terra.$row[1].".4.1.0", $oid_terra.$row[1].".4.2.0");

        $data = snmp_get_multi_oid($device, $oid);
        $hardware = $row[0];
        $version = trim($data[$oid[0]], '"');
        $serial = trim($data[$oid[1]], '"');

        unset($oid);
        unset($data);
    }
}
unset($query);
