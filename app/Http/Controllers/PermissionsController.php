<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class PermissionsController extends Controller
{
    public function index(): View
    {
        return view('permissions.index');
    }
}
