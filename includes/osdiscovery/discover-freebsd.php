<?

if(!$os) {
  if(strstr($sysDescr, "FreeBSD")) { $os = "FreeBSD"; }  ## It's FreeBSD!
  if(strstr($sysDescr, "Voswall")) { unset($os); }       ## Oh-No-It-Isn't!!
  if(strstr($sysDescr, "m0n0wall")) { unset($os); }      ## Ditto
}
?>
