<?php

/**
 * alert-transports.inc.php
 *
 * LibreNMS alert-transports.inc.php for processor
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2018 Vivia Nguyen-Tran
 * @author     Vivia Nguyen-Tran <vivia@ualberta.ca>
 */

use App\Models\AlertTransport;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use LibreNMS\Alert\KeyValueOptions;
use LibreNMS\Alert\Transport;

header('Content-type: application/json');

if (Gate::none(['alert-transport.create', 'alert-transport.update'])) {
    return print json_encode(['status' => 'error', 'message' => 'You need permission']);
}

$name = strip_tags((string) ($vars['name'] ?? ''));
$type = (string) ($vars['transport-type'] ?? '');
$class = Transport::getClass($type);
if ($name === '' || ! array_key_exists($type, Transport::list()) || ! method_exists($class, 'configTemplate')) {
    return print json_encode(['status' => 'error', 'message' => 'A transport name and supported type are required.']);
}

$template = $class::configTemplate();
try {
    foreach ($template['config'] as $field) {
        if ($field['type'] === 'keyvalue') {
            $rows = $vars[$field['name'] . '-rows'] ?? [];
            $vars[$field['name']] = KeyValueOptions::fromRows($rows === '' ? [] : $rows);
        }
    }
} catch (\InvalidArgumentException|\TypeError $e) {
    return print json_encode(['status' => 'error', 'message' => e($e->getMessage())]);
}

$validator = Validator::make($vars, $template['validation']);
if ($validator->fails()) {
    return print json_encode(['status' => 'error', 'message' => implode('<br>', array_map(e(...), $validator->errors()->all()))]);
}

$transportId = $vars['transport_id'] ?? null;
$transport = is_numeric($transportId) && $transportId > 0 ? AlertTransport::findOrFail($transportId) : new AlertTransport;
Gate::authorize($transport->exists ? 'update' : 'create', $transport->exists ? $transport : AlertTransport::class);

$config = $transport->transport_config ?? [];
foreach ($template['config'] as $field) {
    if (isset($field['name']) && $field['type'] !== 'hidden') {
        $config[$field['name']] = $vars[$field['name']] ?? null;
    }
}

$transport->transport_name = $name;
$transport->is_default = ($vars['is_default'] ?? '') === 'on';
$transport->transport_type = $type;
$transport->transport_config = $config;
$transport->save();

return print json_encode(['status' => 'ok', 'message' => 'Updated alert transports']);
