<?php
/*
 * Secureplatform.php
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

use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\RRD\RrdDefinition;

class Secureplatform extends \LibreNMS\OS implements OSPolling
{
    public function pollOS()
    {
        $connections = snmp_get($this->getDeviceArray(), 'fwNumConn.0', '-OQv', 'CHECKPOINT-MIB');

        if (is_numeric($connections)) {
            $rrd_def = RrdDefinition::make()->addDataset('NumConn', 'GAUGE', 0);

            $fields = [
                'NumConn' => $connections,
            ];

            $tags = compact('rrd_def');
            data_update($this->getDeviceArray(), 'secureplatform_sessions', $tags, $fields);
            $this->enableGraph('secureplatform_sessions');
        }
    }
}
