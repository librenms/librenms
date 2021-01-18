<?php
/*
 * ProcessorUsage.php
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

use App\Data\Sets\Processor;
use Illuminate\Http\Request;
use Rrd;

class ProcessorUsage extends \App\Graphing\BaseGraph
{
    public function data(Request $request): array
    {
        $this->init($request);
        $this->renderer->setLabels(['Usage'], 'Percent');
        $this->renderer->enableRangeValues();
        $this->renderer->setYRange(0, 100);
        return $this->renderer->formatRrdData($this->fetchData(1));
    }

    private function fetchData($id)
    {
        $processor = \App\Models\Processor::with('device')->find($id);
        /** @var \App\Models\Processor $processor */
        $dg = Processor::make($processor);
        $rrd_file = Rrd::fileName($dg, $dg->getDataSet('usage'));

        $defs = [
            "DEF:minusage$id=$rrd_file:value:MIN",
            "DEF:usage$id=$rrd_file:value:AVERAGE",
            "DEF:maxusage$id=$rrd_file:value:MAX",
            "XPORT:minusage$id:'min'",
            "XPORT:usage$id:'avg'",
            "XPORT:maxusage$id:'max'",
        ];

        return Rrd::xport($defs, $this->start->timestamp, $this->end->timestamp);
    }
}
