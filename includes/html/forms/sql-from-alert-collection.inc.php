<?php
<<<<<<< HEAD
=======

>>>>>>> 8f8bf04ba52459b79a5000bfe1ae9e50c0d7be8e
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2018 Neil Lathwood
 * @author     Neil Lathwood <gh+n@laf.io>
 */

<<<<<<< HEAD
use LibreNMS\Alerting\QueryBuilderParser;
use LibreNMS\Config;
=======
use App\Facades\LibrenmsConfig;
>>>>>>> 8f8bf04ba52459b79a5000bfe1ae9e50c0d7be8e

header('Content-type: application/json');

if (! Auth::user()->hasGlobalAdmin()) {
    exit(json_encode([
        'status' => 'error',
        'message' => 'ERROR: You need to be admin',
    ]));
}

$template_id = $vars['template_id'];

if (is_numeric($template_id)) {
    $rules = get_rules_from_json();
    $rule = $rules[$template_id];
    $default_extra = [
<<<<<<< HEAD
        'mute' => Config::get('alert_rule.mute_alerts'),
        'count' => Config::get('alert_rule.max_alerts'),
        'delay' => 60 * Config::get('alert_rule.delay'),
        'invert' => Config::get('alert_rule.invert_rule_match'),
        'interval' => 60 * Config::get('alert_rule.interval'),
        'recovery' => Config::get('alert_rule.recovery_alerts'),
        'acknowledgement' => Config::get('alert_rule.acknowledgement_alerts'),
=======
        'mute' => LibrenmsConfig::get('alert_rule.mute_alerts'),
        'count' => LibrenmsConfig::get('alert_rule.max_alerts'),
        'delay' => 60 * LibrenmsConfig::get('alert_rule.delay'),
        'invert' => LibrenmsConfig::get('alert_rule.invert_rule_match'),
        'interval' => 60 * LibrenmsConfig::get('alert_rule.interval'),
        'recovery' => LibrenmsConfig::get('alert_rule.recovery_alerts'),
        'acknowledgement' => LibrenmsConfig::get('alert_rule.acknowledgement_alerts'),
>>>>>>> 8f8bf04ba52459b79a5000bfe1ae9e50c0d7be8e
    ];
    $output = [
        'status' => 'ok',
        'name' => $rule['name'],
<<<<<<< HEAD
        'notes' => $rule['notes'],
        'builder' => $rule['builder'] ?: QueryBuilderParser::fromOld($rule['rule'])->toArray(),
        'extra' => array_replace($default_extra, (array) $rule['extra']),
        'severity' => $rule['severity'] ?: Config::get('alert_rule.severity'),
        'invert_map' => Config::get('alert_rule.invert_map'),
=======
        'notes' => $rule['notes'] ?? null,
        'builder' => $rule['builder'] ?? [],
        'extra' => array_replace($default_extra, (array) ($rule['extra'] ?? [])),
        'severity' => $rule['severity'] ?? LibrenmsConfig::get('alert_rule.severity'),
        'invert_map' => LibrenmsConfig::get('alert_rule.invert_map'),
>>>>>>> 8f8bf04ba52459b79a5000bfe1ae9e50c0d7be8e
    ];
} else {
    $output = [
        'status' => 'error',
        'message' => 'Invalid template',
    ];
}

exit(json_encode($output));
