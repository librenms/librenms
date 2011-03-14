<?php

if (!$os)
{
  if ($sysDescr == "")
  {
    if (snmp_get($device, "GAMATRONIC-MIB::psUnitManufacture.0", "-Oqv", "") == "Gamatronic")
    {
      $os = "gamatronicups";
    }
  }
}

?>