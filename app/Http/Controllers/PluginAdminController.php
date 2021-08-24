<?php

namespace App\Http\Controllers;

use App\Models\Plugin;
use App\Plugins\PluginManager;

class PluginAdminController extends Controller
{
    public function __invoke(PluginManager $manager): \Illuminate\Contracts\View\View
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
