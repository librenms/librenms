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
     * @param  array<array{key: string, label: string, type: string, endpoint?: string, options?: string[], params?: array<string, string>}>  $fields
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

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.filter');
    }
}
