<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\MetaDocument;
use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Support\Facades\Event;

class HealthController extends Controller
{
    public function __invoke(): MetaDocument
    {
        try {
            Event::dispatch(new DiagnosingHealth);
        } catch (\Throwable) {
            abort(503); // FormatJsonApiError keeps the details out of the response
        }

        return new MetaDocument(['status' => 'healthy']);
    }
}
