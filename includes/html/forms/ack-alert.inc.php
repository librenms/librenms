<?php
/**
 * ack-alert.inc.php
 *
 * LibreNMS ack-alert.inc.php
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

use LibreNMS\Config;

header('Content-type: application/json');

$alert_id = $vars['alert_id'];
$state = $vars['state'];
$ack_msg = $vars['ack_msg'];
$until_clear = $vars['ack_until_clear'];

$status = 'error';

if (! is_numeric($alert_id)) {
    $message = 'No alert selected';
} elseif (! is_numeric($state)) {
    $message = 'No state passed';
} else {
    if ($state == 2) {
        $state = 1;
        $state_descr = 'UnAck';
        $open = 1;
    } elseif ($state >= 1) {
        $state = 2;
        $state_descr = 'Ack';
        $open = 1;
    }

    if ($until_clear === 'true') {
        $until_clear = true;
    } else {
        $until_clear = false;
    }

    $info = json_encode([
        'until_clear' => $until_clear,
    ]);

    $username = Auth::user()->username;
    $data = [
        'state' => $state,
        'open' => $open,
        'info' => $info,
    ];

    $note = dbFetchCell('SELECT note FROM alerts WHERE id=?', [$alert_id]);
    if (! empty($note)) {
        $note .= PHP_EOL;
    }
    $data['note'] = $note . date(Config::get('dateformat.long')) . " - $state_descr ($username) $ack_msg";

    if (dbUpdate($data, 'alerts', 'id=?', [$alert_id]) >= 0) {
        if (in_array($state, [2, 22])) {
            $alert_info = dbFetchRow('SELECT `alert_rules`.`name`,`alerts`.`device_id` FROM `alert_rules` LEFT JOIN `alerts` ON `alerts`.`rule_id` = `alert_rules`.`id` WHERE `alerts`.`id` = ?', [$alert_id]);
            log_event("$username acknowledged alert {$alert_info['name']} note: $ack_msg", $alert_info['device_id'], 'alert', 2, $alert_id);
        }
        $message = 'Alert acknowledged status changed.';
        $status = 'ok';
    } else {
        $message = 'Alert has not been acknowledged.';
    }
}//end if

exit(json_encode([
    'status'       => $status,
    'message'      => $message,
]));
