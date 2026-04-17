<?php

namespace App\Restify;

use App\Models\Port;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Fields\BelongsToMany;
use Binaryk\LaravelRestify\Fields\HasMany;
use Binaryk\LaravelRestify\Fields\HasOne;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class PortRepository extends Repository
{
    use PortScopedRepository;

    public static string $model = Port::class;

    public static string $id = 'port_id';

    public static string $title = 'ifName';




    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
            'groups' => BelongsToMany::make('groups', PortGroupRepository::class),
            'ipv4-addresses' => HasMany::make('ipv4', Ipv4AddressRepository::class)->label('ipv4-addresses'),
            'ipv6-addresses' => HasMany::make('ipv6', Ipv6AddressRepository::class)->label('ipv6-addresses'),
            'forwarding-database-entries' => HasMany::make('fdbEntries', PortsFdbRepository::class)->label('forwarding-database-entries'),
            'network-access-controls' => HasMany::make('nac', PortsNacRepository::class)->label('network-access-controls'),
            'neighbor-discovery' => HasMany::make('nd', Ipv6NdRepository::class)->label('neighbor-discovery'),
            'vlans' => HasMany::make('vlans', PortVlanRepository::class),
            'statistics' => HasMany::make('statistics', PortStatisticRepository::class),
            'spanning-tree' => HasMany::make('stp', PortStpRepository::class)->label('spanning-tree'),
            'transceivers' => HasMany::make('transceivers', TransceiverRepository::class),
            'adsl' => HasOne::make('adsl', PortAdslRepository::class),
            'vdsl' => HasOne::make('vdsl', PortVdslRepository::class),
        ];
    }

    public static function searchables(): array
    {
        return [
            'name' => SearchableFilter::make()->setColumn('ifName'),
            'alias' => SearchableFilter::make()->setColumn('ifAlias'),
        ];
    }

    public static function matches(): array
    {
        return [
            'interfaceIndex' => MatchFilter::make()->setType('integer')->setColumn('ifIndex'),
            'name' => MatchFilter::make()->setType('text')->setColumn('ifName'),
            'alias' => MatchFilter::make()->setType('text')->setColumn('ifAlias'),
            'description' => MatchFilter::make()->setType('text')->setColumn('ifDescr'),
            'category' => MatchFilter::make()->setType('text')->setColumn('ifType'),
            'speed' => MatchFilter::make()->setType('integer')->setColumn('ifSpeed'),
            'highSpeed' => MatchFilter::make()->setType('integer')->setColumn('ifHighSpeed'),
            'operationalStatus' => MatchFilter::make()->setType('text')->setColumn('ifOperStatus'),
            'adminStatus' => MatchFilter::make()->setType('text')->setColumn('ifAdminStatus'),
            'mtu' => MatchFilter::make()->setType('integer')->setColumn('ifMtu'),
            'physicalAddress' => MatchFilter::make()->setType('text')->setColumn('ifPhysAddress'),
            'inOctets' => MatchFilter::make()->setType('integer')->setColumn('ifInOctets'),
            'outOctets' => MatchFilter::make()->setType('integer')->setColumn('ifOutOctets'),
            'inErrors' => MatchFilter::make()->setType('integer')->setColumn('ifInErrors'),
            'outErrors' => MatchFilter::make()->setType('integer')->setColumn('ifOutErrors'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'interfaceIndex' => SortableFilter::make()->setColumn('ifIndex'),
            'name' => SortableFilter::make()->setColumn('ifName'),
            'alias' => SortableFilter::make()->setColumn('ifAlias'),
            'description' => SortableFilter::make()->setColumn('ifDescr'),
            'category' => SortableFilter::make()->setColumn('ifType'),
            'speed' => SortableFilter::make()->setColumn('ifSpeed'),
            'highSpeed' => SortableFilter::make()->setColumn('ifHighSpeed'),
            'operationalStatus' => SortableFilter::make()->setColumn('ifOperStatus'),
            'adminStatus' => SortableFilter::make()->setColumn('ifAdminStatus'),
            'mtu' => SortableFilter::make()->setColumn('ifMtu'),
            'physicalAddress' => SortableFilter::make()->setColumn('ifPhysAddress'),
            'inOctets' => SortableFilter::make()->setColumn('ifInOctets'),
            'outOctets' => SortableFilter::make()->setColumn('ifOutOctets'),
            'inErrors' => SortableFilter::make()->setColumn('ifInErrors'),
            'outErrors' => SortableFilter::make()->setColumn('ifOutErrors'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('interfaceIndex', fn ($value, $model) => $model->ifIndex)->readonly(),
            field('name', fn ($value, $model) => $model->ifName)->readonly(),
            field('alias', fn ($value, $model) => $model->ifAlias)->readonly(),
            field('description', fn ($value, $model) => $model->ifDescr)->readonly(),
            field('category', fn ($value, $model) => $model->ifType)->readonly(),
            field('speed', fn ($value, $model) => $model->ifSpeed)->readonly(),
            field('highSpeed', fn ($value, $model) => $model->ifHighSpeed)->readonly(),
            field('operationalStatus', fn ($value, $model) => $model->ifOperStatus)->readonly(),
            field('adminStatus', fn ($value, $model) => $model->ifAdminStatus)->readonly(),
            field('mtu', fn ($value, $model) => $model->ifMtu)->readonly(),
            field('physicalAddress', fn ($value, $model) => $model->ifPhysAddress)->readonly(),
            field('inOctets', fn ($value, $model) => $model->ifInOctets)->readonly(),
            field('outOctets', fn ($value, $model) => $model->ifOutOctets)->readonly(),
            field('inErrors', fn ($value, $model) => $model->ifInErrors)->readonly(),
            field('outErrors', fn ($value, $model) => $model->ifOutErrors)->readonly(),
        ];
    }
}
