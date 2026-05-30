<?php

namespace App\Http\Controllers\Table;

use App\Models\Device;
use App\Models\PrinterSupply;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use LibreNMS\Util\Color;
use LibreNMS\Util\Html;
use LibreNMS\Util\Number;
use LibreNMS\Util\StringHelpers;
use LibreNMS\Util\Url;

/**
 * @extends TableController<PrinterSupply>
 */
class PrinterSupplyController extends TableController
{
    protected array $default_sort = ['device_hostname' => 'asc', 'supply_descr' => 'asc'];

    protected function sortFields(Request $request): array
    {
        return [
            'device_hostname',
            'supply_descr',
            'supply_type',
            'supply_current',
        ];
    }

    protected function searchFields(Request $request): array
    {
        return [
            'hostname',
            'display',
            'supply_descr',
            'supply_type',
        ];
    }

    /**
     * @inheritDoc
     */
    protected function baseQuery(Request $request): Builder
    {
        $this->authorize('viewAny', Device::class);

        return PrinterSupply::query()
            ->hasAccess($request->user())
            ->with('device')
            ->when($request->input('searchPhrase'), fn ($q) => $q->leftJoin('devices', 'devices.device_id', '=', 'printer_supplies.device_id'))
            ->withAggregate('device', 'hostname');
    }

    /**
     * @param  PrinterSupply  $model
     * @return array<string, scalar>
     */
    public function formatItem(Model $model): array
    {
        $hostname = Blade::render('<x-device-link :device="$device" />', ['device' => $model->device]);
        $type = StringHelpers::camelToTitle($model->supply_type == 'opc' ? 'organicPhotoConductor' : $model->supply_type);
        $descr = $model->supply_descr;
        $percent = Number::calculatePercent($model->supply_current, $model->supply_capacity);
        $used = $percent . '%';

        $graph_array = [
            'type' => 'toner_usage',
            'popup_title' => htmlentities(strip_tags($model->device?->displayName() . ': ' . $model->supply_descr)),
            'id' => $model->supply_id,
            'from' => '-1d',
            'height' => 20,
            'width' => 80,
        ];
        $mini_graph = Url::graphPopup($graph_array);

        if (\Request::input('view') == 'graphs') {
            $row = Html::graphRow(array_replace($graph_array, ['height' => 100, 'width' => 216]));

            return [
                'device_hostname' => '<div class="tw:border-b tw:border-gray-200">' . $hostname . '</div><div style="width:216px;margin-left:auto;border-top:">' . $row[0] . '</div>',
                'supply_descr' => '<div class="tw:border-b tw:border-gray-200">' . $descr . '</div><div style="width:216px">' . $row[1] . '</div>',
                'supply_type' => '',
                'graph' => '<div class="tw:border-b tw:border-gray-200" style="min-height:20px">' . $mini_graph . '</div><div style="width:216px">' . $row[2] . '</div>',
                'supply_current' => '<div class="tw:border-b tw:border-gray-200">' . $used . '</div><div style="width:216px">' . $row[3] . '</div>',
            ];
        }

        $colors = Color::percentage(100 - $percent); // supply, not usage
        $right = $model->supply_capacity == 100 ? '' : $model->supply_capacity;

        $bar = Html::percentageBar(400, 10, $percent, $model->supply_current, $right, colors: $colors);

        return [
            'device_hostname' => $hostname,
            'supply_descr' => $descr,
            'supply_type' => $type,
            'graph' => $mini_graph,
            'supply_current' => Url::graphPopup($graph_array, $bar),
        ];
    }
}
