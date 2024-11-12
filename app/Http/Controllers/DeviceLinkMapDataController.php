<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Maps\MapDataController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use LibreNMS\Config;
use LibreNMS\Util\Url;

class DeviceLinkMapDataController extends MapDataController
{
    public function getDeviceLinks(Request $request): JsonResponse
    {
        // List all links
        $link_list = [];
        $port_assoc_seen = [];
        $link_types = $request->link_types;

        foreach ($link_types as $link_type) {
            if ($link_type == 'mac') {
                $remote_port_attr = 'macLinkedPorts';
            } elseif ($link_type == 'xdp') {
                $remote_port_attr = 'xdpLinkedPorts';
            } else {
                Log::error("Link types of $link_type are not supported");
                abort(500);
            }

            foreach ($this->portsWithLinks($request, $remote_port_attr) as $port) {
                // Ignore any entries if the device has not been loaded (filtered out)
                if (! $port->device) {
                    continue;
                }

                foreach ($port->{$remote_port_attr} as $remote_port) {
                    // Ignore any entries if the device has not been loaded (filtered out)
                    if (! $remote_port->device) {
                        continue;
                    }

                    if ($port->port_id < $remote_port->port_id) {
                        $port_ids = $port->port_id . '.' . $remote_port->port_id;
                    } else {
                        $port_ids = $remote_port->port_id . '.' . $port->port_id;
                    }

                    // Ignore any associations that have already been processed
                    if (array_key_exists($port_ids, $port_assoc_seen)) {
                        continue;
                    }
                    $port_assoc_seen[$port_ids] = true;

                    $width = $this->linkSpeedWidth($port->ifSpeed);

                    if ($port->device->status == 0 && $remote_port->device->status == 0) {
                        // If both devices are offline, mark the link as being down
                        $link_style = [
                            'dashes' => [8, 12],
                            'width' => $width,
                            'color' => [
                                'border' => Config::get('network_map_legend.dn.border'),
                                'highlight' => Config::get('network_map_legend.dn.edge'),
                                'color' => Config::get('network_map_legend.dn.edge'),
                            ],
                        ];
                    } elseif ($port->ifOperStatus == 'down' || $remote_port->ifOperStatus == 'down') {
                        // If either port is offline, mark the link as being down
                        $link_style = [
                            'dashes' => [8, 12],
                            'width' => $width,
                            'color' => [
                                'border' => Config::get('network_map_legend.dn.border'),
                                'highlight' => Config::get('network_map_legend.dn.edge'),
                                'color' => Config::get('network_map_legend.dn.edge'),
                            ],
                        ];
                    } else {
                        if ($port->ifSpeed > 0) {
                            $link_in_usage_pct = $port->ifInOctets_rate * 8 / $port->ifSpeed * 100;
                            $link_out_usage_pct = $port->ifOutOctets_rate * 8 / $port->ifSpeed * 100;
                            $link_used = max($link_out_usage_pct, $link_in_usage_pct);
                        } else {
                            $link_used = 0;
                        }
                        $link_color = $this->linkUseColour($link_used);
                        $link_style = [
                            'width' => $width,
                            'color' => [
                                'border' => $link_color,
                                'highlight' => $link_color,
                                'color' => $link_color,
                            ],
                        ];
                    }

                    $link_list[$port->port_id . '.' . $remote_port->port_id] = [
                        'ldev' => $port->device_id,
                        'rdev' => $remote_port->device_id,
                        'ifnames' => $port->ifName . ' <> ' . $remote_port->ifName,
                        'url' => Url::portLink($port, null, null, false, true),
                        'style' => $link_style,
                    ];
                }
            }
        }

        return response()->json($link_list);
    }

    protected function portsWithLinks(Request $request, string $remote_port_attr)
    {
        $user = $request->user();
        $disabled = $request->disabled;
        $disabled_alerts = $request->disabled_alerts;
        $group_id = $request->group;
        $device_id = $request->device;

        $device_filter = ! is_null($disabled) || ! is_null($disabled_alerts) || $group_id || ! $user->hasGlobalRead();

        $linkQuery = Port::hasAccess($request->user())
            ->with([
                $remote_port_attr,
                'device' => $this->filterDevices($user, $disabled, $disabled_alerts, $group_id),
                "$remote_port_attr.device" => $this->filterDevices($user, $disabled, $disabled_alerts, $group_id)])
            ->whereHas($remote_port_attr);

        if ($device_filter) {
            // Apply device level filter to the port list so we exclude ports that are not connected to devices we want to display
            $linkQuery->whereHas('device', $this->filterDevices($user, $disabled, $disabled_alerts, $group_id));

            // Apply the same device level filter to the port list so we exclude ports that have no remote devices we want to display
            $linkQuery->whereHas("$remote_port_attr.device", $this->filterDevices($user, $disabled, $disabled_alerts, $group_id));
        }

        if ($device_id) {
            // If we have a device ID, we want to show if we are the source or target of a link
            $linkQuery->where(function ($q) use ($device_id, $remote_port_attr) {
                $q->whereHas($remote_port_attr, function ($q) use ($device_id) {
                    $q->where('device_id', $device_id);
                })
                    ->orWhereHas('device', function ($q) use ($device_id) {
                        $q->where('device_id', $device_id);
                    });
            });
        }

        return $linkQuery->get();
    }
}
