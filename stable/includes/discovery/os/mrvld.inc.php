<?php

if (!$os)
{
  if (preg_match("/^LambdaDriver/", $sysDescr)) { $os = "mrvld"; }
}

?>
