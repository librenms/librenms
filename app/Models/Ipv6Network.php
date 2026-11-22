<?php

/**
 * Ipv6Network.php
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
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\Gate;

class Ipv6Network extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $primaryKey = 'ipv6_network_id';
    protected $fillable = [
        'ipv6_network',
        'context_name',
    ];

    // ---- Query scopes ----

    /**
     * Networks containing at least one address the user can access.
     */
    protected function scopeHasAccess(Builder $query, User $user): Builder
    {
        if (Gate::forUser($user)->allows('viewAll', Port::class)) {
            return $query;
        }

        return $query->whereIn($this->qualifyColumn('ipv6_network_id'), Ipv6Address::hasAccess($user)->select('ipv6_network_id'));
    }

    // ---- Define Relationships ----
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\App\Models\Ipv6Address, $this>
     */
    public function ipv6(): HasMany
    {
        return $this->hasMany(Ipv6Address::class, 'ipv6_network_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough<\App\Models\Port, \App\Models\Ipv6Address, $this>
     */
    public function connectedPorts(): HasManyThrough
    {
        return $this->hasManyThrough(
            Port::class,
            Ipv6Address::class,
            'ipv6_network_id',
            'port_id',
            'ipv6_network_id',
            'port_id',
        );
    }
}
