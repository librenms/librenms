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
            'subscription.endpoint' => 'required|url',
            'subscription.keys.auth' => 'required|string',
            'subscription.keys.p256dh' => 'required|string',
        ]);

        $subscription = $request->user()
            ->updatePushSubscription(
                $request->input('subscription.endpoint'),
                $request->input('subscription.keys.p256dh'),
                $request->input('subscription.keys.auth')
            );

        $subscription->description = $request->get('description');
        $success = $subscription->save();

        return response()->json(['success' => $success], 200);
    }

    public function unregister(Request $request): void
    {
        $this->validate($request, [
            'endpoint' => 'required|url',
        ]);

        $request->user()
            ->deletePushSubscription($request->input('endpoint'));
    }
}
