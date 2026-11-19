<?php

namespace App\Models;

use App\Observers\DeviceRelationshipObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Device dependency (parent/child) pivot
 *
 * @property int $parent_device_id
 * @property int $child_device_id
 */
#[ObservedBy([DeviceRelationshipObserver::class])]
class DeviceRelationship extends Pivot
{
    protected $table = 'device_relationships';
    public $timestamps = false;
}
