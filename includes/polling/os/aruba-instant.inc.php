<?php
/**
 * aruba-instant.inc.php
 *
 * LibreNMS os polling module for Aruba Instant
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
 * @copyright  2019 Timothy Willey
 * @author     Timothy Willey <developer@timothywilley.net>
 */
use LibreNMS\RRD\RrdDefinition;

// ArubaOS (MODEL: 225), Version 8.4.0.0-8.4.0.0
// ArubaOS (MODEL: 105), Version 6.4.4.8-4.2.4.12
$badchars                    = array( '(', ')', ',',);
list(,,$hardware,,$version,) = str_replace($badchars, '', explode(' ', $device['sysDescr']));

$client_count = 0;
// version 8.4.0.0 introduced client counts per radio as well as per ssid.
// prior versions require iteration over the list of clientss to get a count.
if (intval(explode('.', $version)[0]) >= 8 && intval(explode('.', $version)[1]) >= 4) {
    // ArubaOS >= 8.4.0.0
    // sum the values values of aiRadioClientNum or aiSSIDClientNum to count clients
    $client_data = snmpwalk_group($device, 'aiSSIDClientNum', 'AI-AP-MIB');
    d_echo('Debug Instant aiSSIDClientNum: '.PHP_EOL);
    d_echo(var_export($client_data, 1));
    foreach ($client_data as $key => $value) {
        $client_count += intval($value['aiSSIDClientNum']);
    }
} else {
    // ArubaOS =< 8.4.0.0
    // count number of aiClientMACAddress or aiClientWlanMACAddress
    $client_data = snmpwalk_group($device, 'aiClientMACAddress', 'AI-AP-MIB');
    d_echo('Debug Instant aiClientMACAddress: '.PHP_EOL);
    d_echo(var_export($client_data, 1));
    foreach ($client_data as $key => $value) {
        $client_count += 1;
    }
}

$ap_count = 0;
// Count the number of AP MAC addresses (could use AP IPs, Serial Number, etc too.)
$ap_data = snmpwalk_group($device, 'aiAPMACAddress', 'AI-AP-MIB');
d_echo('Debug Instant aiClientMACAddress: '.PHP_EOL);
d_echo(var_export($ap_data, 1));
foreach ($ap_data as $key => $value) {
    $ap_count += 1;
}

d_echo('Client Count: '.$client_count.PHP_EOL);
d_echo('AP Count:     '.$client_count.PHP_EOL);

$rrd_name = 'aruba-instant';
$rrd_def = RrdDefinition::make()
    ->addDataset('NUMAPS', 'GAUGE', 0, 12500000000)
    ->addDataset('NUMCLIENTS', 'GAUGE', 0, 12500000000);

$fields = array(
    'NUMAPS'     => $ap_count,
    'NUMCLIENTS' => $client_count,
);
$tags = compact('rrd_name', 'rrd_def');
data_update($device, 'aruba-instant', $tags, $fields);
