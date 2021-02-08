<?php
/**
 * DeviceTab.php
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

namespace LibreNMS\Interfaces\UI;

use App\Models\Device;

interface DeviceTab
{
    /**
     * Check if the tab is visible
     * @param Device $device
     * @return bool
     */
    public function visible(Device $device): bool;

    /**
     * The url slug for this tab
     * @return string
     */
    public function slug(): string;

    /**
     * The icon to display for this tab
     * @return string
     */
    public function icon(): string;

    /**
     * Name to display for this tab
     * @return string
     */
    public function name(): string;

    /**
     * Collect data to send to the view
     * @param Device $device
     * @return array
     */
    public function data(Device $device): array;
}
