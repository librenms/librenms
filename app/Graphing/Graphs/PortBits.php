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
    public function data(): array
    {
        $now = CarbonImmutable::now();
        return [
            ['x' => $now->subMinutes(25)->timestamp, 'y' => rand(0, 32)],
            ['x' => $now->subMinutes(20)->timestamp, 'y' => rand(0, 32)],
            ['x' => $now->subMinutes(15)->timestamp, 'y' => rand(0, 32)],
            ['x' => $now->subMinutes(10)->timestamp, 'y' => rand(0, 32)],
            ['x' => $now->subMinutes(5)->timestamp, 'y' => rand(0, 32)],
            ['x' => $now->timestamp, 'y' => rand(0, 32)],
        ];
    }
}
