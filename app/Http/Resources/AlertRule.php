<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\AlertRule
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
        /** @var \App\Models\AlertRule $alertRule */
        $alertRule = $this->resource;
        $alertRule->load('alertOperation:id,default_operation_step_duration_seconds');

        $rule = parent::toArray($request);
        $rule['devices'] = $alertRule->devices->pluck('device_id')->all();
        $rule['groups'] = $alertRule->groups->pluck('id')->all();
        $rule['locations'] = $alertRule->locations->pluck('id')->all();
        $rule['alert_operation_id'] = $alertRule->alert_operation_id;
        $rule['default_operation_step_duration_seconds'] = $alertRule->alertOperation?->default_operation_step_duration_seconds;
        $rule['operations'] = $alertRule->toOperationsApiArray();

        return $rule;
    }
}
