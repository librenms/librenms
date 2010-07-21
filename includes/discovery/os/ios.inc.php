<?php

if(!$os) {

  if(strstr($sysDescr, "Cisco Internetwork Operating System Software")) { $os = "ios"; }
  else if(strstr($sysDescr, "IOS (tm)")) { $os = "ios"; }
  else if(strstr($sysDescr, "Cisco IOS Software")) { $os = "ios"; }
  
  if(strstr($sysDescr, "IOS-XE")) { $os = "iosxe"; }
  if(strstr($sysDescr, "IOS XR")) { $os = "iosxr"; }

}

?>
