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

if (!empty($agent_data['app']['bind']) && $app['app_id'] > 0) {
    echo ' bind ';
    $bind         = $agent_data['app']['bind'];
    $rrd_filename = $config['rrd_dir'].'/'.$device['hostname'].'/app-bind-'.$app['app_id'].'.rrd';
    $bind_parsed  = array();
    $prefix       = '';
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
            }
            else {
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
            }
            else {
                $bind_parsed[$prefix][$item] = $cnt;
            }
        }
    }//end foreach

    if (!is_file($rrd_filename)) {
        rrdtool_create(
            $rrd_filename,
            '--step 300 
            DS:any:COUNTER:600:0:125000000000 
            DS:a:COUNTER:600:0:125000000000 
            DS:aaaa:COUNTER:600:0:125000000000 
            DS:cname:COUNTER:600:0:125000000000 
            DS:mx:COUNTER:600:0:125000000000 
            DS:ns:COUNTER:600:0:125000000000 
            DS:ptr:COUNTER:600:0:125000000000 
            DS:soa:COUNTER:600:0:125000000000 
            DS:srv:COUNTER:600:0:125000000000 
            DS:spf:COUNTER:600:0:125000000000 '.$config['rrd_rra']
        );
    }

    $fields = array(
                    'any'   => ((int) $bind_parsed['incoming_queries']['any']),
                    'a'     => ((int) $bind_parsed['incoming_queries']['a']),
                    'aaaa'  => ((int) $bind_parsed['incoming_queries']['aaaa']),
                    'cname' => ((int) $bind_parsed['incoming_queries']['cname']),
                    'mx'    => ((int) $bind_parsed['incoming_queries']['mx']),
                    'ns'    => ((int) $bind_parsed['incoming_queries']['ns']),
                    'ptr'   => ((int) $bind_parsed['incoming_queries']['ptr']),
                    'soa'   => ((int) $bind_parsed['incoming_queries']['soa']),
                    'srv'   => ((int) $bind_parsed['incoming_queries']['srv']),
                    'spf'   => ((int) $bind_parsed['incoming_queries']['spf']),
    );

    rrdtool_update($rrd_filename, $fields);

}//end if
