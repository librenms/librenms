<?php

namespace App\Http\Controllers\Table;

use App\Facades\LibrenmsConfig;
use App\Models\Device;
use App\Models\DevicePerm;
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

        $query = Device::query()
            ->select('devices.*', 'locations.location as poll_location', 'poller_groups.group_name')
            ->leftJoin('locations', 'devices.location_id', '=', 'locations.id')
            ->leftJoin('poller_groups', 'devices.poller_group', '=', 'poller_groups.id')
            ->isActive()
            ->where('devices.status', 1);

        $user = $request->user();
        if (! $user->can('viewAll', Device::class)) {
            $query->whereIn('devices.device_id', DevicePerm::where('user_id', $user->getKey())->select('device_id'));
        }

        if ($request->input('type') === 'unpolled') {
            $overdue = (int) (LibrenmsConfig::get('rrd.step', 300) * 1.2);
            $query->whereRaw('devices.last_polled <= DATE_SUB(NOW(), INTERVAL ? SECOND)', [$overdue]);
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

            $query->where(function (Builder $query) use ($like): void {
                $query->where('devices.hostname', 'like', $like)
                    ->orWhere('devices.sysName', 'like', $like)
                    ->orWhereRaw('COALESCE(CONVERT_TZ(devices.last_polled, @@session.time_zone, ?), devices.last_polled) LIKE ?', [session('preferences.timezone'), $like])
                    ->orWhere('devices.last_polled_timetaken', 'like', $like);
            });
        }

        return $query;
    }

    /**
     * @param  Device  $model
     */
    public function formatItem(Model $model): array
    {
        $location = $model->getAttribute('poll_location');

        return [
            'hostname' => Url::deviceLink($model, null, ['tab' => 'graphs', 'group' => 'poller']),
            'last_polled' => $model->last_polled ? Time::format($model->last_polled, 'compact') : '',
            'poller_group' => e($model->getAttribute('group_name') ?: 'General'),
            'location' => $location === null ? null : e($location),
            'last_polled_timetaken' => round((float) $model->last_polled_timetaken, 2),
        ];
    }
}
