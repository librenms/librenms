<?php

/**
 * nokia-1830.inc.php
 *
 * LibreNMS polling ports include for Nokia 1830 PSS/PSD
 * Ensures ifDescr is set from ifName for PSD devices that don't provide ifDescr
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 LibreNMS Contributors
 */

// Nokia 1830 PSD devices don't have ifDescr, only ifName
// Copy ifName to ifDescr so port validation works correctly
foreach ($port_stats as $ifIndex => $port) {
    if (empty($port_stats[$ifIndex]['ifDescr']) && ! empty($port_stats[$ifIndex]['ifName'])) {
        $port_stats[$ifIndex]['ifDescr'] = $port_stats[$ifIndex]['ifName'];
    }
}
