<?php

namespace App\Plugins\ExamplePlugin;

use App\Plugins\Hooks\MenuEntryHook;

// this will create a menu entry in the plugin menu
// it should generally just be a
class Menu extends MenuEntryHook
{
    // point to the view for your plugin's settings
    // this is the default name so you can create the blade file as in this plugin
    // by ommitting the variable, or point to another one

//    public string $view = 'resources.views.menu';

    // this will determine if the menu entry should be shown to the user
    public function authorize(\App\Models\User $user, array $settings = []): bool
    {
        // menu entry shown if users has the global-read role and there is a setting that has > one entries in it
//        return $user->can('global-read') && isset($settings['some_data']) && count($settings['some_data']) > 0;

        return true; // allow every logged in user
    }

    // override the data function to add additional data to be accessed in the view
    // inside the blade, all variables will be named based on the key in the returned array
    public function data(array $settings = []): array
    {
        // inject settings and count how many we have so we can display it in the menu

        return [
            'count' => count($settings),
        ];
    }
}
