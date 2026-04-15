<?php

namespace App\Restify;

use App\Models\Device;
use App\Models\Location;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Fields\BelongsToMany;
use Binaryk\LaravelRestify\Fields\HasMany;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class DeviceRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = Device::class;

    public static string $id = 'device_id';

    public static string $title = 'hostname';




    public static function related(): array
    {
        return [
            'ports' => HasMany::make('ports', PortRepository::class),
            'sensors' => HasMany::make('sensors', SensorRepository::class),
            'processors' => HasMany::make('processors', ProcessorRepository::class),
            'mempools' => HasMany::make('mempools', MempoolRepository::class),
            'storage' => HasMany::make('storage', StorageRepository::class),
            'services' => HasMany::make('services', ServiceRepository::class),
            'bgpPeers' => HasMany::make('bgpPeers', BgpPeerRepository::class),
            'components' => HasMany::make('components', ComponentRepository::class),
            'applications' => HasMany::make('applications', ApplicationRepository::class),
            'inventory' => HasMany::make('entityPhysical', InventoryRepository::class),
            'eventlogs' => HasMany::make('eventlogs', EventlogRepository::class),
            'syslogs' => HasMany::make('syslogs', SyslogRepository::class),
            'alertLogs' => HasMany::make('alertLogs', AlertLogRepository::class),
            'location' => BelongsTo::make('location', LocationRepository::class),
            'groups' => BelongsToMany::make('groups', DeviceGroupRepository::class),
        ];
    }

    public static function searchables(): array
    {
        return [
            'hostname' => SearchableFilter::make()->setColumn('hostname'),
            'systemName' => SearchableFilter::make()->setColumn('sysName'),
            'ip' => SearchableFilter::make()->setColumn('ip'),
        ];
    }

    public static function matches(): array
    {
        return [
            'hostname' => MatchFilter::make()->setType('text')->setColumn('hostname'),
            'systemName' => MatchFilter::make()->setType('text')->setColumn('sysName'),
            'systemDescription' => MatchFilter::make()->setType('text')->setColumn('sysDescr'),
            'systemObjectId' => MatchFilter::make()->setType('text')->setColumn('sysObjectID'),
            'os' => MatchFilter::make()->setType('text')->setColumn('os'),
            'isUp' => MatchFilter::make()->setType('bool')->setColumn('status'),
            'statusReason' => MatchFilter::make()->setType('text')->setColumn('status_reason'),
            'hardware' => MatchFilter::make()->setType('text')->setColumn('hardware'),
            'serial' => MatchFilter::make()->setType('text')->setColumn('serial'),
            'version' => MatchFilter::make()->setType('text')->setColumn('version'),
            'features' => MatchFilter::make()->setType('text')->setColumn('features'),
            'ip' => MatchFilter::make()->setType('text')->setColumn('ip'),
            'uptime' => MatchFilter::make()->setType('integer')->setColumn('uptime'),
            'notes' => MatchFilter::make()->setType('text')->setColumn('notes'),
            'display' => MatchFilter::make()->setType('text')->setColumn('display'),
            'overwriteIp' => MatchFilter::make()->setType('text')->setColumn('overwrite_ip'),
            'purpose' => MatchFilter::make()->setType('text')->setColumn('purpose'),
            'category' => MatchFilter::make()->setType('text')->setColumn('type'),
            'isPollingEnabled' => MatchFilter::make()->setType('bool')->setColumn('disabled'),
            'isAlertingEnabled' => MatchFilter::make()->setType('bool')->setColumn('disable_notify'),
            'isIgnored' => MatchFilter::make()->setType('bool')->setColumn('ignore'),
            'isStatusIgnored' => MatchFilter::make()->setType('bool')->setColumn('ignore_status'),
            'isSystemLocationOverridden' => MatchFilter::make()->setType('bool')->setColumn('override_sysLocation'),
            'systemLocation' => MatchFilter::make()->setType('text')->setColumn('location_id'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'hostname' => SortableFilter::make()->setColumn('hostname'),
            'systemName' => SortableFilter::make()->setColumn('sysName'),
            'systemDescription' => SortableFilter::make()->setColumn('sysDescr'),
            'systemObjectId' => SortableFilter::make()->setColumn('sysObjectID'),
            'os' => SortableFilter::make()->setColumn('os'),
            'isUp' => SortableFilter::make()->setColumn('status'),
            'statusReason' => SortableFilter::make()->setColumn('status_reason'),
            'hardware' => SortableFilter::make()->setColumn('hardware'),
            'serial' => SortableFilter::make()->setColumn('serial'),
            'version' => SortableFilter::make()->setColumn('version'),
            'features' => SortableFilter::make()->setColumn('features'),
            'ip' => SortableFilter::make()->setColumn('ip'),
            'uptime' => SortableFilter::make()->setColumn('uptime'),
            'notes' => SortableFilter::make()->setColumn('notes'),
            'display' => SortableFilter::make()->setColumn('display'),
            'overwriteIp' => SortableFilter::make()->setColumn('overwrite_ip'),
            'purpose' => SortableFilter::make()->setColumn('purpose'),
            'category' => SortableFilter::make()->setColumn('type'),
            'isPollingEnabled' => SortableFilter::make()->setColumn('disabled'),
            'isAlertingEnabled' => SortableFilter::make()->setColumn('disable_notify'),
            'isIgnored' => SortableFilter::make()->setColumn('ignore'),
            'isStatusIgnored' => SortableFilter::make()->setColumn('ignore_status'),
            'isSystemLocationOverridden' => SortableFilter::make()->setColumn('override_sysLocation'),
            'systemLocation' => SortableFilter::make()->setColumn('location_id'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('hostname')->readonly(),
            field('systemName', fn ($value, $model) => $model->sysName)->readonly(),
            field('systemDescription', fn ($value, $model) => $model->sysDescr)->readonly(),
            field('systemObjectId', fn ($value, $model) => $model->sysObjectID)->readonly(),
            field('os')->readonly(),
            field('isUp', fn ($value, $model) => $model->status)->readonly(),
            field('statusReason', fn ($value, $model) => $model->status_reason)->readonly(),
            field('hardware')->readonly(),
            field('serial')->readonly(),
            field('version')->readonly(),
            field('features')->readonly(),
            field('ip')->readonly(),
            field('uptime')->readonly(),
            field('notes')->readonly(),
            field('display')->readonly(),
            field('overwriteIp', fn ($value, $model) => $model->overwrite_ip)->readonly(),
            field('purpose')->readonly(),
            field('category', fn ($value, $model) => $model->type)->readonly(),
            field('isPollingEnabled', fn ($value, $model) => ! $model->disabled)->readonly(),
            field('isAlertingEnabled', fn ($value, $model) => ! $model->disable_notify)->readonly(),
            field('isIgnored', fn ($value, $model) => $model->ignore)->readonly(),
            field('isStatusIgnored', fn ($value, $model) => $model->ignore_status)->readonly(),
            field('isSystemLocationOverridden', fn ($value, $model) => $model->override_sysLocation)->readonly(),
            field('systemLocation', fn ($value, $model) => $model->location?->location)->readonly(),
        ];
    }
}
