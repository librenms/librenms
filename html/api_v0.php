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
      $app->get('/:hostname/graphs', 'authToken', 'get_graphs')->name('get_graphs');//api/v0/devices/$hostname/graphs
      $app->get('/:hostname/ports', 'authToken', 'get_port_graphs')->name('get_port_graphs');//api/v0/devices/$hostname/ports
      $app->get('/:hostname/:type', 'authToken', 'get_graph_generic_by_hostname')->name('get_graph_generic_by_hostname');//api/v0/devices/$hostname/$type
      $app->get('/:hostname/ports/:ifname', 'authToken', 'get_port_stats_by_port_hostname')->name('get_port_stats_by_port_hostname');//api/v0/devices/$hostname/ports/$ifName
      $app->get('/:hostname/ports/:ifname/:type', 'authToken', 'get_graph_by_port_hostname')->name('get_graph_by_port_hostname');//api/v0/devices/$hostname/ports/$ifName/$type
    });
    $app->get('/devices', 'authToken', 'list_devices')->name('list_devices');//api/v0/devices
    $app->post('/devices', 'authToken', 'add_device')->name('add_device');//api/v0/devices (json data needs to be passed)
    $app->group('/portgroups', function() use ($app) {
        $app->get('/:group', 'authToken', 'get_graph_by_portgroup')->name('get_graph_by_portgroup');//api/v0/portgroups/$group
    });
    $app->group('/bills', function() use ($app) {
        $app->get('/:bill_id', 'authToken', 'list_bills')->name('get_bill');//api/v0/bills/$bill_id
    });
    $app->get('/bills', 'authToken', 'list_bills')->name('list_bills');//api/v0/bills

    // /api/v0/alerts
    $app->group('/alerts', function() use ($app) {
        $app->get('/:id', 'authToken', 'list_alerts')->name('get_alert');//api/v0/alerts
        $app->put('/:id', 'authToken', 'ack_alert')->name('ack_alert');//api/v0/alerts/$id (PUT)
    });
    $app->get('/alerts', 'authToken', 'list_alerts')->name('list_alerts');//api/v0/alerts

    // /api/v0/rules
    $app->group('/rules', function() use ($app) {
        $app->get('/:id', 'authToken', 'list_alert_rules')->name('get_alert_rule');//api/v0/rules/$id
        $app->delete('/:id', 'authToken', 'delete_rule')->name('delete_rule');//api/v0/rules/$id (DELETE)
    });
    $app->get('/rules', 'authToken', 'list_alert_rules')->name('list_alert_rules');//api/v0/rules
    $app->post('/rules', 'authToken', 'add_edit_rule')->name('add_rule');//api/v0/rules (json data needs to be passed)
    $app->put('/rules', 'authToken', 'add_edit_rule')->name('edit_rule');//api/v0/rules (json data needs to be passed)
  });
  $app->get('/v0', 'authToken', 'show_endpoints');//api/v0
});

$app->run();

?>
