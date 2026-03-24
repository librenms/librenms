<?php

/*
 * SnmpQueryBase.php
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2026 Steven Wilton
 * @author     Steven Wilton <swilton@fluentit.au>
 */

namespace LibreNMS\Data\Source;

use App\Models\Device;

class SnmpQueryBase
{
    /**
     * Parse SNMP options
     */
    public function parseOptions(array $options): SnmpQueryInterface
    {
        foreach ($options as $option) {
            $this->parseOption($option);
        }

        return $this;
    }

    private function parseOption(string $option): SnmpQueryInterface
    {
        return match ($option) {
            'noExtendedIndex' => $this->noExtendedIndex(),
            'enumStrings' => $this->enumStrings(),
            default => throw new \Exception("parseOption() does not recognise $option as a valid SNMP option"),
        };
    }
}
