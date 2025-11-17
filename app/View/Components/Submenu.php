<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class Submenu extends Component
{
    public function __construct(
        public string $title,
        public array $menu,
        public int $deviceId,
        public string $currentTab,
        public string $selected
    ) {
    }

    /**
     * Determine if the given option is the current selected option.
     */
    public function isSelected(string $url): bool
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
     */
    public function render(): View
    {
        return view('components.submenu');
    }
}
