<?php

namespace App\Restify;

use App\Models\Ipv6Nd;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class Ipv6NdRepository extends Repository
{
    public static string $model = Ipv6Nd::class;

    public static string $title = 'ipv6_address';




    public static function searchables(): array
    {
        return [
            'macAddress' => SearchableFilter::make()->setColumn('mac_address'),
            'ipv6Address' => SearchableFilter::make()->setColumn('ipv6_address'),
        ];
    }

    public static function matches(): array
    {
        return [
            'macAddress' => MatchFilter::make()->setType('text')->setColumn('mac_address'),
            'ipv6Address' => MatchFilter::make()->setType('text')->setColumn('ipv6_address'),
            'contextName' => MatchFilter::make()->setType('text')->setColumn('context_name'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'macAddress' => SortableFilter::make()->setColumn('mac_address'),
            'ipv6Address' => SortableFilter::make()->setColumn('ipv6_address'),
            'contextName' => SortableFilter::make()->setColumn('context_name'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('macAddress', fn ($value, $model) => $model->mac_address)->readonly(),
            field('ipv6Address', fn ($value, $model) => $model->ipv6_address)->readonly(),
            field('contextName', fn ($value, $model) => $model->context_name)->readonly(),
        ];
    }

    /**
     * Ipv6Nd extends plain Model (no hasAccess scope), so we filter by device access on device_id.
     * TODO: Discuss if this should be scoped by hasAccess on device/port instead of custom filtering.
     */
    public static function indexQuery(RestifyRequest $request, Builder|Relation $query)
    {
        if ($user = $request->user()) {
            if (Gate::allows('viewAny', Ipv6Nd::class)) {
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
     * IPv6 neighbor discovery entries are populated automatically by LibreNMS during polling not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * IPv6 neighbor discovery entries are managed by the LibreNMS polling process they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
