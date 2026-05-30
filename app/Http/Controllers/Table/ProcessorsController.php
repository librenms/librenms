<?php

namespace App\Http\Controllers\Table;

use App\Models\Processor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use LibreNMS\Util\Html;
use LibreNMS\Util\Url;

/**
 * @extends TableController<Processor>
 */
class ProcessorsController extends TableController
{
    protected ?string $model = Processor::class;

    protected array $default_sort = ['device_hostname' => 'asc', 'processor_descr' => 'asc'];

    protected function rules(): array
    {
        return [
            'status' => 'nullable|string',
        ];
    }

    protected function sortFields(Request $request): array
    {
        return [
            'device_hostname',
            'processor_descr',
            'processor_usage',
        ];
    }

    protected function searchFields(Request $request): array
    {
        return [
            'hostname',
            'display',
            'processor_descr',
        ];
    }

    protected function baseQuery(Request $request): Builder
    {
        $this->authorize('viewAny', Processor::class);

        return Processor::query()
            ->hasAccess($request->user())
            ->when($request->input('searchPhrase'), fn ($q) => $q->leftJoin('devices', 'devices.device_id', '=', 'processors.device_id'))
            ->withAggregate('device', 'hostname')
            ->when($request->input('status') == 'warning', function ($q): void {
                // show only entries in warning state
                $q->where('processor_perc_warn', '>', 0)
                    ->whereColumn('processor_usage', '>=', 'processor_perc_warn');
            });
    }

    /**
     * @param  Processor  $model
     * @return array<string, scalar>
     */
    public function formatItem(Model $model): array
    {
        $perc = round($model->processor_usage);
        $graph_array = [
            'type' => 'processor_usage',
            'popup_title' => htmlentities(strip_tags($model->device->displayName() . ': ' . $model->processor_descr)),
            'id' => $model->processor_id,
            'from' => '-1d',
            'height' => 20,
            'width' => 80,
        ];

        $hostname = Blade::render('<x-device-link :device="$device" />', ['device' => $model->device]);
        $descr = $model->processor_descr;
        $mini_graph = Url::graphPopup($graph_array);
        $bar = Html::percentageBar(400, 10, $perc, $perc . '%', (100 - $perc) . '%', $model->processor_perc_warn);
        $usage = Url::graphPopup($graph_array, $bar);

        if (\Request::input('view') == 'graphs') {
            $row = Html::graphRow(array_replace($graph_array, ['height' => 100, 'width' => 216]));
            $hostname = '<div class="tw:border-b tw:border-gray-200">' . $hostname . '</div><div style="width:216px;margin-left:auto;border-top:">' . $row[0] . '</div>';
            $descr = '<div class="tw:border-b tw:border-gray-200">' . $descr . '</div><div style="width:216px">' . $row[1] . '</div>';
            $mini_graph = '<div class="tw:border-b tw:border-gray-200" style="min-height:20px">' . $mini_graph . '</div><div style="width:216px">' . $row[2] . '</div>';
            $usage = '<div class="tw:border-b tw:border-gray-200">' . $usage . '</div><div style="width:216px">' . $row[3] . '</div>';
        }

        return [
            'device_hostname' => $hostname,
            'processor_descr' => $descr,
            'graph' => $mini_graph,
            'processor_usage' => $usage,
        ];
    }

    /**
     * Get headers for CSV export
     */
    protected function getExportHeaders(): array
    {
        return [
            'Device Hostname',
            'Processor',
            'Usage',
        ];
    }

    /**
     * Format a row for CSV export
     *
     * @param  Processor  $processor
     * @return array<scalar>
     */
    protected function formatExportRow(Model $processor): array
    {
        return [
            $processor->device ? $processor->device->displayName() : '',
            $processor->processor_descr,
            $processor->processor_usage,
        ];
    }
}
