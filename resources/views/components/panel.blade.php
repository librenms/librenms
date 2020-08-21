<div {{ $attributes->merge(['class' => 'panel panel-default']) }}>
@if (isset($heading))
  <div class="panel-heading">
    {{ $heading }}
  </div>
@elseif (isset($title))
  <div class="panel-heading">
    <h3 class="panel-title">{{ $title }}</h3>
  </div>
@endif

@if (isset($slot) && !empty($slot->toHtml()))
  <div class="panel-body {{ $body_class }}">
    {{ $slot }}
  </div>
@endif

@isset($table)
{{ $table }}
@endisset

@isset($footer)
  <div class="panel-footer {{ $footer_class }}">
    {{ $footer }}
  </div>
@endisset
</div>
