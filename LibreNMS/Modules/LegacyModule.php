<?php
/**
 * LegacyModule.php
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
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Modules;

use LibreNMS\Interfaces\Module;
use LibreNMS\OS;
use LibreNMS\Util\Debug;

class LegacyModule implements Module
{

    /**
     * @var string
     */
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function discover(OS $os)
    {
        // TODO: Implement discover() method.
    }

    public function poll(OS $os)
    {
        $device = &$os->getDeviceArray();
        $device['attribs'] = $os->getDevice()->attribs->toArray();
        Debug::set(false);

        include_once base_path('includes/dbFacile.php');

        include base_path("includes/polling/$this->name.inc.php");
    }

    public function cleanup(OS $os)
    {
        // TODO: Implement cleanup() method.
    }
}
