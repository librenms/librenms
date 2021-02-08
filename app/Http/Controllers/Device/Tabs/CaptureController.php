<?php
/**
 * CaptureController.php
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
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Http\Controllers\Device\Tabs;

use App\Models\Device;

class CaptureController implements \LibreNMS\Interfaces\UI\DeviceTab
{
    public function visible(Device $device): bool
    {
        return false;
    }

    public function slug(): string
    {
        return 'capture';
    }

    public function icon(): string
    {
        return 'fa-bug';
    }

    public function name(): string
    {
        return __('Capture');
    }

    public function data(Device $device): array
    {
        return [];
    }
}
