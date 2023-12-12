<?php
/**
 * CustomMapNode.php
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

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomMapNode extends BaseModel
{
    protected $primaryKey = 'custom_map_node_id';
    protected $fillable = ['device_id', 'label', 'style', 'icon', 'size', 'border_width', 'text_face', 'text_size', 'text_colour', 'colour_bg', 'colour_bdr', 'x_pos', 'y_pos'];
    protected $casts = [
        'device_id'    => 'int',
        'border_width' => 'int',
        'size'         => 'int',
        'textsize'     => 'int',
        'x_pos'        => 'int',
        'y_pos'        => 'int',
    ];
    public $timestamps = false;

    public function scopeHasAccess($query, User $user)
    {
        if ($user->hasGlobalRead()) {
            return $query;
        }

        // Allow only if the user has access to the node
        return $this->hasDeviceAccess($query, $user);
    }

    public function map(): BelongsTo
    {
        return $this->belongsTo(CustomMap::class, 'custom_map_id');
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class, 'device_id');
    }

    public function edges1(): HasMany
    {
        return $this->hasMany(CustomMapEdge::class, 'custom_map_node_id1');
    }

    public function edges2(): HasMany
    {
        return $this->hasMany(CustomMapEdge::class, 'custom_map_node_id2');
    }
}
