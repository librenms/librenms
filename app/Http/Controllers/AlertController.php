<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use Illuminate\Http\Request;
use LibreNMS\Config;
use Log;

class AlertController extends Controller
{
    public function ack(Request $request, Alert $alert): \Illuminate\Http\JsonResponse
    {
        $this->validate($request, [
            'state' => 'required|int',
            'ack_msg' => 'nullable|string',
            'ack_until_clear' => 'nullable|in:0,1,true,false',
        ]);

        $state = $request->get('state');
        $state_description = '';
        if ($state == 2) {
            $alert->state = 1;
            $state_description = 'UnAck';
            $alert->open = 1;
        } elseif ($state >= 1) {
            $alert->state = 2;
            $state_description = 'Ack';
            $alert->open = 1;
        }

        $info = $alert->info;
        $info['until_clear'] = filter_var($request->get('ack_until_clear'), FILTER_VALIDATE_BOOLEAN);
        $alert->info = $info;

        $timestamp = date(Config::get('dateformat.long'));
        $username = $request->user()->username;
        $ack_msg = $request->get('ack_msg');
        $alert->note = trim($alert->note . PHP_EOL . "$timestamp - $state_description ($username) " . $ack_msg);

        if ($alert->save()) {
            if (in_array($state, [2, 22])) {
                $rule_name = $alert->rule->name;
                Log::event("$username acknowledged alert $rule_name note: $ack_msg", $alert->device_id, 'alert', 2, $alert->id);
            }

            return response()->json([
                'message' => "Alert {$state_description}nowledged.",
                'status' => 'ok',
            ]);
        }

        return response()->json([
            'message' => 'Alert has not been acknowledged.',
            'status' => 'error',
        ]);
    }
}
