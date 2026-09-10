<?php

use App\Models\Device;
use Illuminate\Support\Facades\Gate;

if (Gate::allows('viewAll', Device::class)) {
    $auth = 1;
}
