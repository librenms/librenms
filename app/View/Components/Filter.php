<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Filter extends Component
{
    /**
     * Create a new component instance.
     *
     * @param  string  $name
     * @param  array<array{key: string, label: string, type: string, search?: bool, endpoint?: string, options?: string[]|array<string, string>, params?: array<string, string>}>  $fields
     * @param  bool  $reload
     * @param  bool  $hide
     * @param  array<string, string>  $initial
     */
    public function __construct(
        public string $name,
        public array $fields,
        public bool $reload = false,
        public bool $hide = false,
        public array $initial = [],
    ) {
    }

    public function getFieldIcon(?string $type): string
    {
        return match ($type) {
            'select' => 'fa-solid fa-square-caret-down',
            'boolean' => 'fa-solid fa-toggle-on',
            'number' => 'fa-solid fa-hashtag',
            'date' => 'fa-solid fa-calendar-days',
            default => 'fa-solid fa-magnifying-glass',
        };
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.filter');
    }
}
