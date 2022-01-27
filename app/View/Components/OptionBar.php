<?php

namespace App\View\Components;

use Illuminate\View\Component;

class OptionBar extends Component
{
    /**
     * Name of the option bar
     *
     * @var string
     */
    public $name;
    /**
     * Entries to show on the option bar
     * [
     *   'name' => ['text' => 'Display Text', 'link' => 'https://...'],
     * ]
     *
     * @var array
     */
    public $options;
    /**
     * Selected option
     *
     * @var mixed
     */
    public $selected;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(string $name = '', array $options = [], $selected = null)
    {
        $this->name = $name;
        $this->options = $options;
        $this->selected = $selected;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.option-bar');
    }
}
