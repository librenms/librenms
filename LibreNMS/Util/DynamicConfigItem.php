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
    private $name;
    private $group;
    private $section;
    private $value;
    private $description;
    private $type;
    private $default;
    private $hidden = false;
    private $class;
    private $help;
    private $pattern;

    public function __construct($name, $settings = [])
    {
        $this->name = $name;
        $this->value = Config::get($this->name, $this->default);
        foreach ($settings as $key => $value) {
            $this->$key = $value;
        }
    }

    public function getGroup()
    {
        return $this->group;
    }

    public function getSection()
    {
        return $this->section;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function isHidden()
    {
        return $this->hidden;
    }

    public function getType()
    {
        return $this->type;
    }

    public function toArray()
    {
        return get_object_vars($this);
    }


    // ArrayAccess functions
    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }

    public function offsetGet($offset)
    {
        return isset($this->$offset) ? $this->$offset : null;
    }

    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->$offset);
    }

    public function getName()
    {
        return $this->name;
    }
}
