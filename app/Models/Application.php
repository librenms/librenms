<?php
/**
 * Applications.php
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

use LibreNMS\Util\StringHelpers;

class Application extends DeviceRelatedModel
{
    public $timestamps = false;
    protected $primaryKey = 'app_id';
    protected $fillable = ['data'];
    protected $casts = [
        'data' => 'string',
    ];

    // ---- Helper Functions ----

    public function displayName()
    {
        return StringHelpers::niceCase($this->app_type);
    }

    public function getShowNameAttribute()
    {
        return StringHelpers::niceCase($this->app_type);
    }

    /**
     * Saves the passed array as JSON to data.
     *
     * @param  array  $data
     * @return null
     */
    public function save_data($data = [])
    {
        $this->fill(['data'=>json_encode($data)]);
        $this->save();
    }

    /**
     * Decodes the JSON stored in data and returns a array.
     *
     * @return array
     */
    public function get_data()
    {
        $parsed_json = json_decode($this->data, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [];
        }

        return $parsed_json;
    }
}
