<?php

if (!$os)
{
  if(preg_match('/HP [a-zA-Z0-9-]+ Switch Software Version/',$sysDescr)) { $os = "hp"; }
}

?>
