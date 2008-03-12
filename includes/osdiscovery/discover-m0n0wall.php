<?php

if(!$os) {
  if(preg_match("/^m0n0wall/", $sysDescr)) { $os = "m0n0wall"; }
}

if(!$os) {
  if(preg_match("/^Voswall/", $sysDescr)) { $os = "Voswall"; }
}


?>
