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
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Bill extends BaseModel
{
    use HasFactory;

    public $timestamps = false;
    protected $primaryKey = 'bill_id';

    protected $fillable = [
        'bill_name',
        'bill_type',
        'bill_cdr',
        'bill_day',
        'bill_quota',
        'bill_custid',
        'bill_ref',
        'bill_notes',
        'dir_95th',
        'rate_95th_in',
        'rate_95th_out',
        'rate_95th',
        'total_data',
        'total_data_in',
        'total_data_out',
        'rate_average_in',
        'rate_average_out',
        'rate_average',
        'bill_last_calc',
        'bill_autoadded',
    ];

    public static function boot()
    {
        parent::boot();

        static::deleting(function (Bill $bill): void {
            $bill->history()->delete();
            $bill->data()->delete();
            $bill->counters()->delete();
            $bill->billPerms()->delete();
        });
    }

    // ---- Query Scopes ----

    protected function scopeHasAccess(Builder $query, User $user): Builder
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
     * Every source linked to this bill with its last counter sample
     *
     * @return HasMany<BillCounter, $this>
     */
    public function counters(): HasMany
    {
        return $this->hasMany(BillCounter::class, 'bill_id', 'bill_id');
    }

    /**
     * @return HasMany<BillPerm, $this>
     */
    public function billPerms(): HasMany
    {
        return $this->hasMany(BillPerm::class, 'bill_id', 'bill_id');
    }

    /**
     * @return MorphToMany<Port, $this, BillCounter>
     */
    public function ports(): MorphToMany
    {
        return $this->sources(Port::class);
    }

    /**
     * @return MorphToMany<MplsSap, $this, BillCounter>
     */
    public function mplsSaps(): MorphToMany
    {
        return $this->sources(MplsSap::class);
    }

    /**
     * All billable sources of this bill, each with the bill_counters pivot loaded
     *
     * @return Collection<int, Port|MplsSap>
     */
    public function billableSources(): Collection
    {
        return $this->ports()->get()->concat($this->mplsSaps()->get());
    }

    /**
     * @template TSource of \Illuminate\Database\Eloquent\Model
     *
     * @param  class-string<TSource>  $class
     * @return MorphToMany<TSource, $this, BillCounter>
     */
    private function sources(string $class): MorphToMany
    {
        return $this->morphedByMany($class, 'source', 'bill_counters', 'bill_id', 'source_id')
            ->using(BillCounter::class)
            ->withPivot(['autoadded', 'timestamp', 'in_counter', 'in_delta', 'out_counter', 'out_delta']);
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'bill_perms', 'bill_id', 'user_id');
    }
}
