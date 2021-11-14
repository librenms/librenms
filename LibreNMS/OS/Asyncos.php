<?php
/**
 * Asyncos.php
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
 * @link       https://www.librenms.org
 *
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\OS;
use LibreNMS\RRD\RrdDefinition;

class Asyncos extends OS implements OSPolling
{
    public function pollOS(): void
    {
        // Get stats only if device is web proxy
        if ($this->getDevice()->sysObjectID == '.1.3.6.1.4.1.15497.1.2') {
            $connections = \SnmpQuery::get('TCP-MIB::tcpCurrEstab.0')->value();

            if (is_numeric($connections)) {
                data_update($this->getDeviceArray(), 'asyncos_conns', [
                    'rrd_def' => RrdDefinition::make()->addDataset('connections', 'GAUGE', 0, 50000),
                ], [
                    'connections' => $connections,
                ]);

                $this->enableGraph('asyncos_conns');
            }
        }
    }
}
