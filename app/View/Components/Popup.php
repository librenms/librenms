<?php

namespace App\View\Components;

use Illuminate\View\Component;
use LibreNMS\Config;

class Popup extends Component
{
    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return Config::get('web_mouseover', true)
            ? view('components.popup')
            : view('components.nopopup');
    }
}
