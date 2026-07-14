<?php

namespace App\Http\Controllers;

use App\Facades\LibrenmsConfig;
use App\Models\Alert;
use App\Models\AlertProblem;
use App\Models\Eventlog;
use Illuminate\Http\Request;
use LibreNMS\Enum\AlertState;
use LibreNMS\Enum\Severity;

class AlertController extends Controller
{
    public function ack(Request $request, AlertProblem $problem): \Illuminate\Http\JsonResponse
    {
        $alert = Alert::query()->where('rule_id', $problem->rule_id)->where('device_id', $problem->device_id)->first();
        if ($alert) {
            $this->authorize('update', $alert);
        }

        $this->validate($request, [
            'state' => 'required|int',
            'ack_msg' => 'nullable|string',
            'ack_until_clear' => 'nullable|in:0,1,true,false',
        ]);

        $state = $request->input('state');
        $state_description = '';
        $newState = null;
        if ($state == AlertState::ACKNOWLEDGED) {
            $newState = AlertState::ACTIVE;
            $state_description = 'UnAck';
        } elseif ($state >= AlertState::ACTIVE) {
            $newState = AlertState::ACKNOWLEDGED;
            $state_description = 'Ack';
        }

        if ($newState === null) {
            return response()->json([
                'message' => 'Problem has not been acknowledged.',
                'status' => 'error',
            ]);
        }

        $untilClear = filter_var($request->input('ack_until_clear'), FILTER_VALIDATE_BOOLEAN);
        $timestamp = date(LibrenmsConfig::get('dateformat.long'));
        $username = $request->user()->username;
        $ack_msg = $request->input('ack_msg');
        $note_suffix = "$timestamp - $state_description ($username) " . $ack_msg;

        $targets = $problem->rule?->notify_per_entity
            ? collect([$problem])
            : AlertProblem::query()->where('rule_id', $problem->rule_id)->where('device_id', $problem->device_id)->where('open', 1)->get();

        $saved = false;
        foreach ($targets as $target) {
            $target->state = $newState;
            $target->open = 1;
            $info = $target->info ?: [];
            $info['until_clear'] = $untilClear;
            $target->info = $info;
            $target->note = trim($target->note . PHP_EOL . $note_suffix);
            $saved = $target->save() || $saved;
        }

        if ($saved) {
            $rule_name = $problem->rule?->name;
            $act = strtolower($state_description) . 'nowledged';
            Eventlog::log("$username {$act} alert $rule_name note: $ack_msg", $problem->device_id, 'alert', Severity::Info, $problem->id);

            return response()->json([
                'message' => "Problem {$state_description}nowledged.",
                'status' => 'ok',
            ]);
        }

        return response()->json([
            'message' => 'Problem has not been acknowledged.',
            'status' => 'error',
        ]);
    }
}
