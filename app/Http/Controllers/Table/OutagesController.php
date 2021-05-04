<?php
/**
 * OutagesController.php
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
 * @copyright  2020 Thomas Berberich
 * @author     Thomas Berberich <sourcehhdoctor@gmail.com>
 */

namespace App\Http\Controllers\Table;

use App\Models\DeviceOutage;
use Carbon\Carbon;
use LibreNMS\Config;
use LibreNMS\Util\Url;

class OutagesController extends TableController
{
    public function rules()
    {
        return [
            'device' => 'nullable|int',
            'to' => 'nullable|date',
            'from' => 'nullable|date',
        ];
    }

    protected function filterFields($request)
    {
        return [
            'device_id' => 'device',
        ];
    }

    protected function sortFields($request)
    {
        return ['going_down', 'up_again', 'device_id'];
    }

    /**
     * Defines the base query for this resource
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function baseQuery($request)
    {
        return DeviceOutage::hasAccess($request->user())
            ->with('device')
            ->when($request->from, function ($query) use ($request) {
                $query->where('going_down', '>=', strtotime($request->from));
            })
            ->when($request->to, function ($query) use ($request) {
                $query->where('going_down', '<=', strtotime($request->to));
            });
    }

    /**
     * @param DeviceOutage $outage
     */
    public function formatItem($outage)
    {
        $start = $this->formatDatetime($outage->going_down);
        $end = $outage->up_again ? $this->formatDatetime($outage->up_again) : '-';
        $duration = ($outage->up_again ?: time()) - $outage->going_down;

        return [
            'status' => $this->statusLabel($outage),
            'going_down' => $start,
            'up_again' => $end,
            'device_id' => $outage->device ? Url::deviceLink($outage->device, $outage->device->shortDisplayName()) : null,
            'duration' => $this->formatTime($duration),
        ];
    }

    private function formatTime($duration)
    {
        $day_seconds = 86400;

        $duration_days = (int) ($duration / $day_seconds);
        $duration_time = $duration % $day_seconds;

        $output = "<span style='display:inline;'>";
        if ($duration_days) {
            $output .= $duration_days . 'd ';
        }
        $output .= (new Carbon($duration))->format(Config::get('dateformat.time'));
        $output .= '</span>';

        return $output;
    }

    private function formatDatetime($timestamp)
    {
        if (! $timestamp) {
            $timestamp = 0;
        }

        $output = "<span style='display:inline;'>";
        $output .= (new Carbon($timestamp))->format(Config::get('dateformat.compact'));
        $output .= '</span>';

        return $output;
    }

    private function statusLabel($outage)
    {
        if (empty($outage->up_again)) {
            $label = 'label-danger';
        } else {
            $label = 'label-success';
        }

        $output = "<span class='alert-status " . $label . "'></span>";

        return $output;
    }
}
