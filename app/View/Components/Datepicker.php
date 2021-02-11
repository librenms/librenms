<?php

namespace App\View\Components;

use DateTimeInterface;
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
        $this->from = $from instanceof DateTimeInterface ? $from->getTimestamp() : $from;
        $this->to = $to instanceof DateTimeInterface ? $to->getTimestamp() : $to;
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
