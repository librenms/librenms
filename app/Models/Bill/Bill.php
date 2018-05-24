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

class Bill extends BaseModel
{
    protected $primaryKey = 'bill_id';

    public $timestamps = false;

    protected $hidden = ['pivot'];

    protected $fillable = [
        'bill_name', 
        'bill_type', 
        'bill_day', 
        'bill_cdr', 
        'bill_quota', 
        'bill_notes', 
        'bill_custid', 
        'bill_ref',
        'rate_95th_in',
        'rate_95th_out',
        'rate_95th',
        'dir_95th',
        'total_data',
        'total_data_in',
        'total_data_out',
        'rate_average_in',
        'rate_average_out',
        'rate_average',
        'bill_last_calc',
        'bill_autoadded'
    ];

    protected $appends = ['used', 'overuse', 'percent', 'allowed'];

    protected static function boot()
    {
        parent::boot();

        static::deleting(function($bill) {
            $bill->ports()->detach();
            $bill->history()->delete();
        });
    }

    // ---- Appends Fields ----
    public function getUsedAttribute()
    {
        if ($this->isCdr()) {
            return format_si($this->attributes['rate_95th'])."bps";
        } elseif ($this->isQuota()) {
            return format_bytes_billing($this->attributes['total_data']);
        }
    }

    public function getAllowedAttribute()
    {
        if ($this->isCdr()) {
            return format_si($this->attributes['bill_cdr'])."bps";
        } elseif ($this->isQuota()) {
            return format_bytes_billing($this->attributes['bill_quota']);
        }
    }

    public function getPercentAttribute()
    {
        if ($this->isCdr()) {
            return round(($this->attributes['rate_95th'] / $this->attributes['bill_cdr']) * 200, 2);
        } elseif ($this->isQuota()) {
            return round(($this->attributes['total_data'] / $this->attributes['bill_quota']) * 100, 2);
        }
    }

    public function getOveruseAttribute()
    {
        if ($this->isCdr()) {
            $overuse = $this->attributes['rate_95th'] - $this->attributes['bill_cdr'];
            return (($overuse <= 0) ? "-" : format_si($overuse));
        } elseif ($this->isQuota()) {
            $overuse = $this->attributes['total_data'] - $this->attributes['bill_quota'];
            return (($overuse <= 0) ? "-" : format_si($overuse));
        }
    }

    // ---- Define Relationships ----

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     */
    public function ports()
    {
        return $this->belongsToMany('App\Models\Port', 'bill_ports', 'bill_id', 'port_id');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function history()
    {
        return $this->hasMany('App\Models\Bill\BillHistory', 'bill_id');
    }
    
    // ---- Helper methods for model ----

    /**
     *  @return boolean
     */
    private function isCdr()
    {
        return $this->attributes['bill_type'] == "cdr";
    }

    /**
     *  @return boolean
     */
    private function isQuota()
    {
        return $this->attributes['bill_type'] == "quota";
    }
}
