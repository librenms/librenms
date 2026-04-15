<?php

namespace App\Restify;

use App\Models\Vlan;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class VlanRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = Vlan::class;

    public static string $id = 'vlan_id';

    public static string $title = 'vlan_name';




    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
        ];
    }

    public static function searchables(): array
    {
        return [
            'name' => SearchableFilter::make()->setColumn('vlan_name'),
        ];
    }

    public static function matches(): array
    {
        return [
            'vid' => MatchFilter::make()->setType('integer')->setColumn('vlan_vlan'),
            'domain' => MatchFilter::make()->setType('integer')->setColumn('vlan_domain'),
            'name' => MatchFilter::make()->setType('text')->setColumn('vlan_name'),
            'category' => MatchFilter::make()->setType('text')->setColumn('vlan_type'),
            'mtu' => MatchFilter::make()->setType('integer')->setColumn('vlan_mtu'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'vid' => SortableFilter::make()->setColumn('vlan_vlan'),
            'domain' => SortableFilter::make()->setColumn('vlan_domain'),
            'name' => SortableFilter::make()->setColumn('vlan_name'),
            'category' => SortableFilter::make()->setColumn('vlan_type'),
            'mtu' => SortableFilter::make()->setColumn('vlan_mtu'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('vid', fn ($value, $model) => $model->vlan_vlan)->readonly(),
            field('domain', fn ($value, $model) => $model->vlan_domain)->readonly(),
            field('name', fn ($value, $model) => $model->vlan_name)->readonly(),
            field('category', fn ($value, $model) => $model->vlan_type)->readonly(),
            field('mtu', fn ($value, $model) => $model->vlan_mtu)->readonly(),
        ];
    }

    /**
     * VLANs are discovered automatically by LibreNMS during the discovery process not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * VLANs are managed by the LibreNMS discovery process they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
