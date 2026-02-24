<?php

/**
 * Bill.php
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

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bill extends BaseModel
{
    use HasFactory;

    public $timestamps = false;
    protected $primaryKey = 'bill_id';

    // ---- Query Scopes ----

    public function scopeHasAccess(Builder $query, User $user): Builder
    {
        return $this->hasBillAccess($query, $user);
    }

    // ---- Define Relationships ----

    /**
     * @return HasMany<BillData, $this>
     */
    public function data(): HasMany
    {
        return $this->hasMany(BillData::class, 'bill_id', 'bill_id');
    }

    /**
     * @return HasMany<BillHistory, $this>
     */
    public function history(): HasMany
    {
        return $this->hasMany(BillHistory::class, 'bill_id', 'bill_id');
    }

    /**
     * @return HasMany<BillPortCounter, $this>
     */
    public function portCounters(): HasMany
    {
        return $this->hasMany(BillPortCounter::class, 'bill_id', 'bill_id');
    }

    /**
     * @return BelongsToMany<Port, $this>
     */
    public function ports(): BelongsToMany
    {
        return $this->belongsToMany(Port::class, 'bill_ports', 'bill_id', 'port_id');
    }
}
