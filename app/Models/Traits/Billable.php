<?php

/**
 * Billable.php
 *
 * The bill relation shared by every LibreNMS\Interfaces\Models\BillableSource
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
 */

namespace App\Models\Traits;

use App\Models\Bill;
use App\Models\BillCounter;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait Billable
{
    public static function bootBillable(): void
    {
        static::deleting(function (self $source): void {
            $source->bills()->detach();
        });
    }

    /**
     * @return MorphToMany<Bill, $this, BillCounter>
     */
    public function bills(): MorphToMany
    {
        return $this->morphToMany(Bill::class, 'source', 'bill_counters', 'source_id', 'bill_id')
            ->using(BillCounter::class);
    }
}
