<div {{ $attributes->merge(['class' => 'panel panel-default']) }}>
@isset($title)
  <div class="panel-heading">
    <h3 class="panel-title">{{ $title }}</h3>
  </div>
@endisset
  <div class="panel-body">
    {{ $slot }}
  </div>
</div>
