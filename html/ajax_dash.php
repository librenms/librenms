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

$init_modules = array('web', 'auth');
require realpath(__DIR__ . '/..') . '/includes/init.php';

set_debug($_REQUEST['debug']);

if (!$_SESSION['authenticated']) {
    echo 'unauthenticated';
    exit;
}

$type = mres($_POST['type']);

if ($type == 'placeholder') {
    $output = "<span style='text-align:left;'><br><h3>Click on the Edit Dashboard button (next to the list of dashboards) to add widgets</h3><br><h4><strong>Remember:</strong> You can only move & resize widgets when you're in <strong>Edit Mode</strong>.</h4><span>";
    $status = 'ok';
    $title = 'Placeholder';
} elseif (is_file('includes/common/'.$type.'.inc.php')) {
    $results_limit     = 10;
    $typeahead_limit   = $config['webui']['global_search_result_limit'];
    $no_form           = true;
    $title             = ucfirst(display($type));
    $unique_id         = str_replace(array("-","."), "_", uniqid($type, true));
    $widget_id         = mres($_POST['id']);
    $widget_settings   = json_decode(dbFetchCell('select settings from users_widgets where user_widget_id = ?', array($widget_id)), true);
    $widget_dimensions = $_POST['dimensions'];
    if (!empty($_POST['settings'])) {
        define('SHOW_SETTINGS', true);
    }
    include 'includes/common/'.$type.'.inc.php';
    $output = implode('', $common_output);
    $status = 'ok';
    $title  = display($widget_settings['title']) ?: $title;
}

$response = array(
                  'status' => $status,
                  'html' => $output,
                  'title' => $title,
                 );
header('Content-type: application/json');
echo _json_encode($response);
