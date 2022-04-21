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
    /** @var \LibreNMS\Services\CheckParameter[] */
    public $group;

    public function __construct(string $param, string $short, string $value, string $description = '')
    {
        $this->param = trim(htmlspecialchars($param));
        $this->short = trim(htmlspecialchars($short));
        $this->value = trim(htmlspecialchars($value));
        $this->description = trim(htmlspecialchars($description));
    }

    /**
     * Append to the existing description, adding new lines
     */
    public function appendDescription(string $line): void
    {
        if (! empty($this->description)) {
            $this->description .= PHP_EOL;
        }

        $this->description .= trim(htmlspecialchars($line));
    }

    /**
     * Mark this parameter as required
     */
    public function setRequired(bool $required = true): void
    {
        $this->required = $required;
    }

    /**
     * @param  \LibreNMS\Services\CheckParameter[]  $group
     * @return void
     */
    public function setExclusiveGroup(array $group): void
    {
        $this->group = $group;
    }
}
