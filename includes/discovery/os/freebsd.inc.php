<?php

if (!$os)
{
  if (strstr($sysDescr, "FreeBSD")) { $os = "freebsd"; }    /// It's FreeBSD!
  if (strstr($sysDescr, "Voswall")) { $os = "voswall"; }    /// Oh-No-It-Isn't!!
  if (strstr($sysDescr, "m0n0wall")) { $os = "monowall"; }  /// Ditto
}

?>