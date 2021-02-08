<?php
/**
 * Categorizer.php
 *
 * Categorize a list of items according to a dynamic list
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

class Categorizer
{
    protected $items;
    protected $categorized = [];
    protected $categories = [];
    protected $skippable;

    public function __construct($items = [])
    {
        $this->skippable = function ($item) {
            return false;
        };
        $this->items = $items;
    }

    public function addCategory(string $category, callable $function)
    {
        $this->categories[$category] = $function;
        $this->categorized[$category] = [];
    }

    public function setSkippable(callable $function)
    {
        $this->skippable = $function;
    }

    public function categorize()
    {
        foreach ($this->items as $item) {
            foreach ($this->categories as $category => $test) {
                if (call_user_func($this->skippable, $item)) {
                    continue;
                }

                $result = call_user_func($test, $item);
                if ($result !== false) {
                    $this->categorized[$category][] = $result;
                }
            }
        }

        return $this->categorized;
    }
}
