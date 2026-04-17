<?php

namespace App\Restify;

use App\Models\Sla;
use Binaryk\LaravelRestify\Fields\BelongsTo;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class SlaRepository extends Repository
{
    use DeviceScopedRepository;

    public static string $model = Sla::class;

    public static string $uriKey = 'service-level-agreements';

    public static string $id = 'sla_id';

    public static string $title = 'tag';




    public static function related(): array
    {
        return [
            'device' => BelongsTo::make('device', DeviceRepository::class),
        ];
    }

    public static function searchables(): array
    {
        return [
            'tag' => SearchableFilter::make()->setColumn('tag'),
        ];
    }

    public static function matches(): array
    {
        return [
            'number' => MatchFilter::make()->setType('integer')->setColumn('sla_nr'),
            'owner' => MatchFilter::make()->setType('text')->setColumn('owner'),
            'tag' => MatchFilter::make()->setType('text')->setColumn('tag'),
            'rttCategory' => MatchFilter::make()->setType('text')->setColumn('rtt_type'),
            'rtt' => MatchFilter::make()->setType('integer')->setColumn('rtt'),
            'status' => MatchFilter::make()->setType('text')->setColumn('status'),
            'operationalStatus' => MatchFilter::make()->setType('text')->setColumn('opstatus'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'number' => SortableFilter::make()->setColumn('sla_nr'),
            'owner' => SortableFilter::make()->setColumn('owner'),
            'tag' => SortableFilter::make()->setColumn('tag'),
            'rttCategory' => SortableFilter::make()->setColumn('rtt_type'),
            'rtt' => SortableFilter::make()->setColumn('rtt'),
            'status' => SortableFilter::make()->setColumn('status'),
            'operationalStatus' => SortableFilter::make()->setColumn('opstatus'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('number', fn ($value, $model) => $model->sla_nr)->readonly(),
            field('owner')->readonly(),
            field('tag')->readonly(),
            field('rttCategory', fn ($value, $model) => $model->rtt_type)->readonly(),
            field('rtt')->readonly(),
            field('status')->readonly(),
            field('operationalStatus', fn ($value, $model) => $model->opstatus)->readonly(),
        ];
    }

    /**
     * SLA entries are discovered automatically by LibreNMS during the discovery process not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * SLA entries are managed by the LibreNMS discovery process they are removed when no longer detected.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
