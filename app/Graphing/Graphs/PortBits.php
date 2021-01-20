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

use App\Data\Sets\PortPackets;
use App\Facades\Rrd;
use App\Graphing\BaseGraph;
use App\Models\Port;
use Illuminate\Http\Request;
use LibreNMS\Data\Store\InfluxDB;

class PortBits extends BaseGraph
{
    public function data(Request $request): array
    {
        $this->init($request);
        $this->renderer->setLabels(['In', 'Out'], 'bps');
        $port = Port::with('device')->find($request->get('id'));
//        $data = $this->fetchData($port);
//        return $this->renderer->formatRrdData($data);
        $data = $this->fetchFromInfluxDB($port);
        return $this->renderer->formatInfluxData($data);
    }

    private function fetchData($port)
    {
        $id = $port->port_id;
        $rrd_file = Rrd::name($port->device->hostname, Rrd::portName($id));
        $defs = [
            "DEF:outoctets$id=$rrd_file:OUTOCTETS:AVERAGE",
            "DEF:inoctets$id=$rrd_file:INOCTETS:AVERAGE",
            "CDEF:doutoctets$id=outoctets$id,-1,*",
            "CDEF:doutbits$id=doutoctets$id,8,*",
            "CDEF:inbits$id=inoctets$id,8,*",
            "XPORT:inbits$id:'In'",
            "XPORT:doutbits$id:'Out'",
        ];

        return Rrd::xport($defs, $this->start->timestamp, $this->end->timestamp);
    }

    private function fetchFromInfluxDB(Port $port)
    {
        $dataGroup = PortPackets::make($port);

        $db = app(InfluxDB::class)->getConnection();
        $query = $db->getQueryBuilder()
            ->select('non_negative_derivative(mean("ifInOctets"), 1s) *8 AS "In", non_negative_derivative(mean("ifOutOctets"), 1s) *-8 AS "Out"')
            ->from($dataGroup->getName())
            ->where([
                "port = '$port->port_id'",
                'time >= ' . $this->start->timestamp . 's',
                'time <= ' . $this->end->timestamp . 's',
            ])
            ->groupBy('time(15s) fill(null)');

        return $query->getResultSet();
    }
}
