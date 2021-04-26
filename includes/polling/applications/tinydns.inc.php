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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/*
 * TinyDNS Statistics
 * @author Daniel Preussker <f0o@devilcode.org>
 * @copyright 2015 f0o, LibreNMS
 * @license GPL
 * @package LibreNMS
 * @subpackage Polling
 */

use LibreNMS\RRD\RrdDefinition;

$name = 'tinydns';
$app_id = $app['app_id'];
if (! empty($agent_data['app'][$name]) && $app_id > 0) {
    echo ' tinydns';
    $rrd_name = ['app', $name, $app_id];
    $rrd_def = RrdDefinition::make()
        ->addDataset('a', 'COUNTER', 0, 125000000000)
        ->addDataset('ns', 'COUNTER', 0, 125000000000)
        ->addDataset('cname', 'COUNTER', 0, 125000000000)
        ->addDataset('soa', 'COUNTER', 0, 125000000000)
        ->addDataset('ptr', 'COUNTER', 0, 125000000000)
        ->addDataset('hinfo', 'COUNTER', 0, 125000000000)
        ->addDataset('mx', 'COUNTER', 0, 125000000000)
        ->addDataset('txt', 'COUNTER', 0, 125000000000)
        ->addDataset('rp', 'COUNTER', 0, 125000000000)
        ->addDataset('sig', 'COUNTER', 0, 125000000000)
        ->addDataset('key', 'COUNTER', 0, 125000000000)
        ->addDataset('aaaa', 'COUNTER', 0, 125000000000)
        ->addDataset('axfr', 'COUNTER', 0, 125000000000)
        ->addDataset('any', 'COUNTER', 0, 125000000000)
        ->addDataset('total', 'COUNTER', 0, 125000000000)
        ->addDataset('other', 'COUNTER', 0, 125000000000)
        ->addDataset('notauth', 'COUNTER', 0, 125000000000)
        ->addDataset('notimpl', 'COUNTER', 0, 125000000000)
        ->addDataset('badclass', 'COUNTER', 0, 125000000000)
        ->addDataset('noquery', 'COUNTER', 0, 125000000000);

    [
        $a, $ns, $cname, $soa, $ptr, $hinfo, $mx, $txt, $rp, $sig, $key, $aaaa, $axfr, $any,
        $total, $other, $notauth, $notimpl, $badclass, $noquery
        ] = explode(':', $agent_data['app'][$name]);

    $fields = [
        'a'        => $a,
        'ns'       => $ns,
        'cname'    => $cname,
        'soa'      => $soa,
        'ptr'      => $ptr,
        'hinfo'    => $hinfo,
        'mx'       => $mx,
        'txt'      => $txt,
        'rp'       => $rp,
        'sig'      => $sig,
        'key'      => $key,
        'aaaa'     => $aaaa,
        'axfr'     => $axfr,
        'any'      => $any,
        'total'    => $total,
        'other'    => $other,
        'notauth'  => $notauth,
        'notimpl'  => $notimpl,
        'badclass' => $badclass,
        'noquery'  => $noquery,
    ];

    $tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
    data_update($device, 'app', $tags, $fields);
    update_application($app, $name, $fields);
}//end if
