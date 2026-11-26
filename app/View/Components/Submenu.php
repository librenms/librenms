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

            return array_all($vars, fn ($value, $key) => ! ($request->input($key) !== $value));
        }

        return $url === $this->selected;
    }

    /**
     * Build a link for a device tab's selected view.
     */
    public function link(string $url): string
    {
        $parsed_url = parse_url($url);
        parse_str($parsed_url['query'] ?? '', $query);

        $view = $parsed_url['path'] ?? '';
        if ($this->currentTab === 'ports' && $view === 'portsecurity') {
            return route('device.ports.security', ['device' => $this->deviceId] + $query);
        }

        return route("device.$this->currentTab", [
            'device' => $this->deviceId,
            'view' => $view === '' ? null : $view,
        ] + $query);
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View
    {
        return view('components.submenu');
    }
}
