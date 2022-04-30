<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use LibreNMS\Validator;

class ValidateController extends Controller
{
    public function index(): View
    {
        return view('validate.index');
    }

    public function runValidation(): JsonResponse
    {
        $validator = new Validator();
        $validator->validate();

        return response()->json($validator->toArray());
    }
}
