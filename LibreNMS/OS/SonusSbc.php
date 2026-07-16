<?php

/**
 * SonusSbc.php
 *
 * Sonus SBC
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
 * @copyright  2026 Sofia El Khalifi
 * @author     Sofia El Khalifi <sofiaelkhalifi@netsf.fr>
 */

namespace LibreNMS\OS;

use App\Facades\DeviceCache;
use App\Models\Device;
use App\Models\Mempool;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use LibreNMS\Device\Processor;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\OS;
use SnmpQuery;

class SonusSbc extends OS implements ProcessorDiscovery
{
    public function discoverOS(Device $device): void
    {
        parent::discoverOS($device); // yaml
        $this->discoverMempools();
    }

    /**
     * @return Collection<int, Mempool>
     */
    public function discoverMempools()
    {
        $deviceModel = DeviceCache::get($this->getDeviceId());
        /** @var Collection<int, Mempool> $mempools */
        $mempools = new Collection();
        $mempools_array = SnmpQuery::device($deviceModel)->numeric()->walk('.1.3.6.1.4.1.2879.2.8.5.1.19.1.2')->values();
        $size = 100;

        foreach (Arr::wrap($mempools_array) as $key => $value) {
            $device_ascii = explode('14.', (string) $key, 2);
            $codes_device = explode('.', $device_ascii[1]);
            $device_text = '';

            foreach ($codes_device as $code) {
                $device_text .= chr((int) $code);
            }

            if ($value != 0) {
                $mempools->push((new Mempool([
                    'mempool_index' => $device_text,
                    'mempool_type' => 'sonus-sbc',
                    'mempool_class' => 'system',
                    'mempool_descr' => 'Memory Utilization - ' . $device_text,
                    'mempool_perc_oid' => $key,
                    'mempool_perc_warn' => 90,
                ]))->fillUsage(null, $size, null, $value));
            }
        }

        return $mempools;
    }

    /**
     * @return array<int, Processor>
     */
    public function discoverProcessors()
    {
        $deviceModel = DeviceCache::get($this->getDeviceId());
        $proc_oid = '1.3.6.1.4.1.2879.2.8.5.1.17.1.3';

        $data = SnmpQuery::device($deviceModel)->numeric()->walk($proc_oid)->values();
        /** @var array<int, Processor> $processors */
        $processors = [];

        foreach ($data as $key => $value) {
            $device_oid = explode('14.', (string) $key, 2);
            $device_ascii = $device_oid[1];
            $codes_device = explode('.', $device_ascii);
            $device_text = '';

            foreach (array_slice($codes_device, 0) as $code) {
                $device_text .= chr((int) $code);
            }

            $processor_descr = 'CPU - ' . $device_text;
            $processor_type = 'sonus-fixed';

            $processors[] = Processor::discover(
                $processor_type,
                $this->getDeviceId(),
                $key,
                $device_text,
                $processor_descr,
                1,
                (int) $value
            );
        }

        return $processors;
    }
}
