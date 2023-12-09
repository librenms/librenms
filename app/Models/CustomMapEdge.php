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

class CustomMapEdge extends BaseModel
{
    protected $primaryKey = 'custom_map_edge_id';
    protected $fillable = ['custom_map_id','custom_map_node1_id','custom_map_node2_id','style','showpct','textface','textsize','textcolour','mid_x','mid_y'];
    protected $casts = [
        'custom_map_id'       => 'int',
        'custom_map_node1_id' => 'int',
        'custom_map_node2_id' => 'int',
        'showpct'             => 'bool',
        'textsize'            => 'int',
        'mid_x'               => 'int',
        'mid_y'               => 'int',
    ];
    public $timestamps = false;

    public function map(): BelongsTo
    {
        return $this->belongsTo(CustomMap::class, 'custom_map_id');
    }

    public function edges(): HasMany
    {
        return $this->hasMany(CustomMapEdge::class, 'custom_map_edge_id');
    }

    public function nodes(): \Illuminate\Support\Collection
    {
        return $this->node1->merge($this->node2);
    }

    public function node1(): BelongsTo
    {
        return $this->belongsTo(CustomMapNode::class, 'custom_map_node1_id');
    }

    public function node2(): BelongsTo
    {
        return $this->belongsTo(CustomMapNode::class, 'custom_map_node2_id');
    }
}
