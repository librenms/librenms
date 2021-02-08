<?php
/**
 * InventoryController.php
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

use App\Facades\DeviceCache;
use App\Models\Device;
use LibreNMS\Config;
use LibreNMS\Interfaces\UI\DeviceTab;

class InventoryController implements DeviceTab
{
    private $type = null;

    public function __construct()
    {
        if (Config::get('enable_inventory')) {
            $device = DeviceCache::getPrimary();

            if ($device->entityPhysical()->exists()) {
                $this->type = 'entphysical';
            } elseif ($device->hostResources()->exists()) {
                $this->type = 'hrdevice';
            }
        }
    }

    public function visible(Device $device): bool
    {
        return $this->type !== null;
    }

    public function slug(): string
    {
        return 'inventory';
    }

    public function icon(): string
    {
        return 'fa-cube';
    }

    public function name(): string
    {
        return __('Inventory');
    }

    public function data(Device $device): array
    {
        return [
            'tab' => $this->type, // inject to load correct legacy file
        ];
    }
}
