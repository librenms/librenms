<?php
/**
 * DashboardController.php
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
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Select;

use App\Models\Dashboard;

class DashboardController extends SelectController
{
    protected function searchFields($request)
    {
        return ['dashboard_name'];
    }

    /**
     * Defines the base query for this resource
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    protected function baseQuery($request)
    {
        return Dashboard::query()
            ->where('access', '>', 0)
            ->with('user')
            ->orderBy('user_id')
            ->orderBy('dashboard_name');
    }

    public function formatItem($dashboard)
    {
        /** @var Dashboard $dashboard */
        return [
            'id' => $dashboard->dashboard_id,
            'text' => $this->describe($dashboard),
        ];
    }

    private function describe($dashboard)
    {
        return "{$dashboard->user->username}: {$dashboard->dashboard_name} ("
            . ($dashboard->access == 1 ? __('read-only') : __('read-write')) . ')';
    }
}
