<?php

namespace App\Http\Controllers;

use App\Models\Plugin;
use Illuminate\Http\Request;

class PluginLegacyController extends Controller
{
    public function redirect(Request $request, ?string $pluginName = null): \Illuminate\Http\RedirectResponse
    {
        if ($request->get('view') == 'admin') {
            return redirect()->route('plugin.admin')->setStatusCode(301);
        }

        if ($resolved_plugin_name = $request->get('p', $pluginName)) {
            return redirect()->route('plugin.legacy', ['plugin' => $resolved_plugin_name])->setStatusCode(301);
        }

        return redirect()->route('plugin.admin');
    }

    public function __invoke(?Plugin $plugin): \Illuminate\Contracts\View\View
    {
        if (! empty($plugin)) {
            $plugin_path = \LibreNMS\Config::get('plugin_dir') . '/' . $plugin->plugin_name . '/' . $plugin->plugin_name . '.inc.php';

            if (is_file($plugin_path)) {
                $init_modules = ['web', 'auth'];
                require base_path('/includes/init.php');

                chdir(base_path('html'));
                ob_start();
                include $plugin_path;
                $output = ob_get_contents();
                ob_end_clean();
                chdir(base_path());
            }
        }

        return view('plugins.legacy', [
            'title' => $plugin->plugin_name ?? trans('plugins.errors.not_exist'),
            'content' => $output ?? 'This plugin is either disabled or not available.',
        ]);
    }
}
