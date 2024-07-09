<?php

namespace App\Http\Controllers;

use App\Models\TransceiverMetric;
use Illuminate\Http\Request;

class TransceiverMetricController extends Controller
{
    public function update(Request $request, TransceiverMetric $metric)
    {
        $this->validate($request, [
            'field' => 'required|in:threshold_min_critical,threshold_min_warning,threshold_max_warning,threshold_max_critical',
            'value' => 'nullable|numeric',
        ]);

        $field = $request->input('field');
        $metric->$field = $request->input('value');
        $result = $metric->save();

        return response()->json([
            'message' => $result ? 'ok' : 'Failed to save',
            'metricStatus' => $metric->status->asSeverity()->value,
        ], $result ? 200 : 500);
    }
}
