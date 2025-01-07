<?php
/*
 * YamlDiscoveryField.php
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
 * @copyright  2023 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Discovery\Yaml;

use LibreNMS\Device\YamlDiscovery;

class YamlDiscoveryField
{
    public mixed $value = null;

    public function __construct(
        public readonly string    $key,
        public readonly ?string   $model_column = null,
        public readonly ?string   $default = null,
        public readonly ?\Closure $callback = null,
    ) {}

    public function calculateValue(array $yaml, array $data, string $index, int $count): void
    {
        if (array_key_exists($this->key, $yaml)) {
            $this->value = YamlDiscovery::replaceValues($this->key, $index, $count, $yaml, $data);

            return;
        }

        if (empty($this->default)) {
            $this->value = $this->default;

            return;
        }

        $this->value = YamlDiscovery::replaceValues('default', $index, $count, ['default' => $this->default], $data);
    }
}
