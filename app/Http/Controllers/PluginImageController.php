<?php

namespace App\Http\Controllers;

use App\Models\Plugin;
use Illuminate\Http\Request;

class PluginImageController extends Controller
{
    public function image(Request $request, Plugin $plugin, string $file)
    {
        $file_path = base_path('/app/Plugins/' . $plugin->plugin_name . '/resources/images/' . $file);

        if (! is_file($file_path)) {
            abort(404);
        }
        if (! is_readable($file_path)) {
            abort(403);
        }

        return response()->file($file_path);
    }
}
