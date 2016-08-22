<?php

/**
 * LibreNMS
 *
 *   This file is part of LibreNMS.
 *
 * @package    librenms
 * @subpackage ajax
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 */

require_once '../includes/defaults.inc.php';
set_debug($_REQUEST['debug']);
require_once '../config.php';
require_once '../includes/definitions.inc.php';
require_once 'includes/functions.inc.php';
require_once '../includes/dbFacile.php';
require_once '../includes/common.php';

require_once '../includes/rewrites.php';
require_once 'includes/authenticate.inc.php';

if (!$_SESSION['authenticated']) {
    echo 'unauthenticated';
    exit;
}

if (is_numeric($_GET['device_id'])) {
    foreach (dbFetch('SELECT * FROM ports WHERE device_id = ?', array($_GET['device_id'])) as $interface) {
        $interface  = ifNameDescr($interface);
        $string = mres($interface['label'].' - '.$interface['ifAlias']);
        echo "obj.options[obj.options.length] = new Option('".$string."','".$interface['port_id']."');\n";
        // echo("obj.options[obj.options.length] = new Option('".$interface['ifDescr']." - ".$interface['ifAlias']."','".$interface['port_id']."');\n");
    }
}
