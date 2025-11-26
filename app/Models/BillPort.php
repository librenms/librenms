<?php

/**
 * BillPort.php
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
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillPort extends BaseModel
{
    public $timestamps = false;
    protected $table = 'bill_ports';

    protected $fillable = [
        'bill_id',
        'port_id',
        'bill_port_autoadded',
    ];

    // ---- Define Relationships ----

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Bill, $this>
     */
    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class, 'bill_id', 'bill_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\Port, $this>
     */
    public function port(): BelongsTo
    {
        return $this->belongsTo(Port::class, 'port_id', 'port_id');
    }
}
