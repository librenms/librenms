<?php

/**
 * TemplateField.php
 *
 * A yaml field that is used as is, unless it contains a template
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

namespace LibreNMS\Discovery\Yaml;

use LibreNMS\Device\YamlDiscovery;

/**
 * Unlike YamlDiscoveryField, a plain value is never looked up in the fetched data.
 * Without a value in the yaml, the default is used.
 */
class TemplateField extends YamlDiscoveryField
{
    /**
     * @param  array<string, mixed>  $yaml
     * @param  array<int|string, mixed>  $data
     */
    public function calculateValue(array $yaml, array $data, string $index, int $count): void
    {
        $value = $yaml[$this->key] ?? $this->default;

        if (is_string($value) && str_contains($value, '{{')) {
            $value = trim((string) YamlDiscovery::replaceValues($this->key, $index, $count, $yaml, $data));
        }

        $this->value = is_callable($this->callback) ? call_user_func($this->callback, $value) : $value;
    }
}
