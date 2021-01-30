<?php
/*
 * Plotly.php
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

class Plotly implements Renderer
{
    private $layout;
    private $labels;
    private $rangeValues = false;

    public function __construct()
    {
        $this->layout = [
            'paper_bgcolor' => 'rgb(255,255,255)',
            'plot_bgcolor' => 'rgb(229,229,229)',
            'xaxis' => [
                'gridcolor' => 'rgb(255,255,255)',
                'showgrid' => true,
                'showline' => false,
                'showticklabels' => true,
                'tickcolor' => 'rgb(127,127,127)',
                'ticks' => 'outside',
                'zeroline' => false,
            ],
            'yaxis' => [
                'gridcolor' => 'rgb(255,255,255)',
                'showgrid' => true,
                'showline' => false,
                'showticklabels' => true,
                'tickcolor' => 'rgb(127,127,127)',
                'ticks' => 'outside',
                'zeroline' => false,
            ],
        ];
    }

    public function setLabels($labels, $yLabel)
    {
        $this->labels = $labels;
        $this->layout['yaxis']['title'] = $yLabel;
    }

    public function formatRrdData($data): array
    {
        $timestamp = $data['meta']['start'];
        $step = $data['meta']['step'];
        $output = $this->prepAxis();

        $indexes = array_keys($output);
        foreach ($data['data'] as $values) {
            foreach ($indexes as $index) {
                $output[$index]['x'][] = $timestamp;
                $output[$index]['y'][] = $values[$index];
            }
            $timestamp += $step;
        }

        return ['data' => $output, 'layout' => $this->layout];
    }

    public function enableRangeValues()
    {
        $this->rangeValues = true;
    }

    public function setTimeRange($start, $end)
    {
        // TODO: Implement setTimeRange() method.
    }

    public function setYRange($min = null, $max = null)
    {
        $this->layout['yaxis']['range'] = [$min, $max];
    }

    public function formatInfluxData(ResultSet $data): array
    {
        return [];
    }

    public function formatData(SeriesData $data): array
    {
        $output = $this->prepAxis();

        foreach (array_keys($output) as $index) {
            $output[$index]['x'] = $data->getSeries(0);
            $output[$index]['y'] = $data->getSeries($index + 1);
        }

        return ['data' => $output, 'layout' => $this->layout];
    }

    private function prepAxis(): array
    {
        $output = [];
        foreach ($this->labels as $index => $label) {
            if ($this->rangeValues) {
                $dataIndex = $index + $index * 3;
                $color = rand(0, 255) . ',' . rand(0, 255) . ',' . rand(0, 255);
                $output[$dataIndex] = [
                    'x' => [],
                    'y' => [],
                    'fill' => 'tozerox',
                    'fillcolor' => 'rgba(' . $color . ',0.2)',
                    'line' => ['color' => 'transparent'],
                    'mode' => 'lines',
                    'name' => 'Min',
                    'showlegend' => false,
                    'type' => 'scatter',
                ];
                $output[$dataIndex + 1] = [
                    'x' => [],
                    'y' => [],
                    'line' => ['color' => 'rgb(' . $color . ')'],
                    'mode' => 'lines',
                    'name' => 'Min',
                    'type' => 'scatter',
                ];
                $output[$dataIndex + 2] = [
                    'x' => [],
                    'y' => [],
                    'fill' => 'tozerox',
                    'fillcolor' => 'rgba(' . $color . ',0.2)',
                    'line' => ['color' => 'transparent'],
                    'mode' => 'lines',
                    'name' => 'Min',
                    'showlegend' => false,
                    'type' => 'scatter',
                ];
            } else {
                $output[$index] = [
                    'x' => [],
                    'y' => [],
                    'line' => [
                        'color' => 'rgb(' . rand(0, 255) . ',' . rand(0, 255) . ',' . rand(0, 255) . ')',
                    ],
                    'mode' => 'lines',
                    'name' => $label,
                    'type' => 'scatter',
                ];
            }
        }
        return $output;
    }
}
