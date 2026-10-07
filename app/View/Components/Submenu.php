<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class Submenu extends Component
{
    public function __construct(
        public string $title,
        public array $menu,
        public int $deviceId = 0,
        public string $currentTab = '',
        public string $selected = ''
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
     * Build a device tab url, moving any query string out of the vars route parameter
     */
    public function link(string $url): string
    {
        $parsed_url = parse_url($url);
        parse_str($parsed_url['query'] ?? '', $query);

        return route('device', [
            'device' => $this->deviceId,
            'tab' => $this->currentTab,
            'vars' => $parsed_url['path'] ?? '',
            ...$query,
        ]);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.submenu');
    }
}
