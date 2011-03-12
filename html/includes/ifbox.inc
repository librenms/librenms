<?php

$iftype = fixiftype($interface[ifType]);

echo("<p class=interface-header>$inf</p>");

if (isset($ifalias) && $ifalias != "") { echo('<span class="box-desc">'.$ifalias.'</span><br />'); }
if (isset($iftype) && $iftype != "")
{
  echo('<span class="box-desc">'.$iftype.'</span> ');
  if ($mac && $mac != "")
  {
    echo("<span class=box-desc>$mac</span><br />");
  }
  else
  {
    echo("<br />");
  }
}

if ($interface[ifType] != "softwareLoopback")
{
  if ($speed == '0') { $speed = "0bps"; }
  echo('<span class="box-desc">'.$speed);
  if ($interface[ifDuplex] != unknown) { echo(" / $interface[ifDuplex]-duplex"); }
  if ($interface[ifMtu] && $interface[ifMtu] != "") { echo(" / $interface[ifMtu]MTU</span>"); }
  echo('</span>');
}

echo('<span class="box-desc"><b>'.$status.'</b></span>');

?>
