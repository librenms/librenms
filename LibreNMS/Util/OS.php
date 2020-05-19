<?php
/**
 * OS.php
 *
 * OS related functions (may belong in LibreNMS/OS, but here for now)
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
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

use LibreNMS\Config;
use Symfony\Component\Yaml\Yaml;

class OS
{
    /**
     * Load os from yaml into config, preserving user os config
     * @param string $os
     * @param bool $force (reload the config)
     */
    public static function loadOsDefinition($os)
    {
        $yaml_file = base_path("/includes/definitions/$os.yaml");

        if (!Config::getOsSetting($os, 'definition_loaded') && file_exists($yaml_file)) {
            $os_def = Yaml::parse(file_get_contents($yaml_file));

            Config::set("os.$os", array_replace_recursive($os_def, Config::get("os.$os", [])));
            Config::set("os.$os.definition_loaded", true);
        }
    }
}
