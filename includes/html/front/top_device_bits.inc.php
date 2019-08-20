<?php
/*
 * LibreNMS front page top devices graph
 * - Find most utilised devices that have been polled in the last N minutes
 *
 * Author: Paul Gear
 * Copyright (c) 2013 Gear Consulting Pty Ltd <http://libertysys.com.au/>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$minutes = 15;
$seconds = ($minutes * 60);
$top = \LibreNMS\Config::get('front_page_settings.top.devices');
if (Auth::user()->hasGlobalRead()) {
    $query = "
        SELECT *, sum(p.ifInOctets_rate + p.ifOutOctets_rate) as total
        FROM ports as p, devices as d
        WHERE d.device_id = p.device_id
        AND unix_timestamp() - p.poll_time < $seconds
        AND ( p.ifInOctets_rate > 0
        OR p.ifOutOctets_rate > 0 )
        GROUP BY d.device_id
        ORDER BY total desc
        LIMIT $top
        ";
} else {
    $query   = "
        SELECT *, sum(p.ifInOctets_rate + p.ifOutOctets_rate) as total
        FROM ports as p, devices as d, `devices_perms` AS `P`
        WHERE `P`.`user_id` = ? AND `P`.`device_id` = `d`.`device_id` AND
        d.device_id = p.device_id
        AND unix_timestamp() - p.poll_time < $seconds
        AND ( p.ifInOctets_rate > 0
        OR p.ifOutOctets_rate > 0 )
        GROUP BY d.device_id
        ORDER BY total desc
        LIMIT $top
        ";
    $param[] = array(Auth::id());
}//end if

echo "<strong>Top $top devices (last $minutes minutes)</strong>\n";
echo "<table class='simple'>\n";
foreach (dbFetchRows($query, $param) as $result) {
    echo '<tr class=top10>'.'<td class=top10>'.generate_device_link($result, shorthost($result['hostname'])).'</td>'.'<td class=top10>'.generate_device_link($result, generate_minigraph_image($result, \LibreNMS\Config::get('time.day'), \LibreNMS\Config::get('time.now'), 'device_bits', 'no', 150, 21, '&', 'top10'), array(), 0, 0, 0).'</td>'."</tr>\n";
}

echo "</table>\n";
