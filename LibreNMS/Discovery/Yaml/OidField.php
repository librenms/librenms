<?php
/*
 * OidField.php
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

use LibreNMS\Discovery\YamlDiscoveryDefinition;

class OidField extends YamlDiscoveryField
{
    public bool $isOid = true;

    public function __construct(string $key, ?string $model_column = null, ?string $default = null, ?\Closure $callback = null, \Closure|bool|null $should_poll = null)
    {
        parent::__construct($key, $model_column, $default, $callback);

        // should poll heuristic
        if (is_bool($should_poll)) {
            $this->should_poll = fn (YamlDiscoveryDefinition $def) => $should_poll;
        } elseif ($should_poll === null) {
            $this->should_poll = fn (YamlDiscoveryDefinition $def) => is_numeric($this->value);
        } else {
            $this->should_poll = $should_poll;
        }
    }
}
