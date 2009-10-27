<?php

if($os == "Linux" || $os == "NetBSD" || $os == "FreeBSD" || $os == "DragonFly" || $os == "OpenBSD" || $os == "Windows" || $os == "m0n0wall" || $os == "Voswall" || $os == "pfSense" || $os == "DragonFly" || $os == "OpenBSD") {
  include("device_cpu_unix.inc.php");
}

?>
