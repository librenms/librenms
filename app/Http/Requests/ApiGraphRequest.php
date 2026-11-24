<?php

namespace App\Http\Requests;

/**
 * A graph requested through API v1, which only takes query parameters.
 */
class ApiGraphRequest extends GraphRequest
{
    protected function prepareForValidation(): void
    {
        // the api path holds no legacy graph variables
    }
}
