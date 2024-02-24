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

class CustomMap extends BaseModel
{
    use HasFactory;
    protected $primaryKey = 'custom_map_id';
    protected $casts = [
        'options' => 'array',
        'newnodeconfig' => 'array',
        'newedgeconfig' => 'array',
    ];
    protected $fillable = [
        'name',
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
        'background_suffix',
        'background_version',
    ];

    // default values for attributes
    protected $attributes = [
        'options' => '{"interaction":{"dragNodes":false,"dragView":false,"zoomView":false},"manipulation":{"enabled":false},"physics":{"enabled":false}}',
        'newnodeconfig' => '{"borderWidth":1,"color":{"border":"#2B7CE9","background":"#D2E5FF"},"font":{"color":"#343434","size":14,"face":"arial"},"icon":[],"label":true,"shape":"box","size":25}',
        'newedgeconfig' => '{"arrows":{"to":{"enabled":true}},"smooth":{"type":"dynamic"},"font":{"color":"#343434","size":12,"face":"arial"},"label":true}',
        'background_version' => 0,
    ];

    public function hasAccess(): bool
    {
        return false; // TODO calculate based on device access
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
