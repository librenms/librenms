<?php

namespace App\Http\Controllers\Table;

use App\Models\Processor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use LibreNMS\Util\Html;
use LibreNMS\Util\Url;

class ProcessorsController extends TableController
{
    protected $default_sort = ['device_hostname' => 'asc', 'processor_descr' => 'asc'];

    protected function sortFields($request): array
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
            'device_hostname',
            'processor_descr',
        ];
    }

    protected function baseQuery(Request $request): Builder
    {
        return Processor::query()
            ->hasAccess($request->user())
            ->with(['device', 'device.location'])
            ->withAggregate('device', 'hostname');
    }

    /**
     * @param  Processor  $processor
     */
    public function formatItem($processor): array
    {
        $perc = round($processor->processor_usage);
        $graph_array = [
            'type' => 'processor_usage',
            'popup_title' => htmlentities(strip_tags($processor->device->displayName() . ': ' . $processor->processor_descr)),
            'id' => $processor->processor_id,
            'from' => '-1d',
            'height' => 20,
            'width' => 80,
        ];

        $hostname = Blade::render('<x-device-link :device="$device" />', ['device' => $processor->device]);
        $descr = $processor->processor_descr;
        $mini_graph = URL::graphPopup($graph_array);
        $bar = Html::percentageBar(400, 20, $perc, $perc . '%', (100 - $perc) . '%', $processor->processor_perc_warn);
        $usage = URL::graphPopup($graph_array, $bar);

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
}
