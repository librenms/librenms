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
 * @copyright  2025 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Discovery\Yaml;

use LibreNMS\Device\YamlDiscovery;
use LibreNMS\Discovery\YamlDiscoveryDefinition;
use LibreNMS\Util\Number;
use LibreNMS\Util\StringHelpers;

class YamlDiscoveryField
{
    public bool $isOid = false;
    public mixed $value = null;
    public ?\Closure $should_poll;

    public function __construct(
        public readonly string $key,
        public readonly ?string $model_column = null,
        public readonly int|string|null $default = null,
        public readonly ?\Closure $callback = null,
    ) {
        $this->should_poll = fn (YamlDiscoveryDefinition $def) => false;
    }

    public function calculateValue(array $yaml, array $data, string $index, int $count): void
    {
        if (array_key_exists($this->key, $yaml)) {
            $key = $this->key;
        } else {
            $key = $this->default;
            $yaml = [$key => $this->default];
        }

        if (empty($yaml[$key]) || is_numeric($yaml[$key])) {
            // if default is an empty or simple value, just set it
            $this->setValue($yaml[$key]);

            return;
        }

        $value = YamlDiscovery::replaceValues($key, $index, $count, $yaml, $data);

        // oid is specifically looking for a number. So if it is not a number, return null
        if ($this->isOid && ! StringHelpers::hasNumber($value)) {
            $this->setValue(null);

            return;
        }

        $this->setValue($value);
    }

    private function setValue(mixed $value): void
    {
        if (is_callable($this->callback)) {
            $this->value = call_user_func($this->callback, $value);

            return;
        }

        if ($this->isOid && $value !== null) {
            $value = Number::cast($value);
        }

        $this->value = $value;
    }
}
