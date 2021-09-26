<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PushNotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function token(): string
    {
        return csrf_token();
    }

    public function key(): string
    {
        return config('webpush.vapid.public_key');
    }

    public function register(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->validate($request, [
            'description' => 'string',
            'subscription.endpoint' => 'required',
            'subscription.keys.auth' => 'required',
            'subscription.keys.p256dh' => 'required',
        ]);

        $subscription = $request->user()
            ->updatePushSubscription(
                $request->input('subscription.endpoint'),
                $request->input('subscription.keys.p256dh'),
                $request->input('subscription.keys.auth')
            );

        $subscription->description = $request->get('description');
        $subscription->save();

        return response()->json(['success' => true], 200);
    }
}
