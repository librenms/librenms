<?php

/**
 * OSIdentification.php
 *
 * Implemented by the data source of a polling method that can tell which OS a device runs.
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
 * @copyright  2026 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Interfaces\Discovery;

interface OSIdentification
{
    /**
     * The LibreNMS os name of the device, such as linux. Null when the source cannot tell.
     * The most specific answer of the device's methods becomes its os, see the core module.
     */
    public function identifyOS(): ?string;
}
