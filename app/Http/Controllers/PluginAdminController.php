<?php

namespace App\Http\Controllers;

use App\Models\Plugin;
use App\Plugins\PluginManager;
use Illuminate\Http\Request;

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

        return view('plugins.admin',[
            'plugins' => $plugins
        ]);
    }

    public function update(Request $request, Plugin $plugin): \Illuminate\Http\RedirectResponse
    {
        $validated = $this->validate($request, [
            'plugin_active' => 'in:0,1'
        ]);

        $plugin->fill($validated)->save();

        return redirect()->route('plugin.admin');
    }
}
