<?php

namespace App\Observers;

use App\Models\Device;
use App\Models\DeviceRelationship;

class DeviceRelationshipObserver
{
    /**
     * A parent was attached to a child, update the dependency depths
     */
    public function created(DeviceRelationship $relationship): void
    {
        // the parent might have been standalone before
        Device::find($relationship->parent_device_id)?->validateStandalone();

        // the child is now one level below its deepest parent
        Device::find($relationship->child_device_id)?->updateMaxDepth();
    }

    /**
     * A parent was detached from a child, update the dependency depths
     */
    public function deleted(DeviceRelationship $relationship): void
    {
        // the parent might be standalone now
        Device::find($relationship->parent_device_id)?->validateStandalone();

        // the child might have a shallower deepest parent or be a root now
        Device::find($relationship->child_device_id)?->updateMaxDepth();
    }
}
