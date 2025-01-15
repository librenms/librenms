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
 * @copyright  2023 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Discovery\Yaml;

class NumericOidField extends YamlDiscoveryField
{
    private $required;

    /**
     * @param  string  $key
     * @param  string|null  $model_column
     * @param  string|null  $default
     * @param  callable|null  $callback
     * @param  callable|bool  $required
     */
    public function __construct(string $key, ?string $model_column = null, ?string $default = null, $callback = null, callable|bool $required = false)
    {
        parent::__construct($key, $model_column, $default, $callback);

        $this->required = $required;
    }

    public function isRequired()
    {
        if (is_callable($this->required)) {
            return call_user_func($this->required);
        }

        return $this->required;
    }
}
