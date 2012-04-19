<?php

preg_match('/Blade Network Technologies (.*)$/', $poll_device['sysDescr'], $store);

if (isset($store[1]))
{
  $hardware = $store[1];
}

?>