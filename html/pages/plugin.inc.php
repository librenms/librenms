<?php

$link_array = array('page' => 'plugin');

$pagetitle[] = 'Plugin';

if ($vars['view'] == 'admin') {
    include_once 'pages/plugin/admin.inc.php';
} else {
    $plugin = dbFetchRow("SELECT `plugin_name` FROM `plugins` WHERE `plugin_name` = '".$vars['p']."' AND `plugin_active`='1'");
    if (!empty($plugin)) {
        include 'plugins/'.$plugin['plugin_name'].'/'.$plugin['plugin_name'].'.inc.php';
    } else {
        print_error('This plugin is either disabled or not available.');
    }
}
