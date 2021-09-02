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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2018 Neil Lathwood
 * @author     Neil Lathwood <gh+n@laf.io>
 */

use LibreNMS\Alerting\QueryBuilderParser;
use LibreNMS\Config;

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
        'mute' => Config::get('alert_rule.mute_alerts'),
        'count' => Config::get('alert_rule.max_alerts'),
        'delay' => 60 * Config::get('alert_rule.delay'),
        'invert' => Config::get('alert_rule.invert_rule_match'),
        'interval' => 60 * Config::get('alert_rule.interval'),
        'recovery' => Config::get('alert_rule.recovery_alerts'),
    ];
    $output = [
        'status' => 'ok',
        'name' => $rule['name'],
        'builder' => $rule['builder'] ?: QueryBuilderParser::fromOld($rule['rule'])->toArray(),
        'extra' => array_replace($default_extra, (array) $rule['extra']),
        'severity' => $rule['severity'] ?: Config::get('alert_rule.severity'),
        'invert_map' => Config::get('alert_rule.invert_map'),
    ];
} else {
    $output = [
        'status' => 'error',
        'message' => 'Invalid template',
    ];
}

exit(json_encode($output));
