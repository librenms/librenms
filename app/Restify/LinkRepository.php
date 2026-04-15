<?php

namespace App\Restify;

use App\Models\Link;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class LinkRepository extends Repository
{
    public static string $model = Link::class;

    public static string $title = 'remote_hostname';




    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
        ];
    }

    public static function searchables(): array
    {
        return [
            'remoteHostname' => SearchableFilter::make()->setColumn('remote_hostname'),
        ];
    }

    public static function matches(): array
    {
        return [
            'isActive' => MatchFilter::make()->setType('bool')->setColumn('active'),
            'protocol' => MatchFilter::make()->setType('text')->setColumn('protocol'),
            'remoteHostname' => MatchFilter::make()->setType('text')->setColumn('remote_hostname'),
            'remotePortName' => MatchFilter::make()->setType('text')->setColumn('remote_port'),
            'remotePlatform' => MatchFilter::make()->setType('text')->setColumn('remote_platform'),
            'remoteVersion' => MatchFilter::make()->setType('text')->setColumn('remote_version'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'isActive' => SortableFilter::make()->setColumn('active'),
            'protocol' => SortableFilter::make()->setColumn('protocol'),
            'remoteHostname' => SortableFilter::make()->setColumn('remote_hostname'),
            'remotePortName' => SortableFilter::make()->setColumn('remote_port'),
            'remotePlatform' => SortableFilter::make()->setColumn('remote_platform'),
            'remoteVersion' => SortableFilter::make()->setColumn('remote_version'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('isActive', fn ($value, $model) => $model->active)->readonly(),
            field('protocol')->readonly(),
            field('remoteHostname', fn ($value, $model) => $model->remote_hostname)->readonly(),
            field('remotePortName', fn ($value, $model) => $model->remote_port)->readonly(),
            field('remotePlatform', fn ($value, $model) => $model->remote_platform)->readonly(),
            field('remoteVersion', fn ($value, $model) => $model->remote_version)->readonly(),
        ];
    }

    /**
     * Link extends plain Model (no hasAccess scope), so we filter by device access on local_device_id.
     * TODO: Discuss if this should be scoped by hasAccess on device/port instead of custom filtering.
     */
    public static function indexQuery(RestifyRequest $request, Builder|Relation $query)
    {
        if ($user = $request->user()) {
            if (Gate::allows('viewAny', Link::class)) {
                return $query;
            }

            return $query->whereIntegerInRaw('local_device_id', \Permissions::devicesForUser($user));
        }

        return $query->whereRaw('1 = 0');
    }

    public static function showQuery(RestifyRequest $request, Builder|Relation $query)
    {
        return static::indexQuery($request, $query);
    }

    /**
     * Links are discovered automatically by LibreNMS via CDP/LLDP not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * Links are managed by the LibreNMS discovery process they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
