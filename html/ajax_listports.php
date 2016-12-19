<?php

/**
 * LibreNMS
 *
 *   This file is part of LibreNMS.
 *
 * @package    librenms
 * @subpackage ajax
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 */

$init_modules = array('web', 'auth');
require realpath(__DIR__ . '/..') . '/includes/init.php';

set_debug($_REQUEST['debug']);

if (!$_SESSION['authenticated']) {
    echo 'unauthenticated';
    exit;
}

if (is_numeric($_GET['device_id'])) {
    foreach (dbFetch('SELECT * FROM ports WHERE device_id = ?', array($_GET['device_id'])) as $interface) {
        $interface  = ifNameDescr($interface);
        $string = display($interface['label'].' - '.$interface['ifAlias']);
        echo "obj.options[obj.options.length] = new Option('".$string."','".$interface['port_id']."');\n";
    }
}
