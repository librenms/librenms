<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk/fa>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if (isset($_REQUEST['debug']))
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
include_once("../includes/functions.php");
include_once("includes/authenticate.inc.php");

$current = $_POST['current'];
settype($current,"integer");
$rowCount = $_POST['rowCount'];
settype($rowCount,"integer");
if (isset($_POST['sort']) && is_array($_POST['sort'])) {
    foreach ($_POST['sort'] as $k=>$v) {
        $sort .= " $k $v";
    }
}
$searchPhrase = mres($_POST['searchPhrase']);
$id = mres($_POST['id']);
$response = array();

if (isset($id)) {
    if (file_exists("includes/table/$id.inc.php")) {
        require_once "includes/table/$id.inc.php";
    }
}

?>
