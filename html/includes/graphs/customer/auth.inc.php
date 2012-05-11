<?php

## FIXME - wtfbbq

if ($_SESSION['userlevel'] >= "5" || $config['allow_unauth_graphs'])
{
  $id = mres($vars['id']);
  $title = generate_device_link($device);
  $auth = TRUE;
}

?>
