<?php

/**
 * LibreNMS
 *
 *   This file is part of LibreNMS
 *
 * @package    librenms
 * @subpackage cli
 * @author     The LibreNMS Contributors <librenms-project@google.groups.com>
 * @copyright  (C) 2006 - 2012 Adam Armstrong (as Observium)
 * @copyright  (C) 2013 LibreNMS Contributors
 *
 */

if ($_GET['debug'])
{
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 0);
  ini_set('log_errors', 0);
  ini_set('allow_url_fopen', 0);
  ini_set('error_reporting', E_ALL);
}

include_once("../includes/defaults.inc.php");
include_once("../config.php");
include_once("../includes/definitions.inc.php");
include_once("includes/functions.inc.php");
include_once("../includes/dbFacile.php");
include_once("../includes/common.php");

include_once("../includes/rewrites.php");
include_once("includes/authenticate.inc.php");

if (!$_SESSION['authenticated']) { echo("unauthenticated"); exit; }

if (is_numeric($_GET['device_id']))
{
  foreach (dbFetch("SELECT * FROM ports WHERE device_id = ?", array($_GET['device_id'])) as $interface)
  {
    $string = mres($interface['ifDescr']." - ".$interface['ifAlias']);
    echo("obj.options[obj.options.length] = new Option('".$string."','".$interface['port_id']."');\n");
    #echo("obj.options[obj.options.length] = new Option('".$interface['ifDescr']." - ".$interface['ifAlias']."','".$interface['port_id']."');\n");
  }
}

?>
