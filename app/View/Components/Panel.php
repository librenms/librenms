<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Panel extends Component
{
    /**
     * The Panel title.
     *
     * @var string
     */
    public $title;

    /**
     * The Panel body class.
     *
     * @var string
     */
    public $body_class;

    /**
     * The Panel footer class.
     *
     * @var string
     */
    public $footer_class;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($title = null, $bodyClass = null, $footerClass = null)
    {
        $this->title = $title;
        $this->body_class = $bodyClass;
        $this->footer_class = $footerClass;
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
