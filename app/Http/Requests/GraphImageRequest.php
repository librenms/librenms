<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use LibreNMS\Exceptions\GraphException;
use LibreNMS\Exceptions\InvalidGraphInput;
use LibreNMS\Exceptions\UnauthorizedGraph;
use LibreNMS\Util\Graph;

/**
 * Graph request for the image endpoint, failures respond with an error image
 */
class GraphImageRequest extends GraphRequest
{
    protected function failedAuthorization(): never
    {
        $this->respondWithErrorImage(new UnauthorizedGraph);
    }

    protected function failedValidation(Validator $validator): never
    {
        $this->respondWithErrorImage(new InvalidGraphInput($validator->errors()->first()));
    }

    private function respondWithErrorImage(GraphException $e): never
    {
        $image = Graph::errorImage($this->graphParams(), $e);

        throw new HttpResponseException(response($image->data, $e->httpStatus(), ['Content-Type' => $image->contentType()]));
    }
}
