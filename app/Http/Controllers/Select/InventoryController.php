<?php
/**
 * EntPhysicalController.php
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
 * @copyright  2023 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Select;

use App\Models\EntPhysical;

class InventoryController extends SelectController
{
    protected function rules()
    {
        return [
            'field' => 'required|in:name,model,descr,class',
            'device' => 'nullable|int',
        ];
    }

    protected function filterFields($request)
    {
        return ['device_id'];
    }

    protected function searchFields($request)
    {
        return [$this->fieldToColumn($request->get('field'))];
    }

    protected function baseQuery($request)
    {
        $column = $this->fieldToColumn($request->get('field'));

        return EntPhysical::hasAccess($request->user())
            ->select($column)
            ->orderBy($column)
            ->distinct();
    }

    private function fieldToColumn(string $field): string
    {
        return match ($field) {
            'name' => 'entPhysicalName',
            'model' => 'entPhysicalModelName',
            'descr' => 'entPhysicalDescr',
            'class' => 'entPhysicalClass',
            default => 'entPhysicalName',
        };
    }
}
