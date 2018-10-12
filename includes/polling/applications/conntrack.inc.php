<?php
/**
 * conntrack.inc.php
 *
 * Connection tracking polling module
 * Capable of collecting stats from direct SNMP connection
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
 */

use LibreNMS\RRD\RrdDefinition;

global $config;
$data = '';
$name = 'conntrack';
$app_id = $app['app_id'];

echo ' ' . $name;

// nsExtendOutputFull."conntrack"
$oid = '.1.3.6.1.4.1.8072.1.3.2.3.1.2.9.99.111.110.110.116.114.97.99.107';
$data = snmp_get($device, $oid, '-Oqv');

print "the data is: ";
print $data;

if (!empty($data)) {
    $ds_list = array(
        'tcp_ss' => 'GAUGE',
        'tcp_sr' => 'GAUGE',
        'tcp_e' => 'GAUGE',
        'tcp_fw' => 'GAUGE',
        'tcp_cw' => 'GAUGE',
        'tcp_la' => 'GAUGE',
        'tcp_tw' => 'GAUGE',
        'tcp_c' => 'GAUGE',
        'tcp_ss2' => 'GAUGE',
        'tcp_n' => 'GAUGE',
        'tcp_unk' => 'GAUGE',
        'tcp_a' => 'GAUGE',
        'tcp_u' => 'GAUGE',
        'tcp_ha' => 'GAUGE',
        'tcp_tot' => 'GAUGE',
        'udp_a' => 'GAUGE',
        'udp_u' => 'GAUGE',
        'udp_ha' => 'GAUGE',
        'udp_tot' => 'GAUGE',
        'icmp_u' => 'GAUGE',
        'icmp_ha' => 'GAUGE',
        'icmp_tot' => 'GAUGE',
        'igmp_u' => 'GAUGE',
        'igmp_ha' => 'GAUGE',
        'igmp_tot' => 'GAUGE',
        'other_a' => 'GAUGE',
        'other_u' => 'GAUGE',
        'other_ha' => 'GAUGE',
        'other_tot' => 'GAUGE',
        'tot_a' => 'GAUGE',
        'tot_u' => 'GAUGE',
        'tot_ha' => 'GAUGE',
        'tot' => 'GAUGE',
    );

    //decode and flatten the data
    $stats = array();
    foreach (explode(" ", $data) as $stat) {
        list($name, $value) = explode(':', $stat);
        $stats[$name] = $value;
    }
    d_echo($stats);

    // only the stats we store in rrd
    $rrd_def = new RrdDefinition();
    $fields = array();
    foreach ($ds_list as $key => $type) {
        $rrd_def->addDataset($key, $type, 0);

        if (isset($stats[$key])) {
            $fields[$key] = $stats[$key];
        } else {
            $fields[$key] = 'U';
        }
    }

    $rrd_name = array('app', 'conntrack', $app_id);
    $tags = compact('name', 'app_id', 'rrd_name', 'rrd_def');
    data_update($device, 'app', $tags, $fields);
    update_application($app, $data, $fields);
}

unset($data, $stats, $rrd_def, $rrd_name, $rrd_keys, $tags, $fields);
