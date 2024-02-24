<?php

namespace App\Plugins\ExamplePlugin;

use App\Plugins\Hooks\PortTabHook;

// this will insert a tab into every port view
class PortTab extends PortTabHook
{
    // point to the view for your plugin's port plugin
    // this is the default name so you can create the blade file as in this plugin
    // by ommitting the variable, or point to another one

//    public string $view = 'resources.views.port-tab';

    // override the data function to add additional data to be accessed in the view
    // title is a required attribute and will be shown above your returned html from your blade file
    // inside the blade, all variables will be named based on the key in the returned array
    public function data(\App\Models\Port $port): array
    {
        // run any calculations here
        $total_delta = $port->ifOutOctets_delta + $port->ifInOctets_delta; // nonsense calculation :)

        return [
            'title' => 'Example Plugin',
            'port' => $port,
            'something' => 'this is a variable and can be accessed with {{ $something }}',
            'total' => $total_delta,
        ];
    }
}
