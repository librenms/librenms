<?php

preg_match('/Blade Network Technologies (.*)$/', $sysDescr, $store);

if (isset($store[1]))
{
  $hardware = $store[1];
}

?>