<?php

/**
 * Utf8Sanitize.php
 *
 * Custom Eloquent cast that converts a string attribute to valid UTF-8 before
 * storage.  SNMP devices may return strings in non-UTF-8 encodings (e.g.
 * Windows-1252 printer descriptions containing trademark symbols ™), which
 * would otherwise cause database exceptions when written into UTF-8 columns.
 *
 * Apply this cast explicitly to individual string fields that carry raw SNMP
 * data, giving precise control over which columns are sanitized.
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
 * @copyright  2026 LibreNMS Contributors
 */

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use LibreNMS\Util\StringHelpers;

class Utf8Sanitize implements CastsAttributes
{
    /**
     * Cast the given value from storage — already UTF-8, return as-is.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return $value;
    }

    /**
     * Prepare the given value for storage, converting to valid UTF-8 if needed.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        return StringHelpers::inferEncoding((string) $value);
    }
}
