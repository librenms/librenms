<?php

/**
 * alert-notes.inc.php
 *
 * LibreNMS alert-notes.inc.php
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
 * @copyright  2018 Neil Lathwood
 * @author     Neil Lathwood <gh+n@laf.io>
 */
use App\Models\Alert;
use Illuminate\Support\Facades\Gate;

header('Content-type: application/json');

$alert_id = $vars['alert_id'] ?? null;
$sub_type = $vars['sub_type'] ?? '';
$note = isset($vars['note']) ? strip_tags($vars['note']) : '';

if (! is_numeric($alert_id) || ! ($alert = Alert::find($alert_id))) {
    http_response_code(404);
    exit(json_encode([
        'status' => 'error',
        'message' => 'Invalid alert id',
        'note' => '',
    ]));
}

$ability = $sub_type === 'get_note' ? 'view' : 'update';
if (Gate::denies($ability, $alert)) {
    http_response_code(403);
    exit(json_encode([
        'status' => 'error',
        'message' => 'You are not authorised to access this alert',
        'note' => '',
    ]));
}

if ($sub_type === 'get_note') {
    $status = 'ok';
    $message = 'Alert note retrieved';
    $note = (string) $alert->note;
} else {
    $alert->note = $note;
    if ($alert->save()) {
        $status = 'ok';
        $message = 'Note updated';
    } else {
        $status = 'error';
        $message = 'Could not update note';
    }
}

exit(json_encode([
    'status' => $status,
    'message' => $message,
    'note' => $note,
]));
