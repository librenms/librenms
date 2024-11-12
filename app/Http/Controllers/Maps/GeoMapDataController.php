<?php

namespace App\Http\Controllers\Maps;

use App\Models\Link;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GeoMapDataController extends MapDataController
{
    public function getGeographicLinks(Request $request): JsonResponse
    {
        // List all links
        $link_list = [];
        foreach ($this->geoLinks($request) as $location) {
            $link = $location[0];
            $capacity = $location->sum(function (Link $l) {
                return $l->port->ifSpeed;
            });
            $inRate = $location->sum(function (Link $l) {
                return $l->port->ifInOctets_rate * 8;
            });
            $outRate = $location->sum(function (Link $l) {
                return $l->port->ifOutOctets_rate * 8;
            });
            if ($capacity > 0) {
                $link_used = max($inRate / $capacity * 100, $outRate / $capacity * 100);
            } elseif ($inRate > 0 || $outRate > 0) {
                $link_used = 100;
            } else {
                $link_used = 0;
            }

            $width = $this->linkSpeedWidth($capacity);
            $link_color = $this->linkUseColour($link_used);

            $link_list[$link->device->location_id . '.' . $link->remoteDevice->location_id] = [
                'local_lat' => $link->device->location->lat,
                'local_lng' => $link->device->location->lng,
                'remote_lat' => $link->remoteDevice->location->lat,
                'remote_lng' => $link->remoteDevice->location->lng,
                'color' => $link_color,
                'width' => $width,
            ];
        }

        return response()->json($link_list);
    }

    protected function geoLinks(Request $request)
    {
        $user = $request->user();

        // Return a blank array for unknown link types
        if ($request->link_type != 'xdp') {
            return collect();
        }

        $linkQuery = Link::with('port', 'device', 'remoteDevice', 'device.location', 'remoteDevice.location')
            ->whereHas('device', function (Builder $q) use ($user) {
                $q->whereIn('status', [0, 1])
                    ->where('disabled', 0)
                    ->where('ignore', 0);

                if (! $user->hasGlobalRead()) {
                    $q->whereIntegerInRaw('device_id', \Permissions::devicesForUser($user));
                }
            })
            ->whereHas('device.location', function (Builder $q) {
                $q->whereNotNull('lat')
                    ->whereNotNull('lng');
            })
            ->whereHas('remoteDevice', function (Builder $q) use ($user) {
                $q->whereIn('status', [0, 1])
                    ->where('disabled', 0)
                    ->where('ignore', 0);

                if (! $user->hasGlobalRead()) {
                    $q->whereIntegerInRaw('device_id', \Permissions::devicesForUser($user));
                }
            })
            ->whereHas('remoteDevice.location', function (Builder $q) {
                $q->whereNotNull('lat')
                    ->whereNotNull('lng');
            })
            ->whereHas('port', function (Builder $q) {
                $q->where('ifOperStatus', 'up');
            });

        $group_id = $request->group;
        if ($group_id) {
            $linkQuery->whereHas('remoteDevice', function ($q) use ($group_id) {
                $q->whereIn('device_id', function ($q) use ($group_id) {
                    $q->select('device_id')
                        ->from('device_group_device')
                        ->where('device_group_id', $group_id);
                });
            })
                ->whereHas('device', function ($q) use ($group_id) {
                    $q->whereIn('device_id', function ($q) use ($group_id) {
                        $q->select('device_id')
                            ->from('device_group_device')
                            ->where('device_group_id', $group_id);
                    });
                });
        }

        return $linkQuery->get()
            ->groupBy(function (Link $i) {
                return $i->device->location->lat . '.' . $i->device->location->lng . '.' . $i->remoteDevice->location->lat . '.' . $i->remoteDevice->location->lng;
            });
    }
}
