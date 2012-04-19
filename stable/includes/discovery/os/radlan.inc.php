<?php

if (!$os)
{
  if (strstr($sysDescr, "Neyland 24T")) { $os = "radlan"; }       /* Dell Powerconnect 5324 */
  if (strstr($sysDescr, "AT-8000")) { $os = "radlan"; }           /* Allied Telesis AT-8000 */
}

?>