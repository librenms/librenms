<?php

namespace App\Http\Controllers;

use App\Models\Plugin;
use Illuminate\Http\Request;

class PluginImageController extends Controller
{
    public function image(Request $request, Plugin $plugin, string $file) {
        $file_path = base_path( '/app/Plugins/' . $plugin->plugin_name . '/resources/images/' . $file);
        return response()->file($file_path);
    }
}
