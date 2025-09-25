<?php

/**
 * PortSecurityStatus.php
 *
 * Enumerates Port Security Status with color mappings
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

namespace LibreNMS\Enum;

class PortSecurityStatus
{
    const SECURE_UP = 'secureup';
    const SECURE_DOWN = 'securedown';
    const SHUTDOWN = 'shutdown';

    const COLORS = [
        self::SECURE_UP => 'green',
        self::SECURE_DOWN => 'orange',
        self::SHUTDOWN => 'red',
    ];

    /**
     * Get the icon class for a given port security status
     *
     * @param  string  $status
     * @return string
     */
    public static function getIconClass(string $status): string
    {
        $color = self::COLORS[$status] ?? null;

        if ($color === null) {
            return 'fa-shield';
        }

        $colorMap = [
            'green' => 'tw:text-green-600',
            'orange' => 'tw:text-orange-600',
            'red' => 'tw:text-red-600',
        ];

        return 'fa-shield ' . ($colorMap[$color] ?? '');
    }
}