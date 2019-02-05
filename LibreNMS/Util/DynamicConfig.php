<?php
/**
 * DynamicConfig.php
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

use Illuminate\Support\Collection;
use LibreNMS\Config;

class DynamicConfig
{
    private $definitions;

    public function __construct()
    {
        $this->definitions = collect(Config::getDefinitions())->map(function ($item, $key) {
            return new DynamicConfigItem($key, $item);
        });
    }

    /**
     * Check if a setting is valid
     *
     * @param string $path
     * @param mixed $value
     * @return static
     */
    public function isValidSetting($path, $value = null)
    {
        // TODO test type too
        return $this->definitions->has($path);
    }

    /**
     * Get all groups defined
     *
     * @return \Illuminate\Support\Collection
     */
    public function getGroups()
    {
        return $this->definitions->pluck('group')->unique()->filter()->prepend('global');
    }

    public function getGrouped()
    {
        // FIXME Laravel 5.5: $this->definitions->groupBy(['group', 'section']

        /** @var Collection $grouped */
        $grouped = $this->definitions->groupBy('group')->map->groupBy('section');
        $grouped->prepend($grouped->pull(''), 'global'); // rename '' to global
        return $grouped;
    }

    public function getByGroup($group, $subgroup = null)
    {
        return $this->definitions->filter(function ($item) use ($group, $subgroup) {
            /** @var DynamicConfigItem $item */
            if ($item->getGroup() != $group) {
                return false;
            }

            if ($subgroup && $item->getSection() != $subgroup) {
                return false;
            }

            return !$item->isHidden();
        })->sortBy('order');
    }
}
