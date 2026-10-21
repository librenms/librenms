<?php

namespace App\Http\Controllers;

use App\Models\AlertLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use LibreNMS\Util\Url;

class AlertHistoryController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $this->authorize('viewAny', AlertLog::class);

        $legacyFilters = $this->parseLegacyUrls($request);
        if (! empty($legacyFilters)) {
            return redirect()->route('alert-log', ['filter' => array_merge($request->array('filter'), $legacyFilters)]);
        }

        $request->validate(AlertLog::filterValidationRules());

        return view('alerts.history', [
            'filter' => $request->array('filter'),
            'filterFields' => AlertLog::filterFieldDefinitions(),
        ]);
    }

    /**
     * Translate the old form parameters (?device_id=1&state=0&severity[]=3) into filters
     *
     * @return array<string, array<string, int|string>>
     */
    private function parseLegacyUrls(Request $request): array
    {
        $legacy = Url::parseLegacyPath($request->path());
        $filters = [];

        $deviceId = $request->query('device_id', $legacy->get('device'));
        if (is_numeric($deviceId)) {
            $filters['device_id'] = ['eq' => (int) $deviceId];
        }

        $state = $request->query('state', $legacy->get('state'));
        if (is_numeric($state)) {
            $filters['state'] = ['eq' => AlertLog::filterStateName((int) $state) ?? (int) $state];
        }

        // legacy severity[] values were 1 based enum indexes
        $severities = array_filter(array_map(fn ($s) => AlertLog::SEVERITIES[(int) $s - 1] ?? null, (array) $request->query('severity', [])));
        if (! empty($severities)) {
            $filters['rule.severity'] = ['in' => implode(',', $severities)];
        }

        return $filters;
    }
}
