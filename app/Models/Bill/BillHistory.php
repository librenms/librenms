<?php
/**
 * Bill.php
 *
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2018 Paul Heinrichs
 * @author     Paul Heinrichs <pdheinrichs@gmail.com>
 */

namespace App\Models\Bill;

use App\Models\BaseModel;

class BillHistory extends BaseModel
{
    protected $table = 'bill_history';
    
    protected $primaryKey = 'bill_history_id';

    public $timestamps = false;

    // ---- Define Relationships ----

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function bill()
    {
        return $this->belongsTo('App\Models\Bill\Bill', 'bill_id');
    }
}
