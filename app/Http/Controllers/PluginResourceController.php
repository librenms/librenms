<?php

namespace App\Http\Controllers;

use App\Models\Plugin;
use Illuminate\Http\Request;
use App\Plugins\Hooks\ResourceAccessorHook;
use PluginManager;

class PluginResourceController extends Controller
{
    public function getResource(Request $request, Plugin $plugin, string $path = null)
    {
        if (is_null($path)) {
            // not much we can do for this path
            abort(404);
        }

        // If we have a hook for resources, we call it.
        return PluginManager::call(ResourceAccessorHook::class, ['request' => $request, 'path' => $path], $plugin->plugin_name)->first() ?? abort(404);
        // if not, we return abort(404)
    }
}
