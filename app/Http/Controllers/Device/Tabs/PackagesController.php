<?php

/**
 * PackagesController.php
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
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Device\Tabs;

use App\Http\Controllers\PackagesController as AllPackagesController;
use App\Models\Device;
use App\Models\Package;
use App\Models\User;
use Illuminate\Http\Request;
use LibreNMS\Interfaces\UI\DeviceTab;

class PackagesController implements DeviceTab
{
    public function visible(Device $device): bool
    {
        return $device->packages()->exists();
    }

    public function slug(): string
    {
        return 'packages';
    }

    public function icon(): string
    {
        return 'fa-folder';
    }

    public function name(): string
    {
        return __('Pkgs');
    }

    public function data(Device $device, Request $request): array
    {
        /** @var User $user */
        $user = $request->user();

        return [
            'packages' => AllPackagesController::filteredQuery($request, $device->device_id)->orderBy('name')->orderBy('version')->orderBy('build')->get(),
            'filter' => $request->array('filter'),
            'filterFields' => Package::filterFieldDefinitions($user, $device->device_id),
        ];
    }
}
