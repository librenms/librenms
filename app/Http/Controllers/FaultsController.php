<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\AlertFault;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use LibreNMS\Enum\AlertState;
use LibreNMS\Util\Url;
use Symfony\Component\HttpFoundation\ParameterBag;

class FaultsController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $this->authorize('viewAny', Alert::class);

        $legacy = Url::parseLegacyPath($request->path());
        if ($legacy->count()) {
            return redirect()->route('faults', array_filter([
                'filter' => array_merge($request->array('filter'), $this->parseLegacyUrls($legacy)),
                'alert_id' => $request->query('alert_id', $legacy->get('alert_id')),
            ]));
        }

        $request->validate([
            ...AlertFault::filterValidationRules(),
            'alert_id' => ['nullable', 'integer'],
        ]);

        return view('alerts.index', [
            'alert_id' => $request->integer('alert_id'),
            'filter' => $request->array('filter'),
            'filterFields' => AlertFault::filterFieldDefinitions(),
        ]);
    }

    /**
     * Translate legacy url segments (alerts/rule_id=1/state=2) into filters
     *
     * @return array<string, array<string, int|string>>
     */
    private function parseLegacyUrls(ParameterBag $legacy): array
    {
        $filters = [];

        foreach (['rule_id', 'device_id'] as $field) {
            if (is_numeric($legacy->get($field))) {
                $filters[$field] = ['eq' => (int) $legacy->get($field)];
            }
        }

        // state, fired and acknowledged all filter the state, use the most specific one
        // (an exact state already implies whether it is fired or acknowledged)
        if (is_numeric($legacy->get('state'))) {
            $filters['state'] = ['eq' => AlertFault::filterStateName($legacy->getInt('state')) ?? $legacy->getInt('state')];
        } elseif ($legacy->getInt('fired')) {
            $filters['state'] = ['eq' => AlertFault::filterStateName(AlertState::ACTIVE)];
        } elseif (is_numeric($legacy->get('acknowledged'))) {
            $filters['state'] = [$legacy->getInt('acknowledged') ? 'eq' : 'neq' => AlertFault::filterStateName(AlertState::ACKNOWLEDGED)];
        }

        if (is_numeric($legacy->get('group'))) {
            $filters['device.groups.id'] = ['eq' => $legacy->getInt('group')];
        }

        return $filters;
    }
}
