<?php
/**
 * Location.php
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    public $fillable = ['location', 'lat', 'lng'];
    const CREATED_AT = null;
    const UPDATED_AT = 'timestamp';

    /**
     * Set up listeners for this Model
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function (Location $location) {
            if (!$location->hasCoordinates()) {
                $location->parseCoordinates();
            }
        });
    }

    // ---- Helper Functions ----

    public function hasCoordinates()
    {
        return !(is_null($this->lat) || is_null($this->lng));
    }

    public function lookupCoordinates()
    {
        if ($this->location) {
            /** @var \LibreNMS\Interfaces\Geocoder $api */
            $api = app(\LibreNMS\Interfaces\Geocoder::class);
            $this->fill($api->getCoordinates($this->location));
        }
    }

    protected function parseCoordinates()
    {
        $lat_regex = '(?<lat>[-+]?(?:[1-8]?\d(?:\.\d+)?|90(?:\.0+)?))';
        $lng_regex = '(?<lng>[-+]?(?:180(?:\.0+)?|(?:(?:1[0-7]\d)|(?:[1-9]?\d))(?:\.\d+)?))';
        $regex = '/\[\s*' . $lat_regex . '\s*,\s*' . $lng_regex . '\s*\]/';

        if (preg_match($regex, $this->location, $parsed)) {
            $this->fill($parsed);
        }
    }

    // ---- Define Relationships ----

    public function devices()
    {
        return $this->hasMany('App\Models\Device', 'location_id');
    }
}
