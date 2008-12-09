<?php

if(!$os) {

  if(strstr($sysDescr, "Cisco Internetwork Operating System Software")) { $os = "IOS"; }
  if(strstr($sysDescr, "IOS (tm)")) { $os = "IOS"; }
  if(strstr($sysDescr, "Cisco IOS Software")) { $os = "IOS"; }
  if(strstr($sysDescr, "IOS-XE")) { $os = "IOS XE"; }

}

?>
