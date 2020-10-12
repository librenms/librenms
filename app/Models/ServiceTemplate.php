<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use LibreNMS\Interfaces\Models\Keyable;

class ServiceTemplate extends DeviceRelatedModel  implements Keyable
{
    public $timestamps = false;
    protected $primaryKey = 'service_template_id';
    protected $fillable = [
        'service_template_id',
        'device_group_id',
        'service_template_ip',
        'service_template_type',
        'service_template_desc',
        'service_template_param',
        'service_template_ignore',
        'service_template_status',
        'service_template_changed',
        'service_template_message',
        'service_template_disabled',
        'service_template_ds',
    ];

    // ---- Helper Functions ----

    public function getCompositeKey()
    {
        return $this->service_template_id . '-' . $this->device_group_id;
    }

    // ---- Query Scopes ----

    /**
     * @param Builder $query
     * @return Builder
     */
    public function getServiceTemplate($query, $service_template_id)
    {
        return $query->where([
            ['service_template_id', '=', $service_template_id],
        ]);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function disoverServiceTemplate($query, $device_group, $service_template)
    {
        return $query->where([
            ['service_ignore', '=', 0],
            ['service_disabled', '=', 0],
            ['service_status', '=', 2],
        ]);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function deleteServiceTemplate($query, $service_template_id)
    {
        return $query->where([
            ['service_ignore', '=', 0],
            ['service_disabled', '=', 0],
            ['service_status', '=', 1],
        ]);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function removeServiceTemplate($query, $service_template_id)
    {
        return $query->where([
            ['service_ignore', '=', 1],
            ['service_disabled', '=', 0],
        ]);
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeIsDisabled($query)
    {
        return $query->where('service_disabled', 1);
    }
}
