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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2018 Neil Lathwood
 * @author     Neil Lathwood <gh+n@laf.io>
 */

use Illuminate\Support\Str;
use LibreNMS\Alerting\QueryBuilderParser;

header('Content-type: application/json');

if (! Auth::user()->hasGlobalAdmin()) {
    exit(json_encode([
        'status' => 'error',
        'message' => 'ERROR: You need to be admin',
    ]));
}

$status = 'ok';
$message = '';

$builder_json = $vars['builder_json'];
$override_query = $vars['override_query'];

$options = [
    'override_query' => $override_query,
];

if ($override_query === 'on') {
    $query = $vars['adv_query'];
} else {
    $query = QueryBuilderParser::fromJson($builder_json)->toSql();
}
$rule_id = $_POST['rule_id'];
$count = $_POST['count'];
$delay = $_POST['delay'];
$interval = $_POST['interval'];
$mute = isset($_POST['mute']) ? $_POST['mute'] : null;
$invert = isset($_POST['invert']) ? $_POST['invert'] : null;
$name = $_POST['name'];
$proc = $_POST['proc'];
$recovery = ($vars['recovery']);
$invert_map = isset($_POST['invert_map']) ? $_POST['invert_map'] : null;
$severity = $_POST['severity'];

if (! is_numeric($count)) {
    $count = '-1';
}

$delay_sec = convert_delay($delay);
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

if ($invert_map == 'on') {
    $invert_map = true;
} else {
    $invert_map = false;
}

$extra = [
    'mute'     => $mute,
    'count'    => $count,
    'delay'    => $delay_sec,
    'invert'   => $invert,
    'interval' => $interval_sec,
    'recovery' => $recovery,
    'options'  => $options,
];

$extra_json = json_encode($extra);

if (! is_array($vars['maps']) && $invert_map) {
    exit(json_encode([
        'status' => 'error',
        'message' => 'Invert map is on but no selection in devices, groups and locations match list<br />',
    ]));
}

if (is_numeric($rule_id) && $rule_id > 0) {
    if (dbUpdate(
        [
            'severity' => $severity,
            'extra' => $extra_json,
            'name' => $name,
            'proc' => $proc,
            'query' => $query,
            'builder' => $builder_json,
            'invert_map' => $invert_map,
        ],
        'alert_rules',
        'id=?',
        [$rule_id]
    ) >= 0) {
        $message = "Edited Rule: <i>$name</i>";
    } else {
        $message = "Failed to edit Rule <i>$name</i>";
        $status = 'error';
    }
} else {
    if (empty($name)) {
        $status = 'error';
        $message = 'No rule name provided';
    } elseif (empty($builder_json) || empty($query)) {
        $status = 'error';
        $message = 'No rules provided';
    } else {
        $rule_id = dbInsert([
            'rule' => '',
            'severity' => $severity,
            'extra' => $extra_json,
            'disabled' => 0,
            'name' => $name,
            'proc' => $proc,
            'query' => $query,
            'builder' => $builder_json,
            'invert_map' => $invert_map,
        ], 'alert_rules');

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
    $locations = [];
    foreach ((array) $vars['maps'] as $item) {
        if (Str::startsWith($item, 'l')) {
            $locations[] = (int) substr($item, 1);
        } elseif (Str::startsWith($item, 'g')) {
            $groups[] = (int) substr($item, 1);
        } else {
            $devices[] = (int) $item;
        }
    }

    dbSyncRelationship('alert_device_map', 'rule_id', $rule_id, 'device_id', $devices);
    dbSyncRelationship('alert_group_map', 'rule_id', $rule_id, 'group_id', $groups);
    dbSyncRelationship('alert_location_map', 'rule_id', $rule_id, 'location_id', $locations);

    //Update transport groups and transports - can't use dbSyncRelationship
    $transports = [];
    $groups = [];
    foreach ((array) $vars['transports'] as $item) {
        if (Str::startsWith($item, 'g')) {
            $groups[] = (int) substr($item, 1);
        } else {
            $transports[] = (int) $item;
        }
    }

    // Fetch transport/group mappings already in db
    $sql = "SELECT `transport_or_group_id` FROM `alert_transport_map` WHERE `target_type`='single' AND `rule_id`=?";
    $db_transports = dbFetchColumn($sql, [$rule_id]);
    $sql = "SELECT `transport_or_group_id` FROM `alert_transport_map` WHERE `target_type`='group' AND `rule_id`=?";
    $db_groups = dbFetchColumn($sql, [$rule_id]);

    // Compare arrays to get add and removed transports/groups
    $t_add = array_diff($transports, $db_transports);
    $t_del = array_diff($db_transports, $transports);
    $g_add = array_diff($groups, $db_groups);
    $g_del = array_diff($db_groups, $groups);

    // Insert any new mappings
    $insert = [];
    foreach ($t_add as $transport_id) {
        $insert[] = [
            'transport_or_group_id' => $transport_id,
            'target_type' => 'single',
            'rule_id' => $rule_id,
        ];
    }
    foreach ($g_add as $group_id) {
        $insert[] = [
            'transport_or_group_id' => $group_id,
            'target_type' => 'group',
            'rule_id' => $rule_id,
        ];
    }
    if (! empty($insert)) {
        $res = dbBulkInsert($insert, 'alert_transport_map');
    }
    // Remove old mappings
    if (! empty($t_del)) {
        $db_t_values = array_merge([$rule_id], $t_del);
        dbDelete('alert_transport_map', 'target_type="single" AND rule_id=? AND transport_or_group_id IN ' . dbGenPlaceholders(count($t_del)), $db_t_values);
    }
    if (! empty($g_del)) {
        $db_g_values = array_merge([$rule_id], $g_del);
        dbDelete('alert_transport_map', 'target_type="group" AND rule_id=? AND transport_or_group_id IN ' . dbGenPlaceholders(count($g_del)), $db_g_values);
    }
}

exit(json_encode([
    'status'       => $status,
    'message'      => $message,
]));
