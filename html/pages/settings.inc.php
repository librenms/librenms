<?php

if ($_SESSION['userlevel'] == '10')
{
  echo("<pre>");
  print_r($config);
  echo("</pre>");
} else {
  include("includes/error-no-perm.inc.php");
}

?>
