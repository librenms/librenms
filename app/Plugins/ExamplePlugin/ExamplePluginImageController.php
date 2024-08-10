<?php

namespace App\Plugins\ExamplePlugin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ExamplePluginImageController extends Controller
{
    public static function image(Request $request, string $file)
    {
        //abort(301); //Check if it is running
        //var_dump($request);

        $file_path = base_path('/app/Plugins/ExamplePlugin' . '/resources/images/' . $file);

        if (! is_file($file_path)) {
            abort(404);
        }
        if (! is_readable($file_path)) {
            abort(403);
        }

        return response()->file($file_path);
    }
}
