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

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CustomMap extends BaseModel
{
    protected $primaryKey = 'custom_map_id';
    protected $fillable = ['name','width','height','background_suffix','background_version','options','newnodeconfig','newedgeconfig'];
    protected $casts = [
        'background_version'  => 'integer',
        'options'             => 'json',
        'newnodeconfig'       => 'json',
        'newedgeconfig'       => 'json',
    ];
    public $timestamps = false;

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
