<?php

if (preg_match('/^Meraki ([A-Z\-_0-9]+) (.*)/', $poll_device['sysDescr'], $regexp_result))
{
  $hardware = $regexp_result[1];
  $platform = $regexp_result[2];
}

// EOF
