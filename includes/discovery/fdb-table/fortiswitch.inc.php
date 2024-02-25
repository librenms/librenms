<?php

/**
 * fortiswicht.inc.php
 *
 * FDP Table discovery file for fortiswitch
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @author     Oriol Lorenzo <oriol.lorenzo@urv.cat>
 */

require Config::get('install_dir') . 'config.php';
use App\Models\Vlan;
use LibreNMS\Config;

$device_id = $device['device_id'];
$device_ip = $device['hostname'];
$community = $device['community'];
$user = $config['fortiswitch']['usuari'];
$pass = $config['fortiswitch']['password'];
$timeout_seconds = 10;

echo 'Connecting: ' . $device_ip;
$url_login = 'https://' . $device_ip . '/logincheck';
$data = ['username' => $user, 'secretkey' => $pass];
$post_data = http_build_query($data);

$curl_connection = curl_init($url_login);

curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl_connection, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl_connection, CURLOPT_POST, true);
curl_setopt($curl_connection, CURLOPT_POSTFIELDS, $post_data);
curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl_connection, CURLOPT_HEADER, true);
$response = curl_exec($curl_connection);

preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $response, $matches);

if ($response === false) {
    echo 'Error cURL: ' . curl_error($ch) . "\n";
    echo 'Código de error: ' . curl_errno($ch) . "\n";
    return; // No hay conectividad, salimos
}

$url_get = 'https://' . $device_ip . '/api/v2/monitor/switch/mac-address';
$curl_connection = curl_init($url_get);
curl_setopt($curl_connection, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl_connection, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl_connection, CURLOPT_COOKIE, $matches[1][0]);
curl_setopt($curl_connection, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($curl_connection);

$response_data = json_decode($response, true);
if ($response_data === null) {
    echo 'Error al decodificar la respuesta JSON';
    exit;
}

$vlan_sw = [];
$interface = [];
$mac = [];
$port_id = [];

foreach ($response_data['results'] as $result) {
    if (strpos($result['interface'], 'port') === 0) { // only get data from ports, not static trunks nor fortilink trunks
        $mac[] = $result['mac'];
        $vlan_sw[] = $result['vlan'];
        $interface[] = $result['interface'];
    }
}

curl_close($curl_connection);

foreach ($interface as $int) {
    $ports_data = get_ports_mapped($device_id, $with_statistics = false);

    $ifName = $int;
    if (isset($ports_data['maps']['ifName'][$ifName])) {
        $port_id[] = $ports_data['maps']['ifName'][$ifName];
    } else {
        echo "No se encontró el port_id para la interfaz $ifName";
    }
}

for ($i = 0; $i < count($vlan_sw); $i++) {
    $vlan_name = 'vlan' . $vlan_sw[$i];
    $vlan = Vlan::where('device_id', $device_id)
            ->where('vlan_name', $vlan_name)
            ->first();

    if ($vlan) {
        $vlan_id = $vlan->vlan_id;
    } else {
        $vlan_id = null;
    }
    $vlan_id_final = $vlan['vlan_id'];
    $mac_address = implode(array_map('zeropad', explode(':', $mac[$i])));
    if (strlen($mac_address) != 12) {
        d_echo("MAC address padding failed for $mac\n");
        continue;
    }

    $insert[$vlan_id_final][$mac_address]['port_id'] = $port_id[$i];
}

if (empty($insert)) { //if there aren't any mac on any port I insert a 0, cause if $insert is null, then  bridge.inc.php is called
    $insert[0][0][0] = '0';
}
echo PHP_EOL;
