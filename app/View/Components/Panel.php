<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Panel extends Component
{
    /**
     * Create a new component instance.
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
     */
    public function render(): \Illuminate\View\View|string
    {
        return view('components.panel');
    }
}
