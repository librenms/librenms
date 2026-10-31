<?php

namespace App\Http\Requests;

use App\Data\Graphing\GraphParameters;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use LibreNMS\Util\Graph;

/**
 * Graph request for the image endpoint, failures respond with an error image
 */
class GraphImageRequest extends GraphRequest
{
    protected function failedAuthorization(): never
    {
        $this->errorImage(403, 'No Authorization', 'No Auth');
    }

    protected function failedValidation(Validator $validator): never
    {
        $this->errorImage(422, $validator->errors()->first(), 'Invalid Input');
    }

    private function errorImage(int $status, string $message, string $short): never
    {
        $image = Graph::errorImage(new GraphParameters($this->toVars()), $message, $short);

        throw new HttpResponseException(response($image->data, $status, ['Content-Type' => $image->contentType()]));
    }
}
