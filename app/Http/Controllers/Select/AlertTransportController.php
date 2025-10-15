<?php

/**
 * AlertTransportController.php
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
 * © 2025 Tony Murray
 *
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Select;

use App\Models\AlertTransport;
use Illuminate\Http\Request;

class AlertTransportController extends SelectController
{
    protected function searchFields(Request $request)
    {
        return ['transport_type', 'transport_name'];
    }

    public function baseQuery(Request $request)
    {
        return AlertTransport::query()
            ->select(['transport_id', 'transport_type', 'transport_name'])
            ->orderBy('transport_type')
            ->orderBy('transport_name');
    }

    public function formatItem($model)
    {
        return [
            'id' => $model->transport_id,
            'text' => ucfirst((string) $model->transport_type) . ': ' . $model->transport_name,
        ];
    }
}
