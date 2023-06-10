<?php

/**
 * LibreNMS
 *
 *   This file is part of LibreNMS.
 *
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 */

use LibreNMS\Util\Debug;

$init_modules = ['web', 'auth'];
require realpath(__DIR__ . '/..') . '/includes/init.php';

if (! Auth::check()) {
    exit('Unauthorized');
}

Debug::set($_REQUEST['debug']);

if (is_numeric($_GET['device_id'])) {
    $interface_map = [];
    foreach (dbFetch('SELECT * FROM ports WHERE device_id = ? ORDER BY portName,ifAlias', [$_GET['device_id']]) as $interface) {
        $interface = cleanPort($interface);
        $interface_map[$interface['label']] = $interface;
    }
    $interface_names = array_keys($interface_map);
    sort($interface_names);
    foreach ($interface_names as $interface_name) {
        $interface = $interface_map[$interface_name];
        $string = addslashes(html_entity_decode($interface['label'] . ' - ' . $interface['ifAlias']));
        echo "obj.options[obj.options.length] = new Option('" . $string . "','" . $interface['port_id'] . "');\n";
    }
}
