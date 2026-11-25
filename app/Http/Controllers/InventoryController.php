<?php

namespace App\Http\Controllers;

use App\Models\EntPhysical;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class InventoryController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        $this->authorize('viewAny', EntPhysical::class);

        $legacy = $request->validate([
            'device' => 'nullable|int',
            'descr' => 'nullable|string',
            'model' => 'nullable|string',
            'serial' => 'nullable|string',
        ]);
        if (array_filter($legacy, fn ($value) => $value !== null)) {
            $filter = array_filter([
                'device_id' => isset($legacy['device']) ? ['eq' => $legacy['device']] : null,
                'entPhysicalDescr' => isset($legacy['descr']) ? ['contains' => $legacy['descr']] : null,
                'entPhysicalModelName' => isset($legacy['model']) ? ['contains' => $legacy['model']] : null,
                'entPhysicalSerialNum' => isset($legacy['serial']) ? ['contains' => $legacy['serial']] : null,
            ]);

            return redirect()->route('inventory', ['filter' => $filter]);
        }

        $request->validate(EntPhysical::filterValidationRules());

        return view('inventory', [
            'filter' => $request->array('filter'),
            'filterFields' => $this->filterFields(),
            'show_purge' => Gate::allows('purge', EntPhysical::class) && EntPhysical::whereDoesntHave('device')->exists(),
        ]);
    }

    private function filterFields(): array
    {
        return [
            ['key' => 'search', 'label' => __('Search'), 'type' => 'text', 'search' => true],
            ['key' => 'device_id', 'label' => __('Device'), 'type' => 'select', 'endpoint' => route('ajax.select.device')],
            [
                'key' => 'part_type',
                'label' => __('Type'),
                'type' => 'select',
                'options' => [
                    'physical' => __('inventory.physical'),
                    'transceiver' => __('inventory.transceiver'),
                ],
            ],
            ['key' => 'entPhysicalDescr', 'label' => __('Description'), 'type' => 'text'],
            ['key' => 'entPhysicalModelName', 'label' => __('inventory.model'), 'type' => 'text'],
            ['key' => 'entPhysicalSerialNum', 'label' => __('inventory.serial'), 'type' => 'text'],
        ];
    }

    public function purge(): RedirectResponse
    {
        $this->authorize('purge', EntPhysical::class);

        EntPhysical::whereDoesntHave('device')->delete();

        return redirect()->back();
    }
}
