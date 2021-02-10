<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Datepicker extends Component
{
    public $from;
    public $to;

    /**
     * Create a new component instance.
     *
     * @param  null  $from
     * @param  null  $to
     */
    public function __construct($from = null, $to = null)
    {
        $this->from = $from;
        $this->to = $to;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view('components.datepicker');
    }
}
