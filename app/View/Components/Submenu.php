<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Submenu extends Component
{
    /**
     * Create a new component instance.
     *
     * @param  string  $title
     * @param  string  $menu
     * @param  string  $device_id
     * @param  string  $current_tab
     * @param  string  $selected
     * @return void
     */
    public function __construct(
        /**
         * The submenu title.
         */
        public $title,
        /**
         * The submenu menu.
         */
        public $menu,
        /**
         * The submenu device_id.
         */
        public $device_id,
        /**
         * The submenu current_tab.
         */
        public $current_tab,
        /**
         * The submenu selected.
         */
        public $selected
    ) {
    }

    /**
     * Determine if the given option is the current selected option.
     *
     * @param  string  $url
     * @return bool
     */
    public function isSelected($url)
    {
        // check for get parameters
        $parsed_url = parse_url($url);
        if (isset($parsed_url['query']) && $parsed_url['path'] === $this->selected) {
            parse_str($parsed_url['query'], $vars);
            $request = request();
            foreach ($vars as $key => $value) {
                if ($request->input($key) !== $value) {
                    return false;
                }
            }

            return true;
        }

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
