<?php
/*
 * PortBits.php
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

namespace App\Graphing\Graphs;

use App\Facades\Rrd;
use App\Graphing\BaseGraph;
use App\Models\Port;
use Carbon\CarbonImmutable;

class PortBits extends BaseGraph
{
    private $now;

    public function __construct()
    {
        $this->now = CarbonImmutable::now();
    }

    public function data(): array
    {
        return \Request::get('renderer') == 'dygraph'
            ? $this->getDygraph()
            : $this->getChartJs();
    }

    private function getDygraph()
    {
        return [
            'data' => $this->formatRrdData($this->fetchData(101)),
            'config' => [
                'labels' => ['x', 'In', 'Out'],
                'legend' => 'always',
                'showRoller' => true,
                'rollPeriod' => 2,
                'customBars' => false,
                'ylabel' => 'bps',
            ],
        ];
    }

    private function getChartJs()
    {
        $actual = $this->formatRrdDataChartjs($this->fetchData(101));

        $data = [
            [
                'label' => 'In',
                'data' => $actual[0],
                'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                'borderColor' => 'rgba(255, 99, 132, 1)',
                'borderWidth' => 1,
            ],
            [
                'label' => 'Out',
                'data' => $actual[1],
                'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                'borderColor' => 'rgba(54, 162, 235, 1)',
                'borderWidth' => 1,
            ],
        ];

        return [
            'type' => 'line',
            'data' => ['datasets' => $data],
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
                            'ticks' => [
                                'min' => $this->now->subHours(2)->timestamp * 1000,
                                'max' => $this->now->timestamp * 1000,
                            ],
                        ],
                    ],
                    'yAxes' => [
                        [
                            'ticks' => [
                                'beginAtZero' => true,
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function fetchData($id)
    {
        $port = Port::with('device')->find($id);

        $rrd_file = Rrd::name($port->device->hostname, Rrd::portName($id));
        $defs = "DEF:outoctets$id=$rrd_file:OUTOCTETS:AVERAGE DEF:inoctets$id=$rrd_file:INOCTETS:AVERAGE CDEF:doutoctets$id=outoctets$id,-1,* CDEF:doutbits$id=doutoctets$id,8,* CDEF:inbits$id=inoctets$id,8,* XPORT:inbits$id:'In' XPORT:doutbits$id:'Out' ";

        return Rrd::xport($defs, $this->now->subHours(2)->timestamp, $this->now->timestamp);
    }

    private function formatRrdData($data)
    {
        $timestamp = $data['meta']['start'];
        $step = $data['meta']['step'];
        $output = [];

        foreach ($data['data'] as $entry) {
            $output[] = array_merge([$timestamp], $entry);
            $timestamp += $step;
        }

        return $output;
    }

    private function formatRrdDataChartjs($data)
    {
        $timestamp = $data['meta']['start'] * 1000;
        $step = $data['meta']['step'] * 1000;
        $in = [];
        $out = [];

        foreach ($data['data'] as $entry) {
            $in[] = ['x' => $timestamp, 'y' => $entry[0]];
            $out[] = ['x' => $timestamp, 'y' => $entry[1]];
            $timestamp += $step;
        }

        return [$in, $out];
    }

    private function genDataDygraph($count = 2)
    {
        $array = [];
        for ($x = 200; $x >= 0; $x -= 5) {
            $data = [$this->now->subMinutes($x)->timestamp];
            for ($i = 0; $i < $count; $i++) {
                $data[] = rand(0, 32);
            }
            $array[] = $data;
        }

        return $array;
    }

    private function fillDataChartJs(&$array)
    {
        for ($x = 200; $x >= 0; $x -= 5) {
            $array[] = ['x' => $this->now->subMinutes($x)->timestamp * 1000, 'y' => rand(0, 32)];
        }
    }
}
