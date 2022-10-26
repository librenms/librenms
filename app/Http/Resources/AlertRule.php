<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $devices
 * @property string $groups
 * @property string $locations
 */
class AlertRule extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $rule = parent::toArray($request);
        $rule['devices'] = $this->devices->pluck('device_id')->all();
        $rule['groups'] = $this->groups->pluck('id')->all();
        $rule['locations'] = $this->locations->pluck('id')->all();

        return $rule;
    }
}
