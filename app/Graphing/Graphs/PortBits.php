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

use App\Data\DataGroup;
use App\Data\Sets\PortPackets;
use App\Graphing\BaseGraph;
use App\Graphing\QueryBuilder;
use App\Models\Port;
use Illuminate\Http\Request;

class PortBits extends BaseGraph
{
    public function data(Request $request): array
    {
        $this->init($request);
        $this->renderer->setLabels(['In', 'Out'], 'bps');
        $port = Port::with('device')->find($request->get('id'));
        $query = $this->getQuery(PortPackets::make($port));

        $data = app('Datastore')->fetch($query);
        return $this->renderer->formatData($data);
    }

    private function getQuery(DataGroup $dataGroup): QueryBuilder
    {
        return QueryBuilder::fromDataGroup($dataGroup)
            ->select('ifInOctets')->math('*', 8)
            ->select('ifOutOctets')->math('*', -8)
            ->range($this->start, $this->end);
    }
}
