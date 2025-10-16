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
    public bool $is_svg = false;
    public string $image;

    public function __construct(
        public string $responsive = '',
        ?string $image = null,
        public ?string $text = null,
    ) {
        $this->image = $image ?? (string) LibrenmsConfig::get('title_image');
        $this->text ??= LibrenmsConfig::get('project_name');
        $this->is_svg = str_ends_with($this->image, '.svg') && ! str_contains($this->image, '//');

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
