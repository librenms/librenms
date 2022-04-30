@props([
'header',
'footer',
])

<div {{ $attributes->class(['panel', 'panel-default']) }}>
@if (isset($header))
  <div {{ $header->attributes ? $header->attributes->class(['panel-heading']) : 'class="panel-heading"' }}>
    {{ $header }}
  </div>
@elseif (isset($title))
  <div class="panel-heading">
    <h3 class="panel-title">{{ $title }}</h3>
  </div>
@endif

@if (isset($slot) && !empty($slot->toHtml()))
  <div {{ $slot->attributes ? $slot->attributes->class(['panel-body']) : 'class="panel-body"' }}>
    {{ $slot }}
  </div>
@endif

@isset($table)
{{ $table }}
@endisset

@isset($footer)
  <div {{ $footer->attributes ? $footer->attributes->class(['panel-footer']) : 'class="panel-footer"' }}>
    {{ $footer }}
  </div>
@endisset
</div>
