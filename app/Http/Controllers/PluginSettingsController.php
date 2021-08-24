<?php

namespace App\Http\Controllers;

use App\Plugins\Hooks\SettingsHook;
use App\Plugins\PluginManager;

class PluginSettingsController extends Controller
{
    public function __invoke(string $pluginName, PluginManager $manager): \Illuminate\Contracts\View\View
    {
        if (! $manager->pluginExists($pluginName) || ! $manager->hasHooks(SettingsHook::class, [], $pluginName)) {
            abort(404, trans('plugins.errors.not_exist', ['plugin' => $pluginName]));
        } elseif (! $manager->pluginEnabled($pluginName)) {
            abort(404, trans('plugins.errors.disabled', ['plugin' => $pluginName]));
        }

        $data = array_merge([
            // fallbacks to prevent exceptions
            'title' => 'No title set',
            'settings_view' => 'plugins.missing',
            'settings' => [],
        ],
            (array) $manager->call(SettingsHook::class, [], $pluginName)->first()
        );

        return view('plugins.settings', $data);
    }
}
