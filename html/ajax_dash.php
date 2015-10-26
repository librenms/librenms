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
    $output = "<span style='text-align:left;'><br><h3>Click on Edit-Dashboard to add Widgets</h3><br><h4><strong>Remember:</strong> You can only move & rezise widgets when you're in <strong>Edit-Mode</strong>.</h4><span>";
    $status = 'ok';
    $title = 'Placeholder';
}
elseif (is_file('includes/common/'.$type.'.inc.php')) {

    $results_limit     = 10;
    $no_form           = true;
    $title             = ucfirst($type);
    $unique_id         = str_replace(array("-","."),"_",uniqid($type,true));
    $widget_id         = mres($_POST['id']);
    $widget_settings   = json_decode(dbFetchCell('select settings from users_widgets where user_widget_id = ?',array($widget_id)),true);
    $widget_dimensions = $_POST['dimensions'];
    if( !empty($_POST['settings']) ) {
        define('show_settings',true);
    }
    include 'includes/common/'.$type.'.inc.php';
    $output = implode('', $common_output);
    $status = 'ok';
    $title  = $widget_settings['title'] ?: $title;
}

$response = array(
                  'status' => $status,
                  'html' => $output,
                  'title' => $title,
                 );

echo _json_encode($response);
