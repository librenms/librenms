<?php

if (!$os)
{
  if (strstr($sysDescr, "Dell Out-of-band SNMP Agent for Remote Access Controller")) { $os = "drac"; }
}

?>