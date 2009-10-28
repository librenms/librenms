<?php

$os = strtolower($os);

if($os == "linux" || $os == "netbsd" || $os == "freebsd" || $os == "dragonfly" || $os == "openbsd" || $os == "windows" || $os == "m0n0wall" || $os == "Voswall" || $os == "pfsense" || $os == "dragonfly" || $os == "openbsd") {
  include("device_cpu_unix.inc.php");
}

?>
