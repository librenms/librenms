<?php

# SNMPv2-SMI::enterprises.253.8.51.10.2.1.7.2.28110202 = STRING: "MFG:Dell;CMD:PJL,RASTER,DOWNLOAD,PCLXL,PCL,POSTSCRIPT;MDL:Laser Printer
# 3100cn;DES:Dell Laser Printer 3100cn;CLS:PRINTER;STS:AAAMAwAAAAAAAgJ/HgMKBigDCgY8AwAzcJqwggAAwAAACAAAAAAA/w==;"

$dellinfo = explode(';',trim(snmp_get($device, "1.3.6.1.4.1.253.8.51.10.2.1.7.2.28110202", "-OQv", "", ""),'" '));

foreach ($dellinfo as $dellinf)
{
  list($key,$value) = explode(':',$dellinf);
  $dell_laser[$key] = $value;
}

$hardware = $dell_laser['MDL'];

list(,$version) = explode('Engine ',$poll_device['sysDescr']);

$version = "Engine " . trim($version,')');

?>