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

use App\Graphing\BaseGraph;
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
        $data = [
            [
                'label' => 'Port 1',
                'data' => [],
                'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                'borderColor' => 'rgba(255, 99, 132, 1)',
                'borderWidth' => 1,
            ],
            [
                'label' => 'Port 2',
                'data' => [],
                'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                'borderColor' => 'rgba(54, 162, 235, 1)',
                'borderWidth' => 1,
            ],
        ];

        $this->fillData($data[0]['data']);
        $this->fillData($data[1]['data']);

        return $data;
    }

    private function fillData(&$array)
    {
        for ($x = 200; $x >= 0; $x -= 5) {
            $array[] = ['x' => $this->now->subMinutes($x)->timestamp * 1000, 'y' => rand(0, 32)];
        }
    }
}
