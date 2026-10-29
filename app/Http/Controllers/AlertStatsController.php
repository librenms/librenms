<?php

namespace App\Http\Controllers;

use App\Models\AlertLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AlertStatsController extends Controller
{
    public function __invoke(Request $request): View
    {
        $this->authorize('viewAny', AlertLog::class);

        return view('alerts.stats', [
            'stats' => AlertLog::severityStats(AlertLog::hasAccess($request->user())),
        ]);
    }
}
