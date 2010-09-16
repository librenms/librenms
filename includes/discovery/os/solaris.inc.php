<?php

if(!$os) {
  if(preg_match("/^SunOS/", $sysDescr)) 
  { 
    $os = "solaris"; 
    list(,,$version) = explode (" ", $sysDescr);
    if($version > "5.10") { $os = "opensolaris"; }
    if($version > "5.10") { 
      if(preg_match("/oi_/", $sysDescr)) { $os = "openindiana"; }
    }
  }
}

?>
