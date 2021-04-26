<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Submenu extends Component
{
    /**
     * The submenu title.
     *
     * @var string
     */
    public $title;

    /**
     * The submenu menu.
     *
     * @var string
     */
    public $menu;

    /**
     * The submenu device_id.
     *
     * @var string
     */
    public $device_id;

    /**
     * The submenu current_tab.
     *
     * @var string
     */
    public $current_tab;

    /**
     * The submenu selected.
     *
     * @var string
     */
    public $selected;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($title, $menu, $deviceId, $currentTab, $selected)
    {
        $this->title = $title;
        $this->menu = $menu;
        $this->device_id = $deviceId;
        $this->current_tab = $currentTab;
        $this->selected = $selected;
    }

    /**
     * Determine if the given option is the current selected option.
     *
     * @param  string  $url
     * @return bool
     */
    public function isSelected($url)
    {
        return $url === $this->selected;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.submenu');
    }
}
