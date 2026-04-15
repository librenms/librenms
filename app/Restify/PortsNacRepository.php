<?php

namespace App\Restify;

use App\Models\PortsNac;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class PortsNacRepository extends Repository
{
    use PortScopedRepository;

    public static string $model = PortsNac::class;

    public static string $id = 'ports_nac_id';

    public static string $title = 'username';




    public static function searchables(): array
    {
        return [
            'username' => SearchableFilter::make()->setColumn('username'),
            'macAddress' => SearchableFilter::make()->setColumn('mac_address'),
            'ipAddress' => SearchableFilter::make()->setColumn('ip_address'),
        ];
    }

    public static function matches(): array
    {
        return [
            'authenticationId' => MatchFilter::make()->setType('text')->setColumn('auth_id'),
            'domain' => MatchFilter::make()->setType('text')->setColumn('domain'),
            'username' => MatchFilter::make()->setType('text')->setColumn('username'),
            'macAddress' => MatchFilter::make()->setType('text')->setColumn('mac_address'),
            'ipAddress' => MatchFilter::make()->setType('text')->setColumn('ip_address'),
            'vlan' => MatchFilter::make()->setType('integer')->setColumn('vlan'),
            'hostMode' => MatchFilter::make()->setType('text')->setColumn('host_mode'),
            'authorizationStatus' => MatchFilter::make()->setType('text')->setColumn('authz_status'),
            'authorizedBy' => MatchFilter::make()->setType('text')->setColumn('authz_by'),
            'authenticationStatus' => MatchFilter::make()->setType('text')->setColumn('authc_status'),
            'method' => MatchFilter::make()->setType('text')->setColumn('method'),
            'timeout' => MatchFilter::make()->setType('integer')->setColumn('timeout'),
            'timeLeft' => MatchFilter::make()->setType('integer')->setColumn('time_left'),
            'timeElapsed' => MatchFilter::make()->setType('integer')->setColumn('time_elapsed'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'authenticationId' => SortableFilter::make()->setColumn('auth_id'),
            'domain' => SortableFilter::make()->setColumn('domain'),
            'username' => SortableFilter::make()->setColumn('username'),
            'macAddress' => SortableFilter::make()->setColumn('mac_address'),
            'ipAddress' => SortableFilter::make()->setColumn('ip_address'),
            'vlan' => SortableFilter::make()->setColumn('vlan'),
            'hostMode' => SortableFilter::make()->setColumn('host_mode'),
            'authorizationStatus' => SortableFilter::make()->setColumn('authz_status'),
            'authorizedBy' => SortableFilter::make()->setColumn('authz_by'),
            'authenticationStatus' => SortableFilter::make()->setColumn('authc_status'),
            'method' => SortableFilter::make()->setColumn('method'),
            'timeout' => SortableFilter::make()->setColumn('timeout'),
            'timeLeft' => SortableFilter::make()->setColumn('time_left'),
            'timeElapsed' => SortableFilter::make()->setColumn('time_elapsed'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('authenticationId', fn ($value, $model) => $model->auth_id)->readonly(),
            field('domain')->readonly(),
            field('username')->readonly(),
            field('macAddress', fn ($value, $model) => $model->mac_address)->readonly(),
            field('ipAddress', fn ($value, $model) => $model->ip_address)->readonly(),
            field('vlan')->readonly(),
            field('hostMode', fn ($value, $model) => $model->host_mode)->readonly(),
            field('authorizationStatus', fn ($value, $model) => $model->authz_status)->readonly(),
            field('authorizedBy', fn ($value, $model) => $model->authz_by)->readonly(),
            field('authenticationStatus', fn ($value, $model) => $model->authc_status)->readonly(),
            field('method')->readonly(),
            field('timeout')->readonly(),
            field('timeLeft', fn ($value, $model) => $model->time_left)->readonly(),
            field('timeElapsed', fn ($value, $model) => $model->time_elapsed)->readonly(),
        ];
    }

    /**
     * NAC entries are discovered automatically by LibreNMS during the polling process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * NAC entries are managed by the LibreNMS polling process.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
