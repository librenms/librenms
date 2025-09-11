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
 * @copyright  2020 Thomas Berberich
 * @author     Thomas Berberich <sourcehhdoctor@gmail.com>
 */

<<<<<<< HEAD
use LibreNMS\Alerting\QueryBuilderParser;
use LibreNMS\Config;
=======
use App\Facades\LibrenmsConfig;
use LibreNMS\Alerting\QueryBuilderParser;
>>>>>>> 8f8bf04ba52459b79a5000bfe1ae9e50c0d7be8e

header('Content-type: application/json');

if (! Auth::user()->hasGlobalAdmin()) {
    exit(json_encode([
        'status' => 'error',
        'message' => 'ERROR: You need to be admin',
    ]));
}

$rule_id = $vars['rule_id'];

if (is_numeric($rule_id)) {
    $rule = dbFetchRow('SELECT * FROM alert_rules where id=?', [$rule_id]);

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
        'name' => $rule['name'] . ' - Copy',
        'builder' => QueryBuilderParser::fromJson($rule['builder']),
        'extra' => array_replace($default_extra, (array) json_decode($rule['extra'])),
<<<<<<< HEAD
        'severity' => $rule['severity'] ?: Config::get('alert_rule.severity'),
=======
        'severity' => $rule['severity'] ?: LibrenmsConfig::get('alert_rule.severity'),
>>>>>>> 8f8bf04ba52459b79a5000bfe1ae9e50c0d7be8e
        'invert_map' => $rule['invert_map'],
    ];
} else {
    $output = [
        'status' => 'error',
        'message' => 'Invalid template',
    ];
}

exit(json_encode($output));
