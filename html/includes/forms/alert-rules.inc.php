<?php
/**
 * alert-rules.inc.php
 *
 * LibreNMS alert-rules.inc.php for processor
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
 * @copyright  2018 Neil Lathwood
 * @author     Neil Lathwood <gh+n@laf.io>
 */

header('Content-type: application/json');

if (is_admin() === false) {
    die(
        json_encode(
            [
                'status' => 'error',
                'message' => 'ERROR: You need to be admin',
            ]
        )
    );
}

$query_builder = $_POST['query'];
$query         = GenSQLNew($query_builder);
$rule_id       = $_POST['rule_id'];
$count         = mres($_POST['count']);
$delay         = mres($_POST['delay']);
$interval      = mres($_POST['interval']);
$mute          = mres($_POST['mute']);
$invert        = mres($_POST['invert']);
$name          = mres($_POST['name']);
if ($_POST['proc'] != "") {
    $proc = $_POST['proc'];
} else {
    $proc = "";
}

if (validate_device_id($_POST['device_id']) || $_POST['device_id'] == '-1' || $_POST['device_id'] == '' || $_POST['device_id'][0] == ':') {
    $status = 'ok';
    $device_id = $_POST['device_id'] ?: '-1';
    if (!is_numeric($count)) {
        $count = '-1';
    }

    $delay_sec    = convert_delay($delay);
    $interval_sec = convert_delay($interval);

    if ($mute == 'on') {
        $mute = true;
    } else {
        $mute = false;
    }

    if ($invert == 'on') {
        $invert = true;
    } else {
        $invert = false;
    }

    $extra      = array(
        'mute'     => $mute,
        'count'    => $count,
        'delay'    => $delay_sec,
        'invert'   => $invert,
        'interval' => $interval_sec,
    );

    $extra_json = json_encode($extra);

    if (is_numeric($rule_id) && $rule_id > 0) {
        if (dbUpdate(
            array(
            'severity' => mres($_POST['severity']),
                'extra' => $extra_json,
                'name' => $name,
                'proc' => $proc,
                'query' => $query,
                'query_builder' => $query_builder
            ),
            'alert_rules',
            'id=?',
            array($rule_id)
        ) >= 0) {
            $message = "Edited Rule: <i>$name</i>";
        } else {
            $messsage = "Failed to edit Rule <i>$name</i>";
            $status   = 'error';
        }
    } else {
        if (is_array($_POST['maps'])) {
            $device_id = ':'.$device_id;
        }
        if (empty($name)) {
            $status = 'error';
            $message = 'No rule name provided';
        } elseif (empty($query_builder) || empty($query)) {
            $status  = 'error';
            $message = 'No rules provided';
        } else {
            if (dbInsert(array(
                'device_id' => $device_id,
                'severity' => mres($_POST['severity']),
                'extra' => $extra_json,
                'disabled' => 0,
                'name' => $name,
                'proc' => $proc,
                'query' => $query,
                'query_builder' => $query_builder
            ), 'alert_rules')) {
                $message = "Added Rule: <i>$name</i>";
                if (is_array($_POST['maps'])) {
                    foreach ($_POST['maps'] as $target) {
                        $_POST['rule'] = $name;
                        $_POST['target'] = $target;
                        $_POST['map_id'] = '';
                        include 'create-map-item.inc.php';
                        unset($ret, $target, $raw, $rule, $msg, $map_id);
                    }
                }
            } else {
                $message = 'Failed to add Rule: <i>' . $name . '</i>';
                $status = 'error';
            }
        }
    }//end if
} else {
    $message = 'Invalid device ID or not a global alert';
    $status  = 'error';
}//end if

die(json_encode(array(
    'status'       => $status,
    'message'      => $message,
)));
