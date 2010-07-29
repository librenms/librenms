<?php

if(!$os) {

  if(stristr($sysDescr, "VRP (R) Software")) { $os = "vrp"; }
  else if(stristr($sysDescr, "VRP Software Version")) { $os = "vrp"; }
  else if(stristr($sysDescr, "Software Version VRP")) { $os = "vrp"; }

}

?>
