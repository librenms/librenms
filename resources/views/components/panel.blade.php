<div {{ $attributes->merge(['class' => $panelClass()]) }}>
    @if (isset($heading))
        <div {{ $heading->attributes->class(['panel-heading']) }}>
            {{ $heading }}
        </div>
    @elseif ($title)
        <div {{ $titleIsSlot() ? $title->attributes->class(['panel-heading']) : 'class=panel-heading' }}>
            <h3 class="panel-title">{{ $title }}</h3>
        </div>
    @endif

    @if ($slot->isNotEmpty())
        <div {{ $slot->attributes->class(['panel-body']) }}>
            {{ $slot }}
        </div>
    @endif

    @isset($footer)
        <div {{ $footer->attributes->class(['panel-footer']) }}>
            {{ $footer }}
        </div>
    @endisset
</div>
