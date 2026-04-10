<?php

namespace App\Restify;

use App\Models\Availability;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AvailabilityRepository extends Repository
{
    public static string $model = Availability::class;

    public static string $id = 'availability_id';

    public static string $title = 'duration';

    public function fields(RestifyRequest $request): array
    {
        return [
            field('device_id')->readonly(),
            field('duration')->readonly(),
            field('availability_perc')->readonly(),
        ];
    }

    /**
     * Availability extends plain Model (no hasAccess scope), so we filter by device access on device_id.
     * TODO: Discuss if this should be scoped by hasAccess on device/port instead of custom filtering.
     */
    public static function indexQuery(RestifyRequest $request, Builder|Relation $query)
    {
        if ($user = $request->user()) {
            if (Gate::allows('viewAny', Availability::class)) {
                return $query;
            }

            return $query->whereIntegerInRaw('device_id', \Permissions::devicesForUser($user));
        }

        return $query->whereRaw('1 = 0');
    }

    public static function showQuery(RestifyRequest $request, Builder|Relation $query)
    {
        return static::indexQuery($request, $query);
    }

    /**
     * Availability records are calculated automatically by LibreNMS during polling — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * Availability records are managed by the LibreNMS polling process.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
