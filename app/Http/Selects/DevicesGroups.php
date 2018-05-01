<?php
/**
 * DevicesGroups.php
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Selects;

class DevicesGroups extends BaseSelect
{
    /**
     * Get the base query for this object
     *
     * @return \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
     */
    protected function baseQuery()
    {
        return null;
    }

    /**
     * Main worker function
     *
     * @return array
     */
    public function get()
    {
        $device_select = new Devices($this->request);
        $group_select = new Groups($this->request);

        $devices = $device_select->format($device_select->load());
        $groups = $group_select->load()->map(function ($group) {
            $group['id'] = 'g' . $group['id'];
            return $group;
        });

        $more = $device_select->hasMore($devices) || $group_select->hasMore($groups);

        // build up composite results
        $items = [];
        if ($devices->count()) {
            $items[] = ['text' => 'Devices', 'children' => $devices];
        }
        if ($groups->count()) {
            $items[] = ['text' => 'Groups', 'children' => $groups];
        }

        return [
            'results' => $items,
            'pagination' => ['more' => $more]
        ];
    }
}
