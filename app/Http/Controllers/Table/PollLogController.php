<?php

namespace App\Http\Controllers\Table;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\PollerCluster;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use LibreNMS\Util\Time;
use LibreNMS\Util\Url;

/**
 * @extends TableController<Device>
 */
class PollLogController extends TableController
{
    /** @var array<string, string> */
    protected array $default_sort = ['last_polled_timetaken' => 'desc'];

    /** @return array<string, string> */
    protected function rules(): array
    {
        return [
            'type' => 'nullable|string',
            'sort' => 'array',
        ];
    }

    protected function baseQuery(Request $request): Builder
    {
        $this->authorize('viewAny', PollerCluster::class);

        $user = $request->user();
        $query = Device::query()
            ->select('devices.*', 'poller_groups.group_name')
            ->leftJoin('locations', 'devices.location_id', '=', 'locations.id')
            ->leftJoin('poller_groups', 'devices.poller_group', '=', 'poller_groups.id')
            ->with('location')
            ->isActive()
            ->where('devices.status', 1)
            ->when($user->cannot('viewAll', Device::class), fn (Builder $query) => $query->whereAttachedTo($user, 'users'));

        if ($request->input('type') === 'unpolled') {
            $overdue = (int) (LibrenmsConfig::get('rrd.step', 300) * 1.2);
            $query->whereRaw('devices.last_polled <= NOW() - INTERVAL ? SECOND', [$overdue]);
        }

        return $query;
    }

    /** @return array<string, string> */
    protected function sortFields(Request $request): array
    {
        return [
            'hostname' => 'devices.hostname',
            'last_polled' => 'devices.last_polled',
            'last_polled_timetaken' => 'devices.last_polled_timetaken',
            'poller_group' => 'devices.poller_group',
            'location' => 'locations.location',
        ];
    }

    /**
     * @param  Builder<Device>  $query
     * @param  array<int|string, mixed>  $fields
     * @return Builder<Device>
     */
    protected function search(?string $search, Builder $query, array $fields): Builder
    {
        if ($search) {
            $like = "%$search%";

            $query->where(fn (Builder $query) => $query
                ->whereLike('devices.hostname', $like)
                ->orWhereLike('devices.sysName', $like)
                ->orWhereLike('devices.last_polled_timetaken', $like)
                ->orWhereRaw(
                    'COALESCE(CONVERT_TZ(devices.last_polled, @@session.time_zone, ?), devices.last_polled) LIKE ?',
                    [session('preferences.timezone'), $like],
                ));
        }

        return $query;
    }

    /**
     * @param  Device  $model
     */
    public function formatItem(Model $model): array
    {
        $location = $model->location?->location;

        return [
            'hostname' => Url::deviceLink($model, null, route('device.graphs', ['device' => $model->device_id, 'group' => 'poller'])),
            'last_polled' => $model->last_polled ? Time::format($model->last_polled, 'compact') : '',
            'poller_group' => e($model->getAttribute('group_name') ?: 'General'),
            'location' => $location === null ? null : e($location),
            'last_polled_timetaken' => round((float) $model->last_polled_timetaken, 2),
        ];
    }
}
