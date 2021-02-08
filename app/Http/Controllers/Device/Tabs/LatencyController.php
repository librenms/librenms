<?php
/**
 * LatencyController.php
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Device\Tabs;

use App\Models\Device;
use Carbon\Carbon;
use DB;
use LibreNMS\Config;
use LibreNMS\Interfaces\UI\DeviceTab;
use LibreNMS\Util\Smokeping;
use Request;

class LatencyController implements DeviceTab
{
    public function visible(Device $device): bool
    {
        return Config::get('smokeping.integration') || DB::table('device_perf')
            ->where('device_id', $device->device_id)->exists();
    }

    public function slug(): string
    {
        return 'latency';
    }

    public function icon(): string
    {
        return 'fa-line-chart';
    }

    public function name(): string
    {
        return __('Latency');
    }

    public function data(Device $device): array
    {
        $from = Request::get('dtpickerfrom', Carbon::now()->subDays(2)->format(Config::get('dateformat.byminute')));
        $to = Request::get('dtpickerto', Carbon::now()->format(Config::get('dateformat.byminute')));

        $perf = $this->fetchPerfData($device, $from, $to);

        $duration = $perf && $perf->isNotEmpty()
            ? abs(strtotime($perf->first()->date) - strtotime($perf->last()->date)) * 1000
            : 0;

        $smokeping = new Smokeping($device);
        $smokeping_tabs = [];
        if ($smokeping->hasInGraph()) {
            $smokeping_tabs[] = 'in';
        }
        if ($smokeping->hasOutGraph()) {
            $smokeping_tabs[] = 'out';
        }

        return [
            'dtpickerfrom' => $from,
            'dtpickerto' => $to,
            'duration' => $duration,
            'perfdata' => $this->formatPerfData($perf),
            'smokeping' => $smokeping,
            'smokeping_tabs' => $smokeping_tabs,
        ];
    }

    private function fetchPerfData(Device $device, $from, $to)
    {
        return DB::table('device_perf')
            ->where('device_id', $device->device_id)
            ->whereBetween('timestamp', [$from, $to])
            ->select(DB::raw("DATE_FORMAT(timestamp, '%Y-%m-%d %H:%i') date,xmt,rcv,loss,min,max,avg"))
            ->get();
    }

    /**
     * Data ready for json export
     * @param \Illuminate\Support\Collection $data
     * @return array
     */
    private function formatPerfData($data)
    {
        return $data->reduce(function ($data, $entry) {
            $data[] = ['x' => $entry->date, 'y' => $entry->loss, 'group' => 0];
            $data[] = ['x' => $entry->date, 'y' => $entry->min, 'group' => 1];
            $data[] = ['x' => $entry->date, 'y' => $entry->max, 'group' => 2];
            $data[] = ['x' => $entry->date, 'y' => $entry->avg, 'group' => 3];

            return $data;
        }, []);
    }
}
