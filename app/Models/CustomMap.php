<?php
/**
 * CustomMap.php
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
 * @copyright  2023 Steven Wilton
 * @author     Steven Wilton <swilton@fluentit.com.au>
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Permissions;

class CustomMap extends BaseModel
{
    use HasFactory;
    protected $primaryKey = 'custom_map_id';
    protected $casts = [
        'options' => 'array',
        'legend_colours' => 'array',
        'newnodeconfig' => 'array',
        'newedgeconfig' => 'array',
        'background_data' => 'array',
    ];
    protected $fillable = [
        'name',
        'menu_group',
        'width',
        'height',
        'node_align',
        'reverse_arrows',
        'edge_separation',
        'legend_x',
        'legend_y',
        'legend_steps',
        'legend_font_size',
        'legend_hide_invalid',
        'legend_hide_overspeed',
        'background_type',
        'background_data',
    ];

    /**
     * Get background data intended to be passed to javascript to configure the background
     */
    public function getBackgroundConfig(): array
    {
        $config = $this->background_data ?? [];
        $config['engine'] = \LibreNMS\Config::get('geoloc.engine');
        $config['api_key'] = \LibreNMS\Config::get('geoloc.api_key');
        $config['tile_url'] = \LibreNMS\Config::get('leaflet.tile_url');
        /* @phpstan-ignore-next-line seems to think version is not in array 100% of the time... which is wrong */
        $config['image_url'] = route('maps.custom.background', ['map' => $this->custom_map_id]) . '?version=' . ($config['version'] ?? 0);

        return $config;
    }

    public function hasReadAccess(User $user): bool
    {
        $device_ids = $this->nodes()->whereNotNull('device_id')->pluck('device_id');

        // Restricted users can only view maps that have at least one device
        if (count($device_ids) === 0) {
            return false;
        }

        // Deny access if we don't have permission on any device
        foreach ($device_ids as $device_id) {
            if (! Permissions::canAccessDevice($device_id, $user)) {
                return false;
            }
        }

        return true;
    }

    public function scopeHasAccess($query, User $user)
    {
        if ($user->hasGlobalRead()) {
            return $query;
        }

        // Allow only if the user has access to all devices on the map
        return $query->withCount([
            'nodes as device_nodes_count' => function (Builder $q) {
                $q->whereNotNull('device_id');
            },
            'nodes as device_nodes_allowed_count' => function (Builder $q) use ($user) {
                $this->hasDeviceAccess($q, $user, 'custom_map_nodes');
            },
        ])
            ->havingRaw('device_nodes_count = device_nodes_allowed_count')
            ->having('device_nodes_count', '>', 0);
    }

    public function nodes(): HasMany
    {
        return $this->hasMany(CustomMapNode::class, 'custom_map_id');
    }

    public function edges(): HasMany
    {
        return $this->hasMany(CustomMapEdge::class, 'custom_map_id');
    }

    public function background(): HasOne
    {
        return $this->hasOne(CustomMapBackground::class, 'custom_map_id');
    }
}
