<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Panel extends Component
{
    /**
     * Create a new component instance.
     *
     * @param  string  $title
     * @param  string  $body_class
     * @return void
     */
    public function __construct(
        /**
         * The Panel title.
         */
        public ?string $title = '',
        /**
         * The Panel body class.
         */
        public ?string $bodyClass = ''
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
