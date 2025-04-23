@aware(['accordionId'])
@props(['id', 'title'])

<div class="accordion-item">
    <h3 class="accordion-header">
        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{$id}}" aria-expanded="true" aria-controls="collapse{{$id}}">
            {{ $title }}
        </button>
    </h3>
    <div class="accordion-collapse collapse" id="collapse{{$id}}" data-bs-parent="#accordion{{$accordionId}}">
        <div class="accordion-body">
            {{ $slot }}
        </div>
    </div>
</div>
