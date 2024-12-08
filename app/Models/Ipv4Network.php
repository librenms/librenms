<?php
/**
 * Ipv4Network.php
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

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Ipv4Network extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $primaryKey = 'ipv4_network_id';
    protected $fillable = [
        'ipv4_network',
        'context_name',
    ];
    // ---- Define Relationships ----

    public function ipv4(): HasMany
    {
        return $this->hasMany(Ipv4Address::class, 'ipv4_network_id');
    }

    public function connectedPorts(): HasManyThrough
    {
        return $this->hasManyThrough(Port::class, Ipv4Address::class, 'ipv4_network_id', 'port_id', 'ipv4_network_id', 'port_id');
    }
}
