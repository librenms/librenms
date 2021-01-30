<?php
/*
 * Dygraph.php
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Graphing\Renderer;

use App\Graphing\Interfaces\Renderer;
use InfluxDB\ResultSet;
use LibreNMS\Data\SeriesData;

class Dygraph implements Renderer
{
    private $config;

    public function __construct()
    {
        $this->config = [
            'data' => null,  // will be filled later
            'config' => [
                'labels' => ['x', 'value'],
                'legend' => 'always',
                'showRoller' => true,
//                'rollPeriod' => 2,
                'customBars' => false,
                'ylabel' => null,
            ],
        ];
    }

    public function formatRrdData($data): array
    {
        $timestamp = $data['meta']['start'];
        $step = $data['meta']['step'];
        $output = [];
        $rangeValues = $this->config['config']['customBars'];

        foreach ($data['data'] as $values) {
            $output[] = array_merge([$timestamp], $rangeValues ? array_chunk($values, 3) : $values);
            $timestamp += $step;
        }

        $this->config['data'] = $output;

        return $this->config;
    }

    public function setLabels($labels, $yLabel = null)
    {
        $this->config['config']['ylabel'] = $yLabel;
        $this->config['config']['labels'] = array_merge(['x'], $labels);
    }

    public function enableRangeValues()
    {
        $this->config['config']['customBars'] = true;
    }

    public function setTimeRange($start, $end)
    {
        // TODO: Implement setTimeRange() method.
    }

    public function setYRange($min = null, $max = null)
    {
        $this->config['config']['valueRange'] = [$min, $max];
    }

    public function formatInfluxData(ResultSet $data): array
    {
        return [];
    }

    public function formatData(SeriesData $data): array
    {
        $this->config['data'] = iterator_to_array($data);
        return $this->config;
    }
}
