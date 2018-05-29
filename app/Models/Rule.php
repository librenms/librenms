<?php

/**
 * app/Models/Alerting/Rule.php
 *
 * Model for access to alert_rules table data
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
 * @copyright  2016 Neil Lathwood
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Alerting\Rule
 *
 * @property integer $id
 * @property string $device_id
 * @property string $rule
 * @property string $severity
 * @property string $extra
 * @property boolean $disabled
 * @property string $name
 * @property string $proc
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Alerting\Alert[] $alert
 * @property-read \App\Models\Device $device
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Alerting\Rule whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Alerting\Rule whereDeviceId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Alerting\Rule whereRule($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Alerting\Rule whereSeverity($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Alerting\Rule whereExtra($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Alerting\Rule whereDisabled($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Alerting\Rule whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Alerting\Rule whereProc($value)
 * @mixin \Eloquent
 * @property string $query
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Alerting\Rule whereQuery($value)
 */
class Rule extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'alert_rules';
    /**
     * The primary key column name.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    protected $fillable = [
        "extra",
        "disabled",
        "severity",
        "rule",
        "device_id",
    ];

    public function setDisabledAttribute($value)
    {
        $this->attributes['mute'] = (bool)$value;
    }
    // ---- Define Relationships ----
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function alert()
    {
        return $this->hasMany('App\Models\Alerting\Alert', 'rule_id');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function device()
    {
        return $this->belongsTo('App\Models\Device', 'device_id');
    }
}
