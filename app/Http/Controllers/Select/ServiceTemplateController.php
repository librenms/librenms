<?php

/**
 * ServiceTemplateController.php
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
 * @copyright  2020 Anthony F McInerney <bofh80>
 * @author     Anthony F McInerney <afm404@gmail.com>
 */

namespace App\Http\Controllers\Select;

use App\Models\ServiceTemplate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * @extends SelectController<ServiceTemplate>
 */
class ServiceTemplateController extends SelectController
{
    protected function searchFields(Request $request): array
    {
        return ['name'];
    }

    protected function baseQuery(Request $request): Builder
    {
        $this->authorize('viewAny', ServiceTemplate::class);

        return ServiceTemplate::hasAccess($request->user())->select(['id', 'name']);
    }

    /**
     * @param  ServiceTemplate  $model
     * @return array{id: int|string, text: string, icon?: string}
     */
    public function formatItem(Model $model): array
    {
        return [
            'id' => $model->id,
            'text' => $model->name,
        ];
    }
}
