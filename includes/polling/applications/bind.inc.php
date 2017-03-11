<?php
/*
 * Copyright (C) 2015 Daniel Preussker <f0o@devilcode.org>
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
 */

/*
 * Bind9 Statistics
 * @author Daniel Preussker <f0o@devilcode.org>
 * @copyright 2015 f0o, LibreNMS
 * @license GPL
 * @package LibreNMS
 * @subpackage Polling
 */

use LibreNMS\RRD\RrdDefinition;

$name = 'bind';
$app_id = $app['app_id'];
if (!empty($agent_data['app'][$name]) && $app_id > 0) {
    echo ' bind ';
    $bind         = $agent_data['app'][$name];
    $bind_parsed  = array();
    $prefix       = '';
    update_application($app, $bind);
    foreach (explode("\n", $bind) as $line) {
        $pattern = '/^\+\+ ([^+]+) \+\+$/';
        preg_match($pattern, $line, $matches);
        if (!empty($matches)) {
            $prefix = str_replace(' ', '_', strtolower($matches[1]));
            $view   = $item = $cnt = '';
        }

        $pattern = '/^\[View: (\w+)(| .*)\]/';
        preg_match($pattern, $line, $matches);
        if (!empty($matches)) {
            if ($matches[1] == 'default') {
                continue;
            } else {
                $view = $matches[1];
            }
        }

        $pattern = '/^\[(.*)\]$/';
        preg_match($pattern, $line, $matches);
        if (!empty($matches)) {
            $prefix = $matches[1];
        }

        $pattern = '/^\s+(\d+) ([^\n]+)/';
        preg_match($pattern, $line, $matches);
        if (!empty($matches)) {
            $cnt  = str_replace(' ', '_', strtolower($matches[1]));
            $item = str_replace(' ', '_', strtolower($matches[2]));
            if (!empty($view)) {
                $bind_parsed[$prefix][$view][$item] = $cnt;
            } else {
                $bind_parsed[$prefix][$item] = $cnt;
            }
        }
    }//end foreach

    $rrd_name = array('app', $name, $app_id);
    $rrd_def = RrdDefinition::make()
        ->addDataset('any', 'COUNTER', 0, 125000000000)
        ->addDataset('a', 'COUNTER', 0, 125000000000)
        ->addDataset('aaaa', 'COUNTER', 0, 125000000000)
        ->addDataset('cname', 'COUNTER', 0, 125000000000)
        ->addDataset('mx', 'COUNTER', 0, 125000000000)
        ->addDataset('ns', 'COUNTER', 0, 125000000000)
        ->addDataset('ptr', 'COUNTER', 0, 125000000000)
        ->addDataset('soa', 'COUNTER', 0, 125000000000)
        ->addDataset('srv', 'COUNTER', 0, 125000000000)
        ->addDataset('spf', 'COUNTER', 0, 125000000000);

    $fields = array(
        'any'   => ((int)$bind_parsed['incoming_queries']['any']),
        'a'     => ((int)$bind_parsed['incoming_queries']['a']),
        'aaaa'  => ((int)$bind_parsed['incoming_queries']['aaaa']),
        'cname' => ((int)$bind_parsed['incoming_queries']['cname']),
        'mx'    => ((int)$bind_parsed['incoming_queries']['mx']),
        'ns'    => ((int)$bind_parsed['incoming_queries']['ns']),
        'ptr'   => ((int)$bind_parsed['incoming_queries']['ptr']),
        'soa'   => ((int)$bind_parsed['incoming_queries']['soa']),
        'srv'   => ((int)$bind_parsed['incoming_queries']['srv']),
        'spf'   => ((int)$bind_parsed['incoming_queries']['spf']),
    );

    $tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
    data_update($device, 'app', $tags, $fields);
}//end if
