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

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    public $fillable = ['location', 'lat', 'lng'];
    const CREATED_AT = null;
    const UPDATED_AT = 'timestamp';

    private $location_regex = '/\[\s*(?<lat>[-+]?(?:[1-8]?\d(?:\.\d+)?|90(?:\.0+)?))\s*,\s*(?<lng>[-+]?(?:180(?:\.0+)?|(?:(?:1[0-7]\d)|(?:[1-9]?\d))(?:\.\d+)?))\s*\]/';


    /**
     * Set up listeners for this Model
     */
    public static function boot()
    {
        parent::boot();

        static::creating(function (Location $location) {
            // parse coordinates for new locations
            $location->lookupCoordinates();
        });
    }

    // ---- Helper Functions ----

    /**
     * Checks if this location has resolved latitude and longitude.
     *
     * @return bool
     */
    public function hasCoordinates()
    {
        return !(is_null($this->lat) || is_null($this->lng));
    }

    /**
     * Check if the coordinates are valid
     * Even though 0,0 is a valid coordinate, we consider it invalid for ease
     */
    public function coordinatesValid()
    {
        return $this->lat && $this->lng &&
            abs($this->lat) <= 90 && abs($this->lng) <= 180;
    }

    /**
     * Try to parse coordinates then
     * call geocoding API to resolve latitude and longitude.
     */
    public function lookupCoordinates()
    {
        if (!$this->hasCoordinates() && $this->location) {
            $this->parseCoordinates();

            if (!$this->hasCoordinates() &&
                \LibreNMS\Config::get('geoloc.latlng', true) &&
                (!$this->id || $this->timestamp && $this->timestamp->diffInDays() > 2)
            ) {
                $this->fetchCoordinates();
                $this->updateTimestamps();
            }
        }
    }

    /**
     * Remove encoded GPS for nicer display
     *
     * @return string
     */
    public function display()
    {
        return trim(preg_replace($this->location_regex, '', $this->location)) ?: $this->location;
    }

    protected function parseCoordinates()
    {
        if (preg_match($this->location_regex, $this->location, $parsed)) {
            $this->fill($parsed);
        }
    }

    protected function fetchCoordinates()
    {
        try {
            /** @var \LibreNMS\Interfaces\Geocoder $api */
            $api = app(\LibreNMS\Interfaces\Geocoder::class);
            $this->fill($api->getCoordinates($this->location));
        } catch (BindingResolutionException $e) {
            // could not resolve geocoder, Laravel isn't booted. Fail silently.
        }
    }

    // ---- Query scopes ----

    /**
     * @param Builder $query
     * @param User $user
     * @return Builder
     */
    public function scopeHasAccess($query, $user)
    {
        if ($user->hasGlobalRead()) {
            return $query;
        }

        $ids = Device::hasAccess($user)
            ->distinct()
            ->whereNotNull('location_id')
            ->pluck('location_id');

        return $query->whereIn('id', $ids);
    }

    public function scopeInDeviceGroup($query, $deviceGroup)
    {
        return $query->whereHas('devices.groups', function ($query) use ($deviceGroup) {
            $query->where('device_groups.id', $deviceGroup);
        });
    }


    // ---- Define Relationships ----

    public function devices()
    {
        return $this->hasMany('App\Models\Device', 'location_id');
    }

    public function __toString()
    {
        return $this->location;
    }
}
