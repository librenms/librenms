<?php

namespace App\View\Components;

use App\Facades\LibrenmsConfig;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Logo extends Component
{
    public string $logo_hide_class;
    public string $logo_show_class;
    public ?string $custom_image;

    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $responsive = '',
    ) {
        $this->custom_image = LibrenmsConfig::get('title_image');
        [$this->logo_hide_class, $this->logo_show_class] = match ($this->responsive) {
            'sm' => ['tw:sm:hidden', 'tw:sm:inline-block'],
            'md' => ['tw:md:hidden', 'tw:md:inline-block'],
            'lg' => ['tw:lg:hidden', 'tw:lg:inline-block'],
            'xl' => ['tw:xl:hidden', 'tw:xl:inline-block'],
            '2xl' => ['tw:2xl:hidden', 'tw:2xl:inline-block'],
            default => ['', ''],
        };
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.logo');
    }
}
