<?php
/*
 * Panos.php
 *
 * -Description-
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
 * @package    LibreNMS
 * @link       https://www.librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use Illuminate\Support\Str;
use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\RRD\RrdDefinition;

class Panos extends \LibreNMS\OS implements OSPolling
{
    private $validNetBufferMemory = [
        'Packet Descriptors',
        'Packet Buffers',
    ];

    public function pollOS()
    {
        $data = snmp_get_multi($this->getDeviceArray(), [
            'panSessionActive.0',
            'panSessionActiveTcp.0',
            'panSessionActiveUdp.0',
            'panSessionActiveICMP.0',
            'panSessionActiveSslProxy.0',
            'panSessionSslProxyUtilization.0',
            'panGPGWUtilizationActiveTunnels.0',
        ], '-OQUs', 'PAN-COMMON-MIB');

        if (is_numeric($data[0]['panSessionActive'])) {
            $rrd_def = RrdDefinition::make()->addDataset('sessions', 'GAUGE', 0, 3000000);

            $fields = [
                'sessions' => $data[0]['panSessionActive'],
            ];

            $tags = compact('rrd_def');
            data_update($this->getDeviceArray(), 'panos-sessions', $tags, $fields);

            $this->enableGraph('panos_sessions');
        }

        if (is_numeric($data[0]['panSessionActiveTcp'])) {
            $rrd_def = RrdDefinition::make()->addDataset('sessions_tcp', 'GAUGE', 0, 3000000);

            $fields = [
                'sessions_tcp' => $data[0]['panSessionActiveTcp'],
            ];

            $tags = compact('rrd_def');
            data_update($this->getDeviceArray(), 'panos-sessions-tcp', $tags, $fields);

            $this->enableGraph('panos_sessions_tcp');
        }

        if (is_numeric($data[0]['panSessionActiveUdp'])) {
            $rrd_def = RrdDefinition::make()->addDataset('sessions_udp', 'GAUGE', 0, 3000000);

            $fields = [
                'sessions_udp' => $data[0]['panSessionActiveUdp'],
            ];

            $tags = compact('rrd_def');
            data_update($this->getDeviceArray(), 'panos-sessions-udp', $tags, $fields);

            $this->enableGraph('panos_sessions_udp');
        }

        if (is_numeric($data[0]['panSessionActiveICMP'])) {
            $rrd_def = RrdDefinition::make()->addDataset('sessions_icmp', 'GAUGE', 0, 3000000);

            $fields = [
                'sessions_icmp' => $data[0]['panSessionActiveICMP'],
            ];

            $tags = compact('rrd_def');
            data_update($this->getDeviceArray(), 'panos-sessions-icmp', $tags, $fields);

            $this->enableGraph('panos_sessions_icmp');
        }

        if (is_numeric($data[0]['panSessionActiveSslProxy'])) {
            $rrd_def = RrdDefinition::make()->addDataset('sessions_ssl', 'GAUGE', 0, 3000000);

            $fields = [
                'sessions_ssl' => $data[0]['panSessionActiveSslProxy'],
            ];

            $tags = compact('rrd_def');
            data_update($this->getDeviceArray(), 'panos-sessions-ssl', $tags, $fields);

            $this->enableGraph('panos_sessions_ssl');
        }

        if (is_numeric($data[0]['panSessionSslProxyUtilization'])) {
            $rrd_def = RrdDefinition::make()->addDataset('sessions_sslutil', 'GAUGE', 0, 3000000);

            $fields = [
                'sessions_sslutil' => $data[0]['panSessionSslProxyUtilization'],
            ];

            $tags = compact('rrd_def');
            data_update($this->getDeviceArray(), 'panos-sessions-sslutil', $tags, $fields);

            $this->enableGraph('panos_sessions_sslutil');
        }

        if (is_numeric($data[0]['panGPGWUtilizationActiveTunnels'])) {
            $rrd_def = RrdDefinition::make()->addDataset('activetunnels', 'GAUGE', 0, 3000000);

            $fields = [
                'activetunnels' => $data[0]['panGPGWUtilizationActiveTunnels'],
            ];

            $tags = compact('rrd_def');
            data_update($this->getDeviceArray(), 'panos-activetunnels', $tags, $fields);

            $this->enableGraph('panos_activetunnels');
        }
    }

    protected function memValid($storage)
    {
        return $storage['hrStorageType'] == 'hrStorageOther'
            && Str::contains($storage['hrStorageDescr'], $this->validNetBufferMemory)
            || parent::memValid($storage);
    }
}
