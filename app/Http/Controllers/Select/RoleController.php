<?php

/*
 * RolesController.php
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
 * @copyright  2023 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Select;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class RoleController extends SelectController
{
    protected function searchFields(Request $request): array
    {
        return ['name'];
    }

    protected function baseQuery(Request $request): \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
    {
        $this->authorize('viewAny', Role::class);

        if (Role::exists()) {
            return Role::query()->select('name');
        }

        // Create a query builder from a raw SQL that returns default values
        return DB::table(DB::raw("(SELECT 'admin' as name UNION ALL SELECT 'global-read' UNION ALL SELECT 'user') as roles"));
    }

    /**
     * @param  Role  $role
     * @return array
     */
    public function formatItem($role): array
    {
        return [
            'id' => $role->name,
            'text' => Str::title(str_replace('-', ' ', $role->name)),
        ];
    }
}
