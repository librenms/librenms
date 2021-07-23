<?php
/**
 * Plugin.php
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Plugin extends BaseModel
{
    public $timestamps = false;
    protected $primaryKey = 'plugin_id';

    // ---- Query scopes ----

    /**
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeIsActive($query)
    {
        return $query->where('plugin_active', 1);
    }

    public static function scan_new_plugins()
    {
        $countInstalled = 0;

        if (file_exists(\LibreNMS\Config::get('plugin_dir'))) {
            $plugin_files = array_diff(scandir(\LibreNMS\Config::get('plugin_dir')), ['..', '.']);
            $plugin_files = array_diff($plugin_files, self::pluck('plugin_name')->toarray());
            foreach ($plugin_files as $name) {
                if (is_dir(\LibreNMS\Config::get('plugin_dir') . '/' . $name)
                    && is_file(\LibreNMS\Config::get('plugin_dir') . '/' . $name . '/' . $name . '.php')
                    && dbInsert(['plugin_name' => $name, 'plugin_active' => '0'], 'plugins')) {
                    $countInstalled++;
                }
            }
        }

        return $countInstalled;
    }

    public static function scan_removed_plugins()
    {
        $countRemoved = 0;

        if (file_exists(\LibreNMS\Config::get('plugin_dir'))) {
            $plugin_files = scandir(\LibreNMS\Config::get('plugin_dir'));
            foreach (self::whereNotIn('plugin_name', $plugin_files)->select('plugin_name')->get() as $plugin) {
                if (dbDelete('plugins', '`plugin_name` = ?', $plugin->plugin_name)) {
                    $countRemoved++;
                }
            }
        }

        return  $countRemoved;
    }
}
