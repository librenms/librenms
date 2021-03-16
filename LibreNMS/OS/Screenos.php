<?php
/*
 * Screenos.php
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

class Screenos extends \LibreNMS\OS implements OSPolling
{
    public function pollOS()
    {
        $sess_data = snmp_get_multi_oid($this->getDeviceArray(), [
            '.1.3.6.1.4.1.3224.16.3.2.0',
            '.1.3.6.1.4.1.3224.16.3.3.0',
            '.1.3.6.1.4.1.3224.16.3.4.0',
        ]);
        [$sessalloc, $sessmax, $sessfailed] = array_values($sess_data);

        $rrd_def = RrdDefinition::make()
            ->addDataset('allocate', 'GAUGE', 0, 3000000)
            ->addDataset('max', 'GAUGE', 0, 3000000)
            ->addDataset('failed', 'GAUGE', 0, 1000);

        $fields = [
            'allocate' => $sessalloc,
            'max' => $sessmax,
            'failed' => $sessfailed,
        ];

        $tags = compact('rrd_def');
        data_update($this->getDeviceArray(), 'screenos_sessions', $tags, $fields);

        $this->enableGraph('screenos_sessions');
    }
}
