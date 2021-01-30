<?php
/*
 * Chartjs.php
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

class Chartjs implements Renderer
{
    private $config;
    private $data = [];
    private $colors = [
        '255, 99, 132',
        '54, 162, 235',
    ];
    private $rangeValues = false;

    public function __construct()
    {
        $this->config = [
            'type' => 'line',
            'data' => ['datasets' => null],
            'options' => [
                'responsive' => true,
                'scales' => [
                    'xAxes' => [
                        [
                            'type' => 'time',
                            'time' => [
                                'unit' => 'hour',
                                'displayFormats' => ['hour' => 'M-D-YYYY hh:mm'],
                            ],
                        ],
                    ],
                    'yAxes' => [[]],
                ],
            ],
        ];
    }

    public function formatRrdData($data): array
    {
        $timestamp = $data['meta']['start'] * 1000;
        $step = $data['meta']['step'] * 1000;

        foreach ($data['data'] as $entry) {
            foreach ($this->data as $index => $config) {
                $dataIndex = $this->rangeValues ? $index * 3 + 1 : $index;  // can't handle ranges
                $this->data[$index]['data'][] = ['x' => $timestamp, 'y' => $entry[$dataIndex]];
            }
            $timestamp += $step;
        }
        $this->config['data']['datasets'] = $this->data;

        return $this->config;
    }

    public function setLabels($labels, $yLabel)
    {
        foreach ($labels as $index => $label) {
            $this->data[$index] = [
                'label' => $label,
                'data' => null,
                'backgroundColor' => "rgba({$this->colors[$index]}, 0.2)",
                'borderColor' => "rgba({$this->colors[$index]}, 1)",
                'borderWidth' => 1,
            ];
        }

        $this->config['options']['scales']['yAxes'][0]['scaleLabel']['labelString'] = $yLabel;
        $this->config['options']['scales']['yAxes'][0]['scaleLabel']['display'] = ! empty($yLabel);
    }

    public function enableRangeValues()
    {
        $this->rangeValues = true;
    }

    public function setTimeRange($start, $end)
    {
        $this->config['options']['scales']['xAxes']['ticks'] = ['min' => $start, 'max' => $end];
    }

    public function setYRange($min = null, $max = null)
    {
        if ($min === 0 || $max === 0) {
            $this->config['options']['scales']['yAxes'][0]['ticks']['beginAtZero'] = true;
        }

        $this->config['options']['scales']['yAxes'][0]['ticks']['min'] = $min;
        $this->config['options']['scales']['yAxes'][0]['ticks']['max'] = $max;
    }

    public function formatInfluxData(ResultSet $data): array
    {
        return [];
    }

    public function formatData(SeriesData $data): array
    {
        foreach ($data as $point) {
            foreach ($this->data as $index => $config) {
                $this->data[$index]['data'][] = ['x' => $point[0] * 1000, 'y' => $point[$index + 1]];
            }
        }
        $this->config['data']['datasets'] = $this->data;

        return $this->config;
    }
}
