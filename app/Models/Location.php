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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace App\Models;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LibreNMS\Util\Dns;

/**
 * @method static \Database\Factories\LocationFactory factory(...$parameters)
 */
class Location extends Model
{
    use HasFactory;

    public $fillable = ['location', 'lat', 'lng'];
    const CREATED_AT = null;
    const UPDATED_AT = 'timestamp';
    protected $casts = ['lat' => 'float', 'lng' => 'float', 'fixed_coordinates' => 'bool'];

    private $location_regex = '/\[\s*(?<lat>[-+]?(?:[1-8]?\d(?:\.\d+)?|90(?:\.0+)?))\s*,\s*(?<lng>[-+]?(?:180(?:\.0+)?|(?:(?:1[0-7]\d)|(?:[1-9]?\d))(?:\.\d+)?))\s*\]/';

    // ---- Helper Functions ----

    /**
     * Checks if this location has resolved latitude and longitude.
     *
     * @return bool
     */
    public function hasCoordinates()
    {
        return ! (is_null($this->lat) || is_null($this->lng));
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
     * Try to parse coordinates
     * then try to lookup DNS LOC records if hostname is provided
     * then call geocoding API to resolve latitude and longitude.
     *
     * @param  string  $hostname
     * @return bool
     */
    public function lookupCoordinates($hostname = null)
    {
        if ($this->location && $this->parseCoordinates()) {
            return true;
        }

        if ($hostname && \LibreNMS\Config::get('geoloc.dns')) {
            $coord = app(Dns::class)->getCoordinates($hostname);

            if (! empty($coord)) {
                $this->fill($coord);

                return true;
            }
        }

        if ($this->location && ! $this->hasCoordinates() && \LibreNMS\Config::get('geoloc.latlng', true)) {
            return $this->fetchCoordinates();
        }

        return false;
    }

    /**
     * Remove encoded GPS for nicer display
     *
     * @param  bool  $withCoords
     * @return string
     */
    public function display($withCoords = false)
    {
        return (trim(preg_replace($this->location_regex, '', $this->location)) ?: $this->location)
            . ($withCoords && $this->coordinatesValid() ? " [$this->lat,$this->lng]" : '');
    }

    protected function parseCoordinates()
    {
        if (preg_match($this->location_regex, $this->location, $parsed)) {
            $this->fill($parsed);

            return true;
        }

        return false;
    }

    protected function fetchCoordinates()
    {
        try {
            /** @var \LibreNMS\Interfaces\Geocoder $api */
            $api = app(\LibreNMS\Interfaces\Geocoder::class);
            $this->fill($api->getCoordinates($this->location));

            return true;
        } catch (BindingResolutionException $e) {
            // could not resolve geocoder, Laravel isn't booted. Fail silently.
        }

        return false;
    }

    // ---- Query scopes ----

    /**
     * @param  Builder  $query
     * @param  User  $user
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

    public function devices(): HasMany
    {
        return $this->hasMany(\App\Models\Device::class, 'location_id');
    }

    public function __toString()
    {
        return $this->location;
    }
}
