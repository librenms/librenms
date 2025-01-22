<?php
/*
 * IndexField.php
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
 * @copyright  2025 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Discovery\Yaml;

use Illuminate\Support\Str;
use LibreNMS\Util\Oid;

class IndexField extends YamlDiscoveryField
{
    public function calculateValue(array $yaml, array $data, string $index, int $count): void
    {
        if (array_key_exists($this->key, $yaml)) {
            parent::calculateValue($yaml, $data, $index, $count);

            return;
        }

        if (Str::startsWith($index, '.') && Oid::of($index)->isNumeric()) {
            // if this is a full numeric oid instead of an index, assume it is a scalar
            $this->value = 0;

            return;
        }

        $this->value = $index;
    }
}
