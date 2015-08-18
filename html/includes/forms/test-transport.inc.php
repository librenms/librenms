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

if (is_admin() === false) {
    die('ERROR: You need to be admin');
}

$transport = mres($_POST['transport']);

$obj = array(
    'contacts' => $config['alert']['default_mail'],
    'title'    => 'Testing transport from ' . $config['project_name'],
    'msg'      => 'This is a test alert',
    'severity' => 'critical',
    'state'    => 'critical',
    'hostname' => 'testing',
    'name'     => 'Testing rule',
);

unset($obj);
$obj['contacts'] = 'test';
$obj['title'] = 'test';
$obj['msg'] = 'test';
$obj['severity'] = 'test';
$obj['state'] = 'test';
$obj['hostname'] = 'test';
$obj['name'] = 'test';

$status = 'error';

if (file_exists($config['install_dir']."/includes/alerts/transport.$transport.php")) {
    $opts = $config['alert']['transports'][$transport];
    if ($opts) {
        eval('$tmp = function($obj,$opts) { global $config; '.file_get_contents($config['install_dir'].'/includes/alerts/transport.'.$transport.'.php').' };');
        $tmp = $tmp($obj,$opts);
        if ($tmp) {
            $status = 'ok';
        }
    }
}

echo _json_encode(array('status' => $status));
