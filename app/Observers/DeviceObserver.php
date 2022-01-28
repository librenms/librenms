<?php

namespace App\Observers;

use App;
use App\ApiClients\Oxidized;
use App\Models\Device;
use Illuminate\Support\Str;
use Log;

class DeviceObserver
{
    /**
     * Handle the device "created" event.
     *
     * @param  \App\Models\Device  $device
     * @return void
     */
    public function created(Device $device, Oxidized $oxidized): void
    {
        Log::event("Device $device->hostname has been created", $device, 'system', 3);
        $oxidized->reloadNodes();
    }

    /**
     * Handle the device "updated" event.
     *
     * @param  \App\Models\Device  $device
     * @return void
     */
    public function updated(Device $device): void
    {
        // handle device dependency updates
        if ($device->isDirty('max_depth')) {
            $device->children->each->updateMaxDepth();
        }

        // log up/down status changes
        if ($device->isDirty(['status', 'status_reason'])) {
            $type = $device->status ? 'up' : 'down';
            $reason = $device->status ? $device->getOriginal('status_reason') : $device->status_reason;
            Log::event('Device status changed to ' . ucfirst($type) . " from $reason check.", $device, $type);
        }

        // key attribute changes
        foreach (['os', 'sysName', 'version', 'hardware', 'features', 'serial', 'icon', 'type', 'ip'] as $attribute) {
            if ($device->isDirty($attribute)) {
                Log::event(self::attributeChangedMessage($attribute, $device->$attribute, $device->getOriginal($attribute)), $device, 'system', 3);
            }
        }
        if ($device->isDirty('location_id')) {
            Log::event(self::attributeChangedMessage('location', (string) $device->location, null), $device, 'system', 3);
        }
    }

    /**
     * Handle the device "deleted" event.
     */
    public function deleted(Device $device, Oxidized $oxidized): void
    {
        // delete rrd files
        $host_dir = addcslashes(escapeshellarg(\Rrd::dirFromHost($device->hostname)), '\'');
        $result = trim(shell_exec("bash -c '( [ ! -d $host_dir ] || rm -vrf $host_dir 2>&1 ) && echo -n OK'"));
        if (! Str::endsWith($result, 'OK')) {
            Log::debug("Could not $device->hostname files: $result");
        }

        Log::event("Device $device->hostname has been removed", 0, 'system', 3);

        $oxidized->reloadNodes();
    }

    /**
     * Handle the device "deleting" event.
     *
     * @param  \App\Models\Device  $device
     * @return void
     */
    public function deleting(Device $device): void
    {
        // prevent abort from the webserver
        if (App::runningInConsole() === false) {
            ignore_user_abort(true);
            set_time_limit(0);
        }

        // delete related data
        $device->accessPoints()->delete();
        $device->alerts()->delete();
        \DB::table('alert_device_map')->where('device_id', $device->device_id)->delete();
        $device->alertLogs()->delete();
        $device->applications()->delete();
        $device->attribs()->delete();
        $device->availability()->delete();
        $device->bgppeers()->delete();
        \DB::table('bgpPeers_cbgp')->where('device_id', $device->device_id)->delete();
        $device->cefSwitching()->delete();
        \DB::table('ciscoASA')->where('device_id', $device->device_id)->delete();
        $device->components()->delete();
        \DB::table('customoids')->where('device_id', $device->device_id)->delete();
        \DB::table('devices_perms')->where('device_id', $device->device_id)->delete();
        $device->graphs()->delete();
        \DB::table('device_group_device')->where('device_id', $device->device_id)->delete();
        $device->diskIo()->delete();
        $device->entityState()->delete();
        $device->entityPhysical()->delete();
        \DB::table('entPhysical_state')->where('device_id', $device->device_id)->delete();
        $device->eventlogs()->delete();
        $device->hostResources()->delete();
        $device->hostResourceValues()->delete();
        $device->ipsecTunnels()->delete();
        $device->ipv4()->delete();
        $device->ipv6()->delete();
        $device->isisAdjacencies()->delete();
        $device->isisAdjacencies()->delete();
        $device->macs()->delete();
        $device->mefInfo()->delete();
        $device->mempools()->delete();
        $device->mplsLsps()->delete();
        $device->mplsLspPaths()->delete();
        $device->mplsSaps()->delete();
        $device->mplsSdps()->delete();
        $device->mplsSdpBinds()->delete();
        $device->mplsServices()->delete();
        $device->mplsTunnelArHops()->delete();
        $device->mplsTunnelCHops()->delete();
        $device->muninPlugins()->delete();
        \DB::table('netscaler_vservers')->where('device_id', $device->device_id)->delete();
        $device->ospfAreas()->delete();
        $device->ospfInstances()->delete();
        $device->ospfNbrs()->delete();
        $device->ospfPorts()->delete();
        $device->outages()->delete();
        $device->packages()->delete();
        $device->portsFdb()->delete();
        $device->portsNac()->delete();
        \DB::table('ports_stack')->where('device_id', $device->device_id)->delete();
        $device->portsStp()->delete();
        $device->portsVlan()->delete();
        $device->printerSupplies()->delete();
        $device->processes()->delete();
        $device->processors()->delete();
        $device->pseudowires()->delete();
        $device->routes()->delete();
        $device->rServers()->delete();
        $device->sensors()->delete();  // delete sensor state indexes first?
        $device->services()->delete();
        \DB::table('service_templates_device')->where('device_id', $device->device_id)->delete();
        $device->syslogs()->delete();
        $device->tnmsNeInfo()->delete();
        $device->vlans()->delete();
        $device->vminfo()->delete();
        $device->vrfs()->delete();
        $device->vrfLites()->delete();
        $device->vServers()->delete();
        $device->wirelessSensors()->delete();

        $device->ports()
            ->select(['port_id', 'device_id', 'ifIndex', 'ifName', 'ifAlias', 'ifDescr'])
            ->chunk(100, function ($ports) {
                foreach ($ports as $port) {
                    $port->delete();
                }
            });

        // handle device dependency updates
        $device->children->each->updateMaxDepth($device->device_id);
    }

    /**
     * Handle the device "Pivot Attached" event.
     *
     * @param  \App\Models\Device  $device
     * @param  string  $relationName  parents or children
     * @param  array  $pivotIds  list of pivot ids
     * @param  array  $pivotIdsAttributes  additional pivot attributes
     * @return void
     */
    public function pivotAttached(Device $device, $relationName, $pivotIds, $pivotIdsAttributes)
    {
        if ($relationName == 'parents') {
            // a parent attached to this device

            // update the parent's max depth incase it used to be standalone
            Device::whereIn('device_id', $pivotIds)->get()->each->validateStandalone();

            // make sure this device's max depth is updated
            $device->updateMaxDepth();
        } elseif ($relationName == 'children') {
            // a child device attached to this device

            // if this device used to be standalone, we need to udpate max depth
            $device->validateStandalone();

            // make sure the child's max depth is updated
            Device::whereIn('device_id', $pivotIds)->get()->each->updateMaxDepth();
        }
    }

    public static function attributeChangedMessage($attribute, $value, $previous)
    {
        return trans("device.attributes.$attribute") . ': '
            . (($previous && $previous != $value) ? "$previous -> " : '')
            . $value;
    }
}
