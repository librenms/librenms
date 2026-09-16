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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * @extends SelectController<Dashboard>
 */
class DashboardController extends SelectController
{
    protected function searchFields($request): array
    {
        return ['dashboard_name', 'username'];
    }

    /**
     * Defines the base query for this resource
     */
    protected function baseQuery(Request $request): Builder
    {
        $this->authorize('viewAny', Dashboard::class);

        return Dashboard::hasAccess($request->user())
            ->leftJoin('users', 'dashboards.user_id', 'users.user_id') // left join so we can search username
            ->orderBy('dashboards.user_id')
            ->orderBy('dashboard_name')
            ->select(['dashboard_id', 'username', 'dashboard_name']);
    }

    /**
     * @param  Dashboard  $model
     * @return array{id: int|string, text: string, icon?: string}
     */
    public function formatItem(Model $model): array
    {
        return [
            'id' => $model->dashboard_id,
            'text' => $this->describe($model),
        ];
    }

    protected function prependItem(): array
    {
        return [
            'id' => 0,
            'text' => __('No Default Dashboard'),
        ];
    }

    /**
     * @param  Dashboard  $dashboard
     * @return string
     */
    private function describe(Dashboard $dashboard): string
    {
        if ($dashboard->dashboard_id == 0) {
            return $this->prependItem()['text'];
        }

        $username = $dashboard->username ?? __('Unknown');

        return "$username: {$dashboard->dashboard_name} ("
            . ($dashboard->access == 1 ? __('read-only') : __('read-write')) . ')';
    }
}
