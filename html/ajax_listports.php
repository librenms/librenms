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
    foreach (dbFetch('SELECT * FROM ports WHERE device_id = ?', [$_GET['device_id']]) as $interface) {
        $interface = cleanPort($interface);
        $string = addslashes(html_entity_decode($interface['label'] . ' - ' . $interface['ifAlias']));
        echo "obj.options[obj.options.length] = new Option('" . $string . "','" . $interface['port_id'] . "');\n";
    }
}
