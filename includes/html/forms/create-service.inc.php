<?php

/*
 * create-service.inc.php
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
 * @copyright  2016 Aaron Daniels
 * @author     Aaron Daniels <aaron@daniels.id.au>
 */

if (! Auth::user()->hasGlobalAdmin()) {
    exit('ERROR: You need to be admin');
}

foreach (['desc', 'ip', 'ignore', 'disabled', 'param', 'name', 'template_id'] as $varname) {
    if (isset($vars[$varname])) {
        $update['service_' . $varname] = $vars[$varname];
        $$varname = $vars[$varname];
    }
}
foreach (['stype', 'device_id', 'service_id'] as $varname) {
    if (isset($vars[$varname])) {
        $$varname = $vars[$varname];
    }
}

if (is_numeric($service_id) && $service_id > 0) {
    // Need to edit.
    if (is_numeric(edit_service($update, $service_id))) {
        $status = ['status' =>0, 'message' => 'Modified Service: <i>' . $service_id . ': ' . $stype . '</i>'];
    } else {
        $status = ['status' =>1, 'message' => 'ERROR: Failed to modify service: <i>' . $service_id . '</i>'];
    }
} else {
    // Need to add.
    $service_id = add_service($device_id, $stype, $desc, $ip, $param, $ignore, $disabled, 0, $name);
    if ($service_id == false) {
        $status = ['status' =>1, 'message' => 'ERROR: Failed to add Service: <i>' . $stype . '</i>'];
    } else {
        $status = ['status' =>0, 'message' => 'Added Service: <i>' . $service_id . ': ' . $stype . '</i>'];
    }
}
header('Content-Type: application/json');
echo json_encode($status, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
