<?php

namespace App\Http\Controllers;

use App\Models\Plugin;
use App\Plugins\Hooks\ResourceAccessorHook;
use Illuminate\Http\Request;
use PluginManager;

class PluginResourceController extends Controller
{
    /**
     * Get a resource (file, image, etc) from a plugin and provide it to the user.
     *
     * @param  Request  $request
     * @param  Plugin  $plugin
     * @param  string  $path  | null
     * @return string | null
     */
    public function getResource(Request $request, Plugin $plugin, string $path = null)
    {
        // if no path is provide, we abort(404), not much we can do.
        if (is_null($path)) {
            abort(404);
        }

        // If we have a hook for resources implemented in the passed plugin, we call it.
        // if not, we abort(404).
        $response = PluginManager::call(ResourceAccessorHook::class, ['request' => $request, 'path' => $path], $plugin->plugin_name)->first();
        if (! is_null($response)) {
            return $response;
        }
        abort(404);
    }
}
