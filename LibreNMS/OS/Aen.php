<?php

/**
 * Aen.php
 *
 * Accedian OS
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use Illuminate\Support\Collection;
use LibreNMS\OS;

class Aen extends OS
{
    public function discoverProcessors(): Collection
    {
        // don't poll v5.3.1_22558 devices due to bug that crashes snmpd
        if ($this->getDevice()->version == 'AEN_5.3.1_22558') {
            return new Collection;
        }

        return parent::discoverProcessors();
    }
}
