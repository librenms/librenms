@aware(['accordionId'])
@props(['id', 'title', 'open' => false])

<div class="accordion-header">
    <button class="accordion-toggle" type="button" data-toggle="collapse" data-target="#collapse{{$id}}" onClick="$('#accordionPM{{$id}}').toggleClass('fa-minus').toggleClass('fa-plus');">
        @if($open)
        <span id="accordionPM{{$id}}" class="fa fa-minus"></span>{!! $title !!}
        @else
        <span id="accordionPM{{$id}}" class="fa fa-plus"></span>{!! $title !!}
        @endif
    </button>
</div>
@if($open)
<div class="accordion-body collapse in" id="collapse{{$id}}" aria-expanded="true">
    {{ $slot }}
</div>
@else
<div class="accordion-body collapse" id="collapse{{$id}}">
    {{ $slot }}
</div>
@endif
