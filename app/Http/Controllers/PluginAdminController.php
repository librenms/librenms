<?php

namespace App\Http\Controllers;

use App\Models\Plugin;
use LibreNMS\Interfaces\Plugins\PluginManagerInterface;

class PluginAdminController extends Controller
{
    public function __invoke(PluginManagerInterface $manager): \Illuminate\Contracts\View\View
    {
        // legacy v1 plugins
        \LibreNMS\Plugins::scanNew();
        \LibreNMS\Plugins::scanRemoved();

        // v2 cleanup
        $manager->cleanupPlugins();

        $plugins = Plugin::get();

        return view('plugins.admin', [
            'plugins' => $plugins,
        ]);
    }
}
