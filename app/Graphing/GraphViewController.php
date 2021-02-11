<?php
/*
 * GraphViewController.php
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

namespace App\Graphing;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class GraphViewController
{
    public function __invoke(Request $request)
    {
        [$_, $group, $graph] = explode('/', $request->path(), 3);

        $options = [
            'id' => $request->get('id'),
            'from' => $this->parseDate($request->get('from', CarbonImmutable::now()->subHours(2))),
            'to' => $this->parseDate($request->get('from', CarbonImmutable::now())),
        ];

        return view('graph', ['url' => route("graph_data.{$group}_{$graph}", $options)], $options);
    }

    private function parseDate($date): CarbonImmutable
    {
        return CarbonImmutable::parse(is_numeric($date) ? intval($date) : $date);
    }
}
