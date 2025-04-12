<?php

namespace App\Http\Controllers\Table;

use App\Models\Storage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use LibreNMS\Util\Html;
use LibreNMS\Util\Number;
use LibreNMS\Util\Url;

class StoragesController extends TableController
{
    protected $default_sort = ['device_hostname' => 'asc', 'storage_descr' => 'asc'];

    protected function sortFields($request): array
    {
        return [
            'device_hostname',
            'storage_descr',
            'storage_used',
            'storage_perc',
        ];
    }

    protected function searchFields(Request $request): array
    {
        return [
            'hostname',
            'storage_descr',
        ];
    }

    protected function baseQuery(Request $request): Builder
    {
        return Storage::query()
            ->hasAccess($request->user())
            ->when($request->get('searchPhrase'), fn ($q) => $q->leftJoin('devices', 'devices.device_id', '=', 'storage.device_id'))
            ->withAggregate('device', 'hostname');
    }

    /**
     * @param  Storage  $storage
     */
    public function formatItem($storage): array
    {
        $hostname = Blade::render('<x-device-link :device="$device" />', ['device' => $storage->device]);
        $descr = $storage->storage_descr;
        $graph_array = [
            'type' => 'storage_usage',
            'popup_title' => htmlentities(strip_tags($storage->device->displayName() . ': ' . $storage->storage_descr)),
            'id' => $storage->storage_id,
            'from' => '-1d',
            'height' => 20,
            'width' => 80,
        ];
        $mini_graph = Url::graphPopup($graph_array);
        $used = $this->usageBar($storage, $graph_array);

        if (\Request::input('view') == 'graphs') {
            $row = Html::graphRow(array_replace($graph_array, ['height' => 100, 'width' => 216]));
            $hostname = '<div class="tw:border-b tw:border-gray-200">' . $hostname . '</div><div style="width:216px;margin-left:auto;border-top:">' . $row[0] . '</div>';
            $descr = '<div class="tw:border-b tw:border-gray-200">' . $descr . '</div><div style="width:216px">' . $row[1] . '</div>';
            $mini_graph = '<div class="tw:border-b tw:border-gray-200" style="min-height:20px">' . $mini_graph . '</div><div style="width:216px">' . $row[2] . '</div>';
            $used = '<div class="tw:border-b tw:border-gray-200">' . $used . '</div><div style="width:216px">' . $row[3] . '</div>';
        }

        return [
            'device_hostname' => $hostname,
            'storage_descr' => $descr,
            'graph' => $mini_graph,
            'storage_used' => $used,
            'storage_perc' => round($storage->storage_perc) . '%',
        ];
    }

    private function usageBar(Storage $storage, array $graph_array): string
    {
        $left_text = Number::formatBi($storage->storage_used) . ' / ' . Number::formatBi($storage->storage_size);
        $right_text = Number::formatBi($storage->storage_free);
        $bar = Html::percentageBar(400, 20, $storage->storage_perc, $left_text, $right_text, $storage->storage_perc_warn);

        return Url::graphPopup($graph_array, $bar);
    }
}
