<?php
/**
 * alert-rules.inc.php
 *
 * LibreNMS alert-rules.inc.php
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

use LibreNMS\Alerting\QueryBuilderParser;
use LibreNMS\Authentication\Auth;

header('Content-type: application/json');

if (!Auth::user()->hasGlobalAdmin()) {
    die(json_encode([
        'status' => 'error',
        'message' => 'ERROR: You need to be admin',
    ]));
}

$status = 'ok';
$message = '';

$builder_json = $_POST['builder_json'];
$query        = QueryBuilderParser::fromJson($builder_json)->toSql();
$rule_id      = $_POST['rule_id'];
$count        = mres($_POST['count']);
$delay        = mres($_POST['delay']);
$interval     = mres($_POST['interval']);
$mute         = mres($_POST['mute']);
$invert       = mres($_POST['invert']);
$name         = mres($_POST['name']);
$proc         = mres($_POST['proc']);
$recovery     = ($vars['recovery']);
$severity     = mres($_POST['severity']);

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

$recovery = empty($recovery) ? $recovery = false : true;

$extra = array(
    'mute'     => $mute,
    'count'    => $count,
    'delay'    => $delay_sec,
    'invert'   => $invert,
    'interval' => $interval_sec,
    'recovery' => $recovery,
);

$extra_json = json_encode($extra);

if (is_numeric($rule_id) && $rule_id > 0) {
    if (dbUpdate(
        array(
        'severity' => $severity,
            'extra' => $extra_json,
            'name' => $name,
            'proc' => $proc,
            'query' => $query,
            'builder' => $builder_json
        ),
        'alert_rules',
        'id=?',
        array($rule_id)
    ) >= 0) {
        $message = "Edited Rule: <i>$name</i>";
    } else {
        $message = "Failed to edit Rule <i>$name</i>";
        $status   = 'error';
    }
} else {
    if (empty($name)) {
        $status = 'error';
        $message = 'No rule name provided';
    } elseif (empty($builder_json) || empty($query)) {
        $status  = 'error';
        $message = 'No rules provided';
    } else {
        $rule_id = dbInsert(array(
            'rule' => '',
            'severity' => $severity,
            'extra' => $extra_json,
            'disabled' => 0,
            'name' => $name,
            'proc' => $proc,
            'query' => $query,
            'builder' => $builder_json
        ), 'alert_rules');

        if ($rule_id) {
            $message = "Added Rule: <i>$name</i>";
        } else {
            if (dbFetchCell('SELECT 1 FROM alert_rules WHERE name=?', [$name])) {
                $message = "Rule named <i>$name</i> already exists";
            } else {
                $message = "Failed to add Rule: <i>$name</i>";
            }
            $status = 'error';
        }
    }
}//end if

// update maps
if (is_numeric($rule_id) && $rule_id > 0) {
    $devices = [];
    $groups = [];
    foreach ((array)$_POST['maps'] as $item) {
        if (starts_with($item, 'g')) {
            $groups[] = (int)substr($item, 1);
        } else {
            $devices[] = (int)$item;
        }
    }

    dbSyncRelationship('alert_device_map', 'rule_id', $rule_id, 'device_id', $devices);
    dbSyncRelationship('alert_group_map', 'rule_id', $rule_id, 'group_id', $groups);
}

die(json_encode([
    'status'       => $status,
    'message'      => $message,
]));
