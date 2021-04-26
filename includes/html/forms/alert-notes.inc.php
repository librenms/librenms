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
 * @copyright  2018 Neil Lathwood
 * @author     Neil Lathwood <gh+n@laf.io>
 */
header('Content-type: application/json');

$alert_id = $vars['alert_id'];
$sub_type = $vars['sub_type'];
$note = $vars['note'] ?: '';
$status = 'error';

if (is_numeric($alert_id)) {
    if ($sub_type === 'get_note') {
        $note = dbFetchCell('SELECT `note` FROM `alerts` WHERE `id` = ?', [$alert_id]);
        $message = 'Alert note retrieved';
        $status = 'ok';
    } else {
        if (dbUpdate(['note' => $note], 'alerts', '`id` = ?', [$alert_id])) {
            $status = 'ok';
            $message = 'Note updated';
        } else {
            $message = 'Could not update note';
        }
    }
} else {
    $message = 'Invalid alert id';
}
exit(json_encode([
    'status'  => $status,
    'message' => $message,
    'note'    => $note,
]));
