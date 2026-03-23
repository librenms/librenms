<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property \App\Models\Device $devices
 * @property \App\Models\DeviceGroup $groups
 * @property \App\Models\Location $locations
 */
class AlertRule extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function toArray($request): array
    {
        $this->loadMissing('alertOperation:id,default_operation_step_duration_seconds');

        $rule = parent::toArray($request);
        $rule['devices'] = $this->devices->pluck('device_id')->all();
        $rule['groups'] = $this->groups->pluck('id')->all();
        $rule['locations'] = $this->locations->pluck('id')->all();
        $rule['alert_operation_id'] = $this->alert_operation_id;
        $rule['default_operation_step_duration_seconds'] = $this->alertOperation?->default_operation_step_duration_seconds;
        $rule['operations'] = $this->toOperationsApiArray();

        return $rule;
    }
}
