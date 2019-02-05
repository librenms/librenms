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
    private $required = false;
    private $disabled = false;
    private $options = [];
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

    /**
     * Check given value is valid. Using the type of this config item and possibly other variables.
     *
     * @param $value
     * @return bool|mixed
     */
    public function checkValue($value)
    {
        if ($this->type == 'boolean') {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== null;
        } elseif ($this->type == 'integer') {
            return filter_var($value, FILTER_VALIDATE_INT);
        } elseif ($this->type == 'select') {
            return isset($this->options[$value]);
        } elseif ($this->type == 'email') {
            return filter_var($value, FILTER_VALIDATE_EMAIL);
        } elseif (in_array($this->type, ['text', 'password'])) {
            return true;
        }

        return false;
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

    public function getOptions()
    {
        return $this->options;
    }

    public function isHidden()
    {
        return $this->hidden;
    }

    public function isRequired()
    {
        return $this->required;
    }

    public function getType()
    {
        return $this->type;
    }

    public function toArray()
    {
        return get_object_vars($this);
    }

    public function isValid()
    {
        return ($this->group == "" || $this->type) && !$this->hidden && !$this->disabled;
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
