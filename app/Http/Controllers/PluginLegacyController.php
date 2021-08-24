<?php

namespace App\Http\Controllers;

use App\Models\Plugin;
use Illuminate\Http\Request;

class PluginLegacyController extends Controller
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @param  string|null  $pluginName
     * @return \Illuminate\Http\RedirectResponse|void
     */
    public function redirect(Request $request, ?string $pluginName)
    {
        if ($request->get('view') == 'admin') {
            return redirect(null, 301)->route('plugin.admin');
        }

        if ($plugin = $request->get('p', $pluginName)) {
            return redirect(null, 301)->route('plugin.legacy', ['pluginName' => $plugin]);
        }

        abort(404);
    }

    public function __invoke(string $pluginName)
    {
        $plugin = Plugin::firstWhere('plugin_name', $pluginName);

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
