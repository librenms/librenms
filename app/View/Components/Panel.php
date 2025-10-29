<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Panel extends Component
{
    /**
     * Create a new component instance.
     *
     * @param  string  $title
     * @param  string  $bodyClass
     * @return void
     */
    public function __construct(
        /**
         * The Panel title.
         */
        public $title = null,
        /**
         * The Panel body class.
         */
        public $body_class = null
    ) {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.panel');
    }
}
