<?php
/**
 * MeasurementCollection.php
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
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Data\Measure;

use Illuminate\Support\Collection;

class MeasurementCollection extends Collection
{
    public function getTotalCount()
    {
        return $this->sumStat('getCount');
    }

    public function getTotalDuration()
    {
        return $this->sumStat('getDuration');
    }

    public function getCountDiff()
    {
        return $this->sumStat('getCountDiff');
    }

    public function getDurationDiff()
    {
        return $this->sumStat('getDurationDiff');
    }

    public function checkpoint()
    {
        $this->each->checkpoint();
    }

    public function record(Measurement $measurement)
    {
        $type = $measurement->getType();

        if (! $this->has($type)) {
            $this->put($type, new MeasurementSummary($type));
        }

        $this->get($type)->add($measurement);
    }

    private function sumStat($function)
    {
        return $this->reduce(function ($sum, $measurement) use ($function) {
            $sum += $measurement->$function();

            return $sum;
        }, 0);
    }
}
