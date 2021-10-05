<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use LibreNMS\Validator;

class ValidateController extends Controller
{
    public function index(): View
    {
        $validator = new Validator();
        $validator->validate();

        $short_size = 10;

        return view('validate.index', compact('validator', 'short_size'));
    }

    public function fix(Request $request, string $class): RedirectResponse
    {
        $class = "\LibreNMS\Validations\\$class";

        if (! class_exists($class)) {
            abort(404);
        }

        $validator = new $class();

        if (! method_exists($validator, 'fix')) {
            abort(404);
        }

        $validator->fix(new Validator());

        return redirect()->back();
    }
}
