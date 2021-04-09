<?php

/*
 * PortController.php
 *
 * -Description-
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2021 PipoCanaja
 * @author     PipoCanaja
 */

header('Content-type: text/plain');

if (! Auth::user()->hasGlobalAdmin()) {
    exit('ERROR: You need to be admin');
}

$service_id = $vars['service_id'];

if (! is_numeric($service_id)) {
    echo 'ERROR: No service selected';
    exit;
} else {
    if ($_POST['state'] == 'true') {
        $state = 0;
    } elseif ($_POST['state'] == 'false') {
        $state = 1;
    } else {
        $state = 1;
    }

    $update = ['service_disabled' => $state];
    if (is_numeric(edit_service($update, $service_id))) {
        echo 'Service has been updated.';
        exit;
    } else {
        echo 'ERROR: Service has not been updated.';
        exit;
    }
}
