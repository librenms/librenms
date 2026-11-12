<?php

namespace App\Listeners;

use App\Facades\LibrenmsConfig;
use Illuminate\Auth\Events\Failed;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class AuthFailedListener
{
    public function handle(Failed $event): void
    {
        if (LibrenmsConfig::get('auth_mechanism') === 'active_directory') {
            DB::table('authlog')->insert([
                'user' => $event->credentials['samaccountname'] ?? $event->credentials['userprincipalname'] ?? '',
                'address' => Request::ip(),
                'result' => 'Invalid credentials',
            ]);
        }
    }
}
