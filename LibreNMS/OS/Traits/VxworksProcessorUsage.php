<?php

/**
 * VxworksProcessorUsage.php
 *
 * Several devices use the janky output of this oid
 * Requires both ProcessorDiscovery and ProcessorPolling
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS\Traits;

use App\Models\Processor;
use Illuminate\Support\Collection;
use SnmpQuery;

trait VxworksProcessorUsage
{
    /**
     * Discover processors.
     * Returns an array of LibreNMS\Device\Processor objects that have been discovered
     *
     * @return Collection<Processor>
     */
    public function discoverProcessors($oid = '.1.3.6.1.4.1.4413.1.1.1.1.4.9.0'): Collection
    {
          $usage = SnmpQuery::get($oid)->value();
        if ($usage) {
            $usage = $this->parseCpuUsageString($usage);
            if (is_numeric($usage)) {
                return collect([
                    new Processor([
                        'processor_type' => $this->getName(),
                        'processor_oid' => $oid,
                        'processor_index' => 0,
                        'processor_usage' => $usage,
                    ]),
                ]);
            }
        }

        return new Collection;
    }

    /**
     * Poll processor data.  This can be implemented if custom polling is needed.
     *
     * @param  Collection<Processor>  $processors  Array of processor entries from the database that need to be polled
     * @return Collection<Processor> of polled data
     */
    public function pollProcessors(Collection $processors): Collection
    {
        foreach ($processors as $processor) {
            $processor->processor_usage = $this->parseCpuUsageString(
                SnmpQuery::get($processor->processor_oid)->value()
            );
        }

        return $processors;
    }

    /**
     * Parse the silly cpu usage string
     * "    5 Secs ( 96.4918%)   60 Secs ( 54.2271%)  300 Secs ( 38.2591%)"
     */
    protected function parseCpuUsageString(string $data): ?string
    {
        preg_match('/(\d+\.\d+)%/', $data, $matches);

        return $matches[1] ?? null;
    }
}
