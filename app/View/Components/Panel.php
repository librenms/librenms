<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\ComponentSlot;

class Panel extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public ?string $type = 'default',
        public mixed $title = null,
    ) {
    }

    /**
     * Determine if the title is a slot with attributes or just a string.
     */
    public function titleIsSlot(): bool
    {
        return $this->title instanceof ComponentSlot;
    }

    /**
     * Resolve the panel's base CSS classes.
     */
    public function panelClass(): string
    {
        return match ($this->type) {
            'primary' => 'panel panel-primary',
            'success' => 'panel panel-success',
            'info' => 'panel panel-info',
            'warning' => 'panel panel-warning',
            'danger' => 'panel panel-danger',
            default => 'panel panel-default',
        };
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): \Illuminate\View\View|string
    {
        return view('components.panel');
    }
}
