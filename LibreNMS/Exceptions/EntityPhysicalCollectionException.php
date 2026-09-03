<?php

/**
 * EntityPhysicalCollectionException.php
 *
 * Thrown when entity-physical data could not be collected from a device, as
 * opposed to the device reporting that it has no inventory. The two are not
 * interchangeable: an empty result is authoritative and should prune stale
 * rows, a failed collection tells us nothing and must not.
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
 */

namespace LibreNMS\Exceptions;

use Exception;

class EntityPhysicalCollectionException extends Exception
{
}
