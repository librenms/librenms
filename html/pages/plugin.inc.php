<?php

$link_array = array('page'    => 'plugin');

$pagetitle[] = "Plugin";

if ($vars['view'] == "admin")
{
  include_once('pages/plugin/admin.inc.php');
}
else
{
  $plugin = dbFetchRow("SELECT `plugin_name` FROM `plugins` WHERE `plugin_name` = '".$vars['p']."'");
  if(!empty($plugin))
  {
    require('plugins/'.$plugin['plugin_name'].'/'.$plugin['plugin_name'].'.inc.php');
  }
}

?>
