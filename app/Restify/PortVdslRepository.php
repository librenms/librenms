<?php

namespace App\Restify;

use App\Models\PortVdsl;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Http\Request;
use Binaryk\LaravelRestify\Filters\MatchFilter;
use Binaryk\LaravelRestify\Filters\SearchableFilter;
use Binaryk\LaravelRestify\Filters\SortableFilter;

class PortVdslRepository extends Repository
{
    use PortScopedRepository;

    public static string $model = PortVdsl::class;

    public static string $id = 'port_id';

    public static string $title = 'port_id';



    public static function searchables(): array
    {
        return [];
    }

    public static function matches(): array
    {
        return [
            'attainableRateDownstream' => MatchFilter::make()->setType('integer')->setColumn('xdsl2LineStatusAttainableRateDs'),
            'attainableRateUpstream' => MatchFilter::make()->setType('integer')->setColumn('xdsl2LineStatusAttainableRateUs'),
            'actualDataRateReceive' => MatchFilter::make()->setType('integer')->setColumn('xdsl2ChStatusActDataRateXtur'),
            'actualDataRateCentralOffice' => MatchFilter::make()->setType('integer')->setColumn('xdsl2ChStatusActDataRateXtuc'),
            'actualAtpDownstream' => MatchFilter::make()->setType('integer')->setColumn('xdsl2LineStatusActAtpDs'),
            'actualAtpUpstream' => MatchFilter::make()->setType('integer')->setColumn('xdsl2LineStatusActAtpUs'),
        ];
    }

    public static function sorts(): array
    {
        return [
            'attainableRateDownstream' => SortableFilter::make()->setColumn('xdsl2LineStatusAttainableRateDs'),
            'attainableRateUpstream' => SortableFilter::make()->setColumn('xdsl2LineStatusAttainableRateUs'),
            'actualDataRateReceive' => SortableFilter::make()->setColumn('xdsl2ChStatusActDataRateXtur'),
            'actualDataRateCentralOffice' => SortableFilter::make()->setColumn('xdsl2ChStatusActDataRateXtuc'),
            'actualAtpDownstream' => SortableFilter::make()->setColumn('xdsl2LineStatusActAtpDs'),
            'actualAtpUpstream' => SortableFilter::make()->setColumn('xdsl2LineStatusActAtpUs'),
        ];
    }

    public function fields(RestifyRequest $request): array
    {
        return [
            field('attainableRateDownstream', fn ($value, $model) => $model->xdsl2LineStatusAttainableRateDs)->readonly(),
            field('attainableRateUpstream', fn ($value, $model) => $model->xdsl2LineStatusAttainableRateUs)->readonly(),
            field('actualDataRateReceive', fn ($value, $model) => $model->xdsl2ChStatusActDataRateXtur)->readonly(),
            field('actualDataRateCentralOffice', fn ($value, $model) => $model->xdsl2ChStatusActDataRateXtuc)->readonly(),
            field('actualAtpDownstream', fn ($value, $model) => $model->xdsl2LineStatusActAtpDs)->readonly(),
            field('actualAtpUpstream', fn ($value, $model) => $model->xdsl2LineStatusActAtpUs)->readonly(),
        ];
    }

    /**
     * VDSL port stats are discovered automatically by LibreNMS during the polling process — not created manually.
     */
    public static function authorizedToStore(Request $request): bool
    {
        return false;
    }

    /**
     * VDSL port stats are managed by the LibreNMS polling process.
     */
    public function authorizedToDelete(Request $request): bool
    {
        return false;
    }
}
