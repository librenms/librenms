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
  $app->group('/v0', function() use ($app) {
    $app->get('/bgp', 'authToken', 'list_bgp')->name('list_bgp');//api/v0/bgp
    $app->group('/devices', function() use ($app) {
      $app->delete('/:hostname', 'authToken', 'del_device')->name('del_device');//api/v0/devices/$hostname
      $app->get('/:hostname', 'authToken', 'get_device')->name('get_device');//api/v0/devices/$hostname
      $app->get('/:hostname/vlans', 'authToken', 'get_vlans')->name('get_vlans');//api/v0/devices/$hostname/vlans
      $app->get('/:hostname/:type', 'authToken', 'get_graph_generic_by_hostname')->name('get_graph_generic_by_hostname');//api/v0/devices/$hostname/$type
      $app->get('/:hostname/ports/:ifname', 'authToken', 'get_port_stats_by_port_hostname')->name('get_port_stats_by_port_hostname');//api/v0/devices/$hostname/ports/$ifName
      $app->get('/:hostname/ports/:ifname/:type', 'authToken', 'get_graph_by_port_hostname')->name('get_graph_by_port_hostname');//api/v0/devices/$hostname/ports/$ifName/$type
    });
    $app->get('/devices', 'authToken', 'list_devices')->name('list_devices');//api/v0/devices
    $app->post('/devices', 'authToken', 'add_device')->name('add_device');//api/v0/devices (json data needs to be passed)
    $app->group('/portgroups', function() use ($app) {
        $app->get('/:group', 'authToken', 'get_graph_by_portgroup')->name('get_graph_by_portgroup');//api/v0/portgroups/$group
    });
  });
  $app->get('/v0', 'authToken', 'show_endpoints');//api/v0
});

$app->run();

?>
