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

include_once("../includes/defaults.inc.php");
include_once("../config.php");
include_once("../includes/definitions.inc.php");
include_once("../includes/common.php");
include_once("../includes/console_colour.php");
include_once("../includes/dbFacile.php");
include_once("../includes/rewrites.php");
include_once("includes/functions.inc.php");
include_once("../includes/rrdtool.inc.php");
require 'includes/Slim/Slim.php';
\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();
require_once("includes/api_functions.inc.php");
$app->setName('api');

$app->group('/api', function() use ($app) {
  $app->group('/v1', function() use ($app) {
    $app->group('/devices', function() use ($app) {
      $app->get('/:hostname/ports/:ifname/:type', 'authToken', 'get_graph_by_port_hostname');//api/v1/devices/$hostname/ports/$ifName/$type
      $app->get('/:hostname/:type', 'authToken', 'get_graph_generic_by_hostname');//api/v1/devices/$hostname/$type
      $app->get('/:hostname/ports/:ifname', 'authToken', 'get_port_stats_by_port_hostname');//api/v1/devices/$hostname/ports/$ifName
    });
    $app->get('/devices', 'authToken', 'list_devices');//api/v1/devices
    $app->post('/devices', 'authToken', 'add_device');//api/v1/devices (json data needs to be passed)
  });
});

$app->run();

?>
