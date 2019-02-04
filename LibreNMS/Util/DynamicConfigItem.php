<?php
/**
 * DynamicConfigItem.php
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
 * @copyright  2019 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

use LibreNMS\Config;

class DynamicConfigItem implements \ArrayAccess
{
    private $path;
    private $group;
    private $sub_group;
    private $value;
    private $description;
    private $default;
    private $hidden = false;

    public function __construct($path, $settings = [])
    {
        $this->path = $path;
        $this->value = Config::get($this->path, $this->default);
        foreach ($settings as $key => $value) {
            $this->$key = $value;
        }
    }

    public function getGroup()
    {
        return $this->group;
    }

    public function getSubGroup()
    {
        return $this->sub_group;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function isHidden()
    {
        return $this->hidden;
    }


    // ArrayAccess functions
    public function offsetExists($offset)
    {
        $offset = $this->convertLegacyField($offset);

        return isset($this->$offset);
    }

    public function offsetGet($offset)
    {
        $offset = $this->convertLegacyField($offset);

        return isset($this->$offset) ? $this->$offset : null;
    }

    public function offsetSet($offset, $value)
    {
        $offset = $this->convertLegacyField($offset);

        $this->$offset = $value;
    }

    public function offsetUnset($offset)
    {
        $offset = $this->convertLegacyField($offset);

        unset($this->$offset);
    }

    private function convertLegacyField($offset)
    {
        $offset = str_replace('config_', '', $offset);

        if ($offset == 'descr') {
            $offset = 'description';
        }

        return $offset;
    }
}
