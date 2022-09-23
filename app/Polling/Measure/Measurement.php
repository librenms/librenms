<?php
/**
 * Measurement.php
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

namespace App\Polling\Measure;

class Measurement
{
    private $start;
    private $type;
    private $duration;

    private function __construct(string $type, float $duration = null)
    {
        $this->type = $type;
        $this->start = microtime(true);
        if ($duration !== null) {
            $this->duration = $duration;
        }
    }

    /**
     * Create a measurement with an existing duration
     */
    public static function make(string $type, float $duration): Measurement
    {
        return new static($type, $duration);
    }

    /**
     * Start the timer for a new operation
     *
     * @param  string  $type
     * @return static
     */
    public static function start(string $type): Measurement
    {
        return new static($type);
    }

    /**
     * End the timer for this operation
     */
    public function end(): Measurement
    {
        $this->duration = microtime(true) - $this->start;

        return $this;
    }

    /**
     * Get the duration of the operation
     */
    public function getDuration(): float
    {
        return $this->duration;
    }

    /**
     * Get the type of the operation
     */
    public function getType(): string
    {
        return $this->type;
    }

    public function manager(): MeasurementManager
    {
        return app(MeasurementManager::class);
    }
}
