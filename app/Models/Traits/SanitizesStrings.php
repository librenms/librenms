<?php

/**
 * SanitizesStrings.php
 *
 * Ensures string attributes are valid UTF-8 before saving to the database.
 * SNMP devices may return strings in non-UTF-8 encodings (e.g., Windows-1252),
 * which causes database exceptions when stored in UTF-8 columns.
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

namespace App\Models\Traits;

use LibreNMS\Util\StringHelpers;

trait SanitizesStrings
{
    public static function bootSanitizesStrings(): void
    {
        static::saving(function ($model): void {
            foreach ($model->getDirty() as $key => $value) {
                if (is_string($value)) {
                    $model->attributes[$key] = StringHelpers::inferEncoding($value);
                }
            }
        });
    }
}
