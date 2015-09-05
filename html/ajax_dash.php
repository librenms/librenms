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

require_once '../includes/defaults.inc.php';
set_debug($_REQUEST['debug']);
require_once '../config.php';
require_once '../includes/definitions.inc.php';
require_once 'includes/functions.inc.php';
require_once '../includes/functions.php';
require_once 'includes/authenticate.inc.php';

if (!$_SESSION['authenticated']) {
    echo 'unauthenticated';
    exit;
}

$type = mres($_POST['type']);

if ($type == 'placeholder') {
    $output = 'Please add a Widget to get started';
    $status = 'ok';
}
elseif (is_file('includes/common/'.$type.'.inc.php')) {

    $results_limit     = 10;
    $no_form           = true;
    $widget_id         = mres($_POST['id']);
    $widget_settings   = json_decode(dbFetchCell('select settings from users_widgets where user_widget_id = ?',array($widget_id)),true);
    $widget_dimensions = dbfetchRow('select size_x,size_y from users_widgets where user_widget_id = ?',array($widget_id));
    include 'includes/common/'.$type.'.inc.php';
    $output = implode('', $common_output);
    $status = 'ok';

}

$response = array(
                  'status' => $status,
                  'html' => $output,
                 );

echo _json_encode($response);
