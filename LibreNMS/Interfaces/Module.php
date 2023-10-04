<?php
/**
 * Module.php
 *
 * LibreNMS Discovery/Poller Module
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Interfaces;

use App\Models\Device;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\OS;
use LibreNMS\Polling\ModuleStatus;

interface Module
{
    /**
     * An array of all modules this module depends on
     */
    public function dependencies(): array;

    /**
     * Should this module be run?
     */
    public function shouldDiscover(OS $os, ModuleStatus $status): bool;

    /**
     * Should polling run for this device?
     */
    public function shouldPoll(OS $os, ModuleStatus $status): bool;

    /**
     * Discover this module. Heavier processes can be run here
     * Run infrequently (default 4 times a day)
     *
     * @param  \LibreNMS\OS  $os
     */
    public function discover(OS $os): void;

    /**
     * Poll data for this module and update the DB / RRD.
     * Try to keep this efficient and only run if discovery has indicated there is a reason to run.
     * Run frequently (default every 5 minutes)
     *
     * @param  \LibreNMS\OS  $os
     * @param  \LibreNMS\Interfaces\Data\DataStorageInterface  $datastore
     */
    public function poll(OS $os, DataStorageInterface $datastore): void;

    /**
     * Remove all DB data for this module.
     * This will be run when the module is disabled.
     *
     * @param  \App\Models\Device  $device
     */
    public function cleanup(Device $device): void;

    /**
     * Dump current module data for the given device for tests.
     * Make sure to hide transient fields, such as id and date.
     * You should always order the data by a non-transient column.
     * Some id fields may need to be joined to tie back to non-transient data.
     * Module may return false if testing is not supported or required.
     *
     * @return array|false
     */
    public function dump(Device $device);
}
