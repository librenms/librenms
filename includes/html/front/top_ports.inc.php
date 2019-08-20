<?php
/*
 * LibreNMS front page top ports graph
 * - Find most utilised ports that have been polled in the last N minutes
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
$top = \LibreNMS\Config::get('front_page_settings.top.ports');
if (Auth::user()->hasGlobalRead()) {
    $query = "
        SELECT *, p.ifInOctets_rate + p.ifOutOctets_rate as total
        FROM ports as p, devices as d
        WHERE d.device_id = p.device_id
        AND unix_timestamp() - p.poll_time < $seconds
        AND ( p.ifInOctets_rate > 0
        OR p.ifOutOctets_rate > 0 )
        ORDER BY total desc
        LIMIT $top
        ";
} else {
    $query   = "
        SELECT *, I.ifInOctets_rate + I.ifOutOctets_rate as total
        FROM ports as I, devices as d,
        `devices_perms` AS `P`, `ports_perms` AS `PP`
        WHERE ((`P`.`user_id` = ? AND `P`.`device_id` = `d`.`device_id`) OR (`PP`.`user_id` = ? AND `PP`.`port_id` = `I`.`port_id` AND `I`.`device_id` = `d`.`device_id`)) AND
        d.device_id = I.device_id
        AND unix_timestamp() - I.poll_time < $seconds
        AND ( I.ifInOctets_rate > 0
        OR I.ifOutOctets_rate > 0 )
        ORDER BY total desc
        LIMIT $top
        ";
    $param[] = array(
        Auth::id(),
        Auth::id(),
    );
}//end if

echo "<strong>Top $top ports (last $minutes minutes)</strong>\n";
echo "<table class='simple'>\n";
foreach (dbFetchRows($query, $param) as $result) {
    $result = cleanPort($result);
    echo '<tr class=top10>'.'<td class=top10>'.generate_device_link($result, shorthost($result['hostname'])).'</td>'.'<td class=top10>'.generate_port_link($result).'</td>'.'<td class=top10>'.generate_port_link($result, generate_port_thumbnail($result)).'</td>'."</tr>\n";
}

echo "</table>\n";
