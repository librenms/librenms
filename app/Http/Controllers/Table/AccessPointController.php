<?php

namespace App\Http\Controllers\Table;

use App\Models\AccessPoint;
use App\Models\Device;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use LibreNMS\Util\Url;

/**
 * @extends TableController<AccessPoint>
 */
class AccessPointController extends TableController
{
    protected ?string $model = AccessPoint::class;

    /** @var array<string, string> */
    protected array $default_sort = ['name' => 'asc'];

    /** @return array<string, string> */
    protected function rules(): array
    {
        return [
            'device_id' => 'required|integer|exists:devices,device_id',
        ];
    }

    /** @return array<int|string, string|array<int, string>> */
    protected function sortFields(Request $request): array
    {
        return [
            'name' => ['name', 'radio_number'],
            'radio' => ['radio_number', 'type'],
            'channel',
            'numasoclients',
            'radioutil',
            'interference',
            'txpow',
        ];
    }

    /** @return array<int, string> */
    protected function searchFields(Request $request): array
    {
        return [
            'name',
            'mac_addr',
            'type',
        ];
    }

    protected function baseQuery(Request $request): Builder
    {
        $device = Device::findOrFail((int) $request->input('device_id'));
        $this->authorize('view', $device);

        return AccessPoint::query()
            ->where('device_id', $device->device_id)
            ->where('deleted', false);
    }

    /**
     * @param  AccessPoint  $model
     * @return array<string, scalar>
     */
    public function formatItem(Model $model): array
    {
        $detailUrl = route('device', [
            'device' => $model->device_id,
            'tab' => 'accesspoints',
            'vars' => 'ap=' . $model->accesspoint_id,
        ]);
        $escapedUrl = htmlspecialchars($detailUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $name = htmlspecialchars((string) $model->name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $mac = htmlspecialchars((string) $model->mac_addr, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $type = htmlspecialchars((string) $model->type, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $graph = (fn (string $type, string $title): string => Url::graphPopup([
            'type' => $type,
            'id' => $model->accesspoint_id,
            'from' => '-1d',
            'width' => 100,
            'height' => 20,
            'legend' => 'no',
            'popup_title' => htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
        ], null, $detailUrl));

        $graphTitle = strip_tags((string) $model->name) . ' radio ' . (int) $model->radio_number;

        return [
            'accesspoint_id' => (int) $model->accesspoint_id,
            'name' => '<a href="' . $escapedUrl . '">' . $name . '</a><br /><span class="text-muted">' . $mac . '</span>',
            'radio' => $type . ' (' . (int) $model->radio_number . ')',
            'channel' => (int) $model->channel,
            'numasoclients' => (int) $model->numasoclients,
            'radioutil' => (int) $model->radioutil,
            'interference' => (int) $model->interference,
            'txpow' => (int) $model->txpow,
            'trends' => '<div class="tw:flex tw:flex-col tw:gap-1">'
                . $graph('accesspoints_numasoclients', $graphTitle . ': Associated Clients')
                . $graph('accesspoints_radioutil', $graphTitle . ': Radio Utilization')
                . $graph('accesspoints_interference', $graphTitle . ': Interference Index')
                . '</div>',
        ];
    }
}
