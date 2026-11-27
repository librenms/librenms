<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Symfony\Component\Yaml\Yaml;

class ApiV1SpecificationController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $specification = Yaml::parseFile(resource_path('openapi/v1.yaml'), Yaml::PARSE_OBJECT_FOR_MAP);
        $specification->servers[0]->url = url('api/v1');

        return response()->json($specification, headers: ['Cache-Control' => 'private, no-store']);
    }
}
