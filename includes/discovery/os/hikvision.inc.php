<?php

if (!$os)
{
    if (preg_match("/Hikvision/", snmp_get($device, ".1.3.6.1.4.1.39165.1.6.0", "-Oqv", ""))) {
      $os = "hikvision";
    }
}

