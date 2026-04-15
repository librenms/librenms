<?php

namespace App\Restify;

use App\Models\PortSecurity;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class PortSecurityRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = PortSecurity::class;

    public static string $title = 'last_mac_address';




    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
        ];
    }

    public static function searchables(): array
    {
        return [
            'lastMacAddress' => SearchableFilter::make()->setColumn('last_mac_address'),
        ];
    }

    public static function matches(): array
    {
        return [
            'isEnabled' => MatchFilter::make()->setType('bool')->setColumn('port_security_enable'),
            'status' => MatchFilter::make()->setType('text')->setColumn('status'),
            'maxAddresses' => MatchFilter::make()->setType('integer')->setColumn('max_addresses'),
            'addressCount' => MatchFilter::make()->setType('integer')->setColumn('address_count'),
            'violationAction' => MatchFilter::make()->setType('text')->setColumn('violation_action'),
            'violationCount' => MatchFilter::make()->setType('integer')->setColumn('violation_count'),
            'lastMacAddress' => MatchFilter::make()->setType('text')->setColumn('last_mac_address'),
            'isStickyEnabled' => MatchFilter::make()->setType('bool')->setColumn('sticky_enable'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'isEnabled' => SortableFilter::make()->setColumn('port_security_enable'),
            'status' => SortableFilter::make()->setColumn('status'),
            'maxAddresses' => SortableFilter::make()->setColumn('max_addresses'),
            'addressCount' => SortableFilter::make()->setColumn('address_count'),
            'violationAction' => SortableFilter::make()->setColumn('violation_action'),
            'violationCount' => SortableFilter::make()->setColumn('violation_count'),
            'lastMacAddress' => SortableFilter::make()->setColumn('last_mac_address'),
            'isStickyEnabled' => SortableFilter::make()->setColumn('sticky_enable'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('isEnabled', fn ($value, $model) => $model->port_security_enable)->readonly(),
            field('status')->readonly(),
            field('maxAddresses', fn ($value, $model) => $model->max_addresses)->readonly(),
            field('addressCount', fn ($value, $model) => $model->address_count)->readonly(),
            field('violationAction', fn ($value, $model) => $model->violation_action)->readonly(),
            field('violationCount', fn ($value, $model) => $model->violation_count)->readonly(),
            field('lastMacAddress', fn ($value, $model) => $model->last_mac_address)->readonly(),
            field('isStickyEnabled', fn ($value, $model) => $model->sticky_enable)->readonly(),
        ];
    }

    /**
     * Port security entries are discovered automatically by LibreNMS during the discovery process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * Port security entries are managed by the LibreNMS discovery process.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
