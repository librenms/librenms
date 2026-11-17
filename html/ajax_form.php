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

// Validate the CSRF token for this legacy POST dispatcher.
$token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (! hash_equals(csrf_token(), (string) $token)) {
    http_response_code(419);
    exit(json_encode(['status' => 'error', 'message' => 'CSRF token mismatch']));
}

Debug::set(isset($_REQUEST['debug']));

$ajax_form = match ($_POST['type'] ?? '') {
    'alert-notes' => 'includes/html/forms/alert-notes.inc.php',
    'create-service' => 'includes/html/forms/create-service.inc.php',
    'override-config' => 'includes/html/forms/override-config.inc.php',
    default => null,
};

if (! $ajax_form || ! file_exists($ajax_form)) {
    http_response_code(400);
    exit('Invalid form type');
}

include_once $ajax_form;
