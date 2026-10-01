<?php

namespace App\Http\Controllers;

use App\Models\AuthLog;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;

class AuthLogController
{
    use AuthorizesRequests;

    public function index(): View
    {
        $this->authorize('auth-log.view');

        return view('user.authlog', [
            'authlog' => AuthLog::orderBy('datetime', 'DESC')->get(),
        ]);
    }
}
