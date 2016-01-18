/* Copyright (C) 2015 Daniel Preussker <f0o@devilcode.org>
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>. */

/**
 * VictorOps Generic-API Transport - Based on PagerDuty transport
 * @author f0o <f0o@devilcode.org>
 * @author laf <neil@librenms.org>
 * @copyright 2015 f0o, laf, LibreNMS
 * @license GPL
 * @package LibreNMS
 * @subpackage Alerts
 */

$url = $opts['url'];

$protocol = array(
    'entity_id' => ($obj['id'] ? $obj['id'] : $obj['uid']),
    'state_start_time' => strtotime($obj['timestamp']),
    'monitoring_tool' => 'librenms',
);
if( $obj['state'] == 0 ) {
    $protocol['message_type'] = 'recovery';
}
elseif( $obj['state'] == 2 ) {
    $protocol['message_type'] = 'acknowledgement';
}
elseif ($obj['state'] == 1) {
    $protocol['message_type'] = 'critical';
}

foreach( $obj['faults'] as $fault=>$data ) {
    $protocol['state_message'] .= $data['string'];
}

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $url );
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type'=> 'application/json'));
curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($protocol));
$ret = curl_exec($curl);
$code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
if( $code != 200 ) {
    var_dump("VictorOps returned Error, retry later"); //FIXME: propper debuging
    return false;
}
return true;
