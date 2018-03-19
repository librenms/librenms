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

use LibreNMS\Alerting\QueryBuilderParser;

header('Content-type: application/json');

if (is_admin() === false) {
    die(json_encode([
        'status' => 'error',
        'message' => 'ERROR: You need to be admin',
    ]));
}

$template_id = $vars['template_id'];

if (is_numeric($template_id)) {
    $rules = get_rules_from_json();

    $output = [
        'status' => 'ok',
        'name' => $rules[$template_id]['name'],
        'builder' => QueryBuilderParser::fromOld($rules[$template_id]['rule'])->toArray(),
        'extra' => $rules[$template_id]['extra']
    ];
} else {
    $output = [
        'status' => 'error',
        'message' => 'Invalid template'
    ];
}

die(json_encode($output));
