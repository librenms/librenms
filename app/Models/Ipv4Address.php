<?php
/**
 * Ipv4Address.php
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
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ipv4Address extends PortRelatedModel
{
    use HasFactory;

    public $timestamps = false;
    protected $primaryKey = 'ipv4_address_id';
    protected $fillable = [
        'ipv4_address',
        'ipv4_prefixlen',
        'ipv4_network_id',
        'port_id',
        'context_name',
    ];

    public function network(): BelongsTo
    {
        return $this->belongsTo(Ipv4Network::class, 'ipv4_network_id', 'ipv4_network_id');
    }
}
