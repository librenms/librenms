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
 *
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Select;

use App\Models\Dashboard;

class DashboardController extends SelectController
{
    protected function searchFields($request)
    {
        return ['dashboard_name', 'username'];
    }

    /**
     * Defines the base query for this resource
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    protected function baseQuery($request)
    {
        return Dashboard::query()
            ->where('access', '>', 0)
            ->leftJoin('users', 'dashboards.user_id', 'users.user_id') // left join so we can search username
            ->orderBy('dashboards.user_id')
            ->orderBy('dashboard_name')
            ->select(['dashboard_id', 'username', 'dashboard_name']);
    }

    /**
     * @param  object  $dashboard
     * @return array
     */
    public function formatItem($dashboard): array
    {
        return [
            'id' => $dashboard->dashboard_id,
            'text' => $this->describe($dashboard),
        ];
    }

    public function formatResponse($paginator)
    {
        if (! request()->has('term')) {
            $paginator->prepend((object) ['dashboard_id' => 0]);
        }

        return parent::formatResponse($paginator);
    }

    private function describe($dashboard): string
    {
        if ($dashboard->dashboard_id == 0) {
            return 'No Default Dashboard';
        }

        return "{$dashboard->username}: {$dashboard->dashboard_name} ("
            . ($dashboard->access == 1 ? __('read-only') : __('read-write')) . ')';
    }
}
