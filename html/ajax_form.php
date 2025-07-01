<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

use LibreNMS\Util\Debug;

$init_modules = ['web', 'auth', 'alerts'];
require realpath(__DIR__ . '/..') . '/includes/init.php';

if (! Auth::check()) {
    exit('Unauthorized');
}

Debug::set(isset($_REQUEST['debug']));

$ajax_form = match ($_POST['type'] ?? '') {
    'alert-details' => 'includes/html/forms/alert-details.inc.php',
    'alert-notes' => 'includes/html/forms/alert-notes.inc.php',
    'alert-rules' => 'includes/html/forms/alert-rules.inc.php',
    'alert-templates' => 'includes/html/forms/alert-templates.inc.php',
    'alert-transports' => 'includes/html/forms/alert-transports.inc.php',
    'application-update' => 'includes/html/forms/application-update.inc.php',
    'component' => 'includes/html/forms/component.inc.php',
    'convert-template' => 'includes/html/forms/convert-template.inc.php',
    'create-service' => 'includes/html/forms/create-service.inc.php',
    'customoid' => 'includes/html/forms/customoid.inc.php',
    'delete-alert-rule' => 'includes/html/forms/delete-alert-rule.inc.php',
    'delete-alert-template' => 'includes/html/forms/delete-alert-template.inc.php',
    'delete-alert-transport' => 'includes/html/forms/delete-alert-transport.inc.php',
    'delete-cluster-poller' => 'includes/html/forms/delete-cluster-poller.inc.php',
    'delete-customoid' => 'includes/html/forms/delete-customoid.inc.php',
    'delete-host-dependency' => 'includes/html/forms/delete-host-dependency.inc.php',
    'delete-poller' => 'includes/html/forms/delete-poller.inc.php',
    'delete-service' => 'includes/html/forms/delete-service.inc.php',
    'delete-transport-group' => 'includes/html/forms/delete-transport-group.inc.php',
    'get-host-dependencies' => 'includes/html/forms/get-host-dependencies.inc.php',
    'mempool-update' => 'includes/html/forms/mempool-update.inc.php',
    'notifications' => 'includes/html/forms/notifications.inc.php',
    'override-config' => 'includes/html/forms/override-config.inc.php',
    'parse-alert-rule' => 'includes/html/forms/parse-alert-rule.inc.php',
    'parse-alert-template' => 'includes/html/forms/parse-alert-template.inc.php',
    'parse-customoid' => 'includes/html/forms/parse-customoid.inc.php',
    'parse-poller-groups' => 'includes/html/forms/parse-poller-groups.inc.php',
    'parse-service' => 'includes/html/forms/parse-service.inc.php',
    'poller-groups' => 'includes/html/forms/poller-groups.inc.php',
    'processor-update' => 'includes/html/forms/processor-update.inc.php',
    'rediscover-device' => 'includes/html/forms/rediscover-device.inc.php',
    'refresh-oxidized-node' => 'includes/html/forms/refresh-oxidized-node.inc.php',
    'reload-oxidized-nodes-list' => 'includes/html/forms/reload-oxidized-nodes-list.inc.php',
    'reset-port-state' => 'includes/html/forms/reset-port-state.inc.php',
    'routing-update' => 'includes/html/forms/routing-update.inc.php',
    'save-host-dependency' => 'includes/html/forms/save-host-dependency.inc.php',
    'schedule-maintenance' => 'includes/html/forms/schedule-maintenance.inc.php',
    'search-oxidized-config' => 'includes/html/forms/search-oxidized-config.inc.php',
    'sensor-alert-reset' => 'includes/html/forms/sensor-alert-reset.inc.php',
    'sensor-alert-update' => 'includes/html/forms/sensor-alert-update.inc.php',
    'sensor-update' => 'includes/html/forms/sensor-update.inc.php',
    'show-alert-transport' => 'includes/html/forms/show-alert-transport.inc.php',
    'show-transport-group' => 'includes/html/forms/show-transport-group.inc.php',
    'sql-from-alert-collection' => 'includes/html/forms/sql-from-alert-collection.inc.php',
    'sql-from-alert-rules' => 'includes/html/forms/sql-from-alert-rules.inc.php',
    'storage-update' => 'includes/html/forms/storage-update.inc.php',
    'token-item-create' => 'includes/html/forms/token-item-create.inc.php',
    'token-item-disable' => 'includes/html/forms/token-item-disable.inc.php',
    'token-item-remove' => 'includes/html/forms/token-item-remove.inc.php',
    'transport-groups' => 'includes/html/forms/transport-groups.inc.php',
    'update-alert-rule' => 'includes/html/forms/update-alert-rule.inc.php',
    'update-ifalias' => 'includes/html/forms/update-ifalias.inc.php',
    'update-ifspeed' => 'includes/html/forms/update-ifspeed.inc.php',
    'update-port-notes' => 'includes/html/forms/update-port-notes.inc.php',
    'update-ports' => 'includes/html/forms/update-ports.inc.php',
    'wireless-sensor-alert-reset' => 'includes/html/forms/wireless-sensor-alert-reset.inc.php',
    'wireless-sensor-alert-update' => 'includes/html/forms/wireless-sensor-alert-update.inc.php',
    'wireless-sensor-update' => 'includes/html/forms/wireless-sensor-update.inc.php',
    default => null,
};

if (!$ajax_form || !file_exists($ajax_form)) {
    http_response_code(400);
    exit('Invalid form type');
}

include_once $ajax_form;
