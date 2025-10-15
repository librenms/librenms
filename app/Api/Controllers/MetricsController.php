<?php

namespace App\Api\Controllers;

use Illuminate\Http\Request;

class MetricsController
{
    public function devices(Request $request)
    {
        // Forward to the new MetricsApi controller for implementation
        return app()->call([\App\Api\Controllers\MetricsApi\Controller::class, 'devices'], ['request' => $request]);
    }
}
