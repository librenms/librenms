<?php
/*
 * CheckParameter.php
 *
 * Data helper for parsing parameters from check help text
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
 * @copyright  2022 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Services;

class CheckParameter
{
    /** @var string */
    public $param;
    /** @var string */
    public $short;
    /** @var string */
    public $value;
    /** @var string */
    public $description = '';
    /** @var bool */
    public $required = false;
    /** @var string[] */
    public $exclusive_group;
    /** @var string[] */
    public $inclusive_group;

    /**
     * Create a new check parameter
     */
    public static function make(string $param, string $short, string $value, string $description = ''): CheckParameter
    {
        return new static($param, $short, $value, $description);
    }

    public function __construct(string $param, string $short, string $value, string $description = '')
    {
        $this->param = trim($param);
        $this->short = trim($short);
        $this->value = trim($value);
        $this->description = trim($description);
    }

    /**
     * Append to the existing description, adding new lines
     */
    public function appendDescription(string $line): CheckParameter
    {
        if (! empty($this->description)) {
            $this->description .= PHP_EOL;
        }

        $this->description .= trim($line);

        return $this;
    }

    /**
     * Mark this parameter as required
     */
    public function setRequired(bool $required = true): CheckParameter
    {
        $this->required = $required;

        return $this;
    }

    /**
     * @param  string[]  $group
     * @return \LibreNMS\Services\CheckParameter
     */
    public function setExclusiveGroup(array $group): CheckParameter
    {
        $this->exclusive_group = $group;

        return $this;
    }

    /**
     * @param  string[]  $group
     * @return \LibreNMS\Services\CheckParameter
     */
    public function setInclusiveGroup(array $group): CheckParameter
    {
        $this->inclusive_group = $group;

        return $this;
    }

    public function toEscapedArray(): array
    {
        return [
            'param' => htmlentities($this->param),
            'short' => htmlentities($this->short),
            'value' => htmlentities($this->value),
            'description' => htmlentities($this->description),
            'required' => $this->required,
            'exclusive_group' => isset($this->exclusive_group) ? array_map('htmlentities', $this->exclusive_group) : null,
            'inclusive_group' => isset($this->inclusive_group) ? array_map('htmlentities', $this->inclusive_group) : null,
        ];
    }
}
