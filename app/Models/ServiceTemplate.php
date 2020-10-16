<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use LibreNMS\Interfaces\Models\Keyable;

class ServiceTemplate extends Model implements Keyable
{
    public $timestamps = false;
    protected $primaryKey = 'id';
    protected $fillable = [
        'id',
        'device_group_id',
        'ip',
        'type',
        'desc',
        'param',
        'ignore',
        'status',
        'changed',
        'disabled',
        'name',
    ];

    // ---- Helper Functions ----

    public function getCompositeKey()
    {
        return $this->id . '-' . $this->device_group_id;
    }

    // ---- Query Scopes ----

    /**
     * @param Builder $query
     * @return Builder
     */
    public function getServiceTemplate($id)
    {
        return $id->where([
            ['id', '=', $id],
        ]);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeIsDisabled($query)
    {
        return $query->where('disabled', 1);
    }
}
