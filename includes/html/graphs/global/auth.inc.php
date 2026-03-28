<?php

use App\Models\Device;
use Illuminate\Support\Facades\Gate;

if (Gate::allows('viewAny', Device::class)) {
    $auth = 1;
}
