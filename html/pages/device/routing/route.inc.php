<?php
/* Copyright (C) 2014 Nicolas Armando <nicearma@yahoo.com> and Mathieu Millet <htam-net@github.net>
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
* along with this program. If not, see <http://www.gnu.org/licenses/>. */

echo('<table border="0" cellspacing="0" cellpadding="9" width=100% class="sortable">');


if(!empty($vars['vrf-lite'])){
     $tableRoute=  dbFetchRows("select R.*, D.hostname, P.ifName, VR.vrf_name from route R LEFT OUTER JOIN devices D on R.device_id=D.device_id LEFT OUTER JOIN ports P on P.ifIndex=R.ipRouteIfIndex and P.ifIndex=R.ipRouteIfIndex AND P.device_id=R.device_id LEFT OUTER JOIN  vrf_lite_cisco VR on VR.device_id=R.device_id and VR.context_name=R.context_name where R.device_id=? and vrf_name=? group by D.hostname, VR.vrf_name, R.ipRouteDest",array($device['device_id'],$vars['vrf-lite']));
 
   }else{
        $tableRoute=  dbFetchRows("select R.*, D.hostname, P.ifName, VR.vrf_name from route R LEFT OUTER JOIN devices D on R.device_id=D.device_id LEFT OUTER JOIN ports P on P.ifIndex=R.ipRouteIfIndex and P.ifIndex=R.ipRouteIfIndex AND P.device_id=R.device_id LEFT OUTER JOIN  vrf_lite_cisco VR on VR.device_id=R.device_id and VR.context_name=R.context_name where R.device_id= ? group by D.hostname, VR.vrf_name, R.ipRouteDest",array($device['device_id']));

}
echo ('<tr><th>Hostname</th><th>VRF name</th><th>Ip Destination</th><th>Mask</th><th>Ip Next Hop</th><th>Ports Name</th><th>Metric</th><th>Discovered at</th><th>Type</th><th>Prototype</th><th>RouteIfIndex</th></tr>');
foreach ($tableRoute as $route) {
    echo ('<tr>'.
            '<td>'.$route['hostname'].'</td>'.
            '<td>'.$route['vrf_name'].'</td>'.
            '<td>'.$route['ipRouteDest'].'</td>'.
            '<td>'.$route['ipRouteMask'].'</td>'.
            '<td>'.$route['ipRouteNextHop'].'</td>'.
            '<td>'.$route['ifName'].'</td>'.
            '<td>'.$route['ipRouteMetric'].'</td>'.
            '<td>'.date('Y-m-d', $route['discoveredAt']).'</td>'.
            '<td>'.$route['ipRouteType'].'</td>'.
            '<td>'.$route['ipRouteProto'].'</td>'.
            '<td>'.$route['ipRouteIfIndex'].'</td>'.
          '</tr>' 
            );
    
    
}

 
unset($tableRoute);
 
 
 
 
echo("</table>");
