<?php
/**
 * CollectdController.php
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
use LibreNMS\Config;
use LibreNMS\Interfaces\UI\DeviceTab;

class CollectdController implements DeviceTab
{
    public function visible(Device $device): bool
    {
        return Config::has('collectd_dir') && is_dir(Config::get('collectd_dir') . '/' . $device->hostname . '/');
    }

    public function slug(): string
    {
        return 'collectd';
    }

    public function icon(): string
    {
        return 'fa-pie-chart';
    }

    public function name(): string
    {
        return __('CollectD');
    }

    public function data(Device $device): array
    {
        return [];
    }
}
