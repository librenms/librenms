<?php
/**
 * MeasurementSummary.php
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

class MeasurementSummary
{
    private $type;
    private $count = 0;
    private $duration = 0.0;

    private $checkpointCount = 0;
    private $checkpointDuration = 0.0;

    public function __construct(string $type)
    {
        $this->type = $type;
    }

    public function add(Measurement $measurement)
    {
        $this->count++;
        $this->duration += $measurement->getDuration();
    }

    /**
     * Get the measurement summary
     * ['count' => #, 'duration' => s]
     *
     * @return array
     */
    public function get()
    {
        return [
            'count' => $this->count,
            'duration' => $this->duration,
        ];
    }

    public function getCount()
    {
        return $this->count;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getDuration()
    {
        return $this->duration;
    }

    public function checkpoint()
    {
        $this->checkpointCount = $this->count;
        $this->checkpointDuration = $this->duration;
    }

    public function getCountDiff()
    {
        return $this->count - $this->checkpointCount;
    }

    public function getDurationDiff()
    {
        return $this->duration - $this->checkpointDuration;
    }
}
