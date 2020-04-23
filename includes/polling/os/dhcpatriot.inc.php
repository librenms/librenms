<?php

/*
 *
 * OIDs obtained from First Network Group Inc. DHCPatriot operations manual version 6.4.x
 * Found here: http://www.network1.net/products/dhcpatriot/documentation/PDFs/v64xmanual-rev1.pdf
 *
*/

$ft_tmp = trim(snmp_get($device, ".1.3.6.1.4.1.2021.51.12.4.1.2.7.76.73.67.69.78.83.69.1", "-Oqv"), '" ');

if (!empty($ft_tmp)) {
    if (str_contains($ft_tmp, "FULL:0")) {
        $features = "Non-Expiry License";
    }
    if (str_contains($ft_tmp, "LIMITED:")) {
        $ft_epoch = str_replace("LIMITED:", "", $ft_tmp);
        $ft_dt = new DateTime("@$ft_epoch");
        $features = "License Expires ".$ft_dt->format('Y-m-d');
    }
}

$hardware = trim(snmp_get($device, ".1.3.6.1.4.1.2021.52.7.4.1.2.5.77.79.68.69.76.1", "-Oqv"), '" ');
$serial = trim(snmp_get($device, ".1.3.6.1.4.1.2021.52.8.4.1.2.6.83.69.82.73.65.76.1", "-Oqv"), '" ');
$version = trim(snmp_get($device, ".1.3.6.1.4.1.2021.51.11.4.1.2.7.86.69.82.83.73.79.78.1", "-Oqv"), '" ');

unset($ft_tmp, $ft_epoch, $ft_dt);
