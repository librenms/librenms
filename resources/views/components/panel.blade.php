{{-- a heading with nothing below it is the bottom of the panel: no divider, and the panel's corners --}}
@php($headingClass = 'panel-heading tw:last:border-b-0! tw:last:rounded-b-[inherit]!')
<div {{ $attributes->merge(['class' => $panelClass()]) }}>
    @if (isset($heading))
        <div {{ $heading->attributes->class($headingClass) }}>
            {{ $heading }}
        </div>
    @elseif ($title)
        <div {{ $titleIsSlot() ? $title->attributes->class($headingClass) : new \Illuminate\View\ComponentAttributeBag(['class' => $headingClass]) }}>
            <h3 class="panel-title">{{ $title }}</h3>
        </div>
    @endif

    @if ($slot->isNotEmpty())
        <div {{ $slot->attributes->class(['panel-body']) }}>
            {{ $slot }}
        </div>
    @endif

    @isset($table)
        {{ $table }}
    @endisset

    @isset($footer)
        <div {{ $footer->attributes->class(['panel-footer']) }}>
            {{ $footer }}
        </div>
    @endisset
</div>
