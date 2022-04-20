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

    public function __construct(string $param, string $short, string $value)
    {
        $this->param = $param;
        $this->short = $short;
        $this->value = $value;
    }

    public function appendDescription(string $line)
    {
        if (! empty($this->description)) {
            $this->description .= PHP_EOL;
        }

        $this->description .= trim($line);
    }
}
