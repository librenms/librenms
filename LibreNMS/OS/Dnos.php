<?php

/**
 * Dnos.php
 *
 * Dell Network OS
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

namespace LibreNMS\OS;

use App\Models\Processor;
use Illuminate\Support\Collection;
use LibreNMS\OS;

class Dnos extends OS
{
    public function discoverProcessors(): Collection
    {
        $processors = parent::discoverProcessors();

        // the F10 chassis tables report the same cpus, only use them when dellNetCpuUtilTable is missing
        $dellNet = $processors->filter(fn (Processor $processor) => str_starts_with((string) $processor->processor_index, 'dellNetCpuUtil'));

        return $dellNet->isNotEmpty() ? $dellNet->values() : $processors;
    }
}
