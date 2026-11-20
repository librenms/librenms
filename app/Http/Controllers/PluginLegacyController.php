<?php

namespace App\Http\Controllers;

use App\Models\Plugin;
use Illuminate\Http\Request;
use LibreNMS\Util\Url;

class PluginLegacyController extends Controller
{
    public function redirect(Request $request, ?string $pluginName = null): \Illuminate\Http\RedirectResponse
    {
        if ($request->input('view') == 'admin') {
            return redirect()->route('plugin.admin')->setStatusCode(301);
        }

        if ($resolved_plugin_name = $request->input('p', $pluginName)) {
            return redirect()->route('plugin.legacy', ['plugin' => $resolved_plugin_name])->setStatusCode(301);
        }

        return redirect()->route('plugin.admin');
    }

    public function __invoke(Request $request, ?Plugin $plugin): \Illuminate\Contracts\View\View
    {
        if (! empty($plugin)) {
            $plugin_path = \App\Facades\LibrenmsConfig::get('plugin_dir') . '/' . $plugin->plugin_name . '/' . $plugin->plugin_name . '.inc.php';

            if (is_file($plugin_path)) {
                require base_path('/includes/init.php');
                $vars = $this->pluginVars($request); // v1 plugins read request options from $vars

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

    /**
     * Legacy path options (/key=value/) plus query and post input, without credentials
     */
    private function pluginVars(Request $request): array
    {
        $vars = Url::parseLegacyPathVars($request->getRequestUri());

        foreach ($request->query() as $name => $value) {
            if (is_scalar($value)) {
                $vars[$name] = strip_tags((string) $value);
            }
        }

        $vars = array_replace($vars, $request->post());

        unset($vars['username'], $vars['password'], $vars['_token']);

        return $vars;
    }
}
