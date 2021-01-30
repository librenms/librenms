<?php
/*
 * MetricsGraphics.php
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

class MetricsGraphics implements Renderer
{
    private $config;

    public function __construct()
    {
        $this->config = [
            'title' => 'TEST Title',
            'description' => 'Just some more text',
            'data' => [],
            'width' => 1000,
            'height' => 500,
            'right' => 40,
            'x_accessor' => 0,
            'y_accessor' => 1,
            'aggregate_rollover' => true,
            'brush' => 'x',
            'legend' => [],
        ];
    }

    public function setLabels($labels, $yLabel)
    {
        $this->config['legend'] = $labels;

        if ($yLabel == 'bps') {
//            $this->config['yax_format'] = '.2s';
//            $this->config['y_rollover_format'] = '.2s';
            $this->config['yax_units'] = $yLabel;
            $this->config['yax_units_append'] = true;
        } elseif ($yLabel == 'Percent') {
            $this->config['yax_units'] = '%';
            $this->config['yax_units_append'] = true;
        } else {
            $this->config['y_label'] = $yLabel;
        }
    }

    public function formatData(SeriesData $data): array
    {
        $output = [];
        foreach ($data as $point) {
            foreach ($this->config['legend'] as $index => $label) {
                $output[$index][] = [$point[0], $point[$index + 1]];
            }
        }

        $this->config['data'] = $output;

        return $this->config;
    }

    public function formatRrdData($data): array
    {
        $timestamp = $data['meta']['start'];
        $step = $data['meta']['step'];
        $output = [];
        $rangeValues = isset($this->config['show_confidence_band']);

        foreach ($data['data'] as $values) {
            foreach ($this->config['legend'] as $index => $label) {
                $dataIndex = $index + $index * 3;
                $output[$index][] = $rangeValues
                    ? [$timestamp, $values[$dataIndex + 1], $values[$dataIndex], $values[$dataIndex + 2]]
                    : [$timestamp, $values[$index]];
            }

            $timestamp += $step;
        }

        $this->config['data'] = $output;

        return $this->config;
    }

    public function formatInfluxData(ResultSet $data): array
    {
        $rangeValues = isset($this->config['show_confidence_band']);

        $output = [];
        foreach ($data->getSeries()[0]['values'] as $point) {
            foreach ($this->config['legend'] as $index => $label) {
                $output[$index][] = [$point[0], $point[$index + 1]];
            }
        }

        $this->config['data'] = $output;

        return $this->config;
    }

    public function enableRangeValues()
    {
        $this->config['show_confidence_band'] = [2, 3];
    }

    public function setTimeRange($start, $end)
    {
        // TODO: Implement setTimeRange() method.
    }

    public function setYRange($min = null, $max = null)
    {
        $this->config['min_y'] = $min;
        $this->config['max_y'] = $max;
    }
}
