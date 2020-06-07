<div class="d-flex flex-row justify-content-around">
    @foreach($steps as $step => $controller)
    <div>
        <a href="{{ route('install.' . $step) }}"
           id="install-{{ $step }}-button"
           class="btn btn-primary btn-circle @if(!$controller::enabled($steps)) disabled @endif"
           title="@lang("install.$step.title")"
        >
            <i class="fa fa-lg {{ $controller::icon() }}"></i>
        </a>
    </div>
    @endforeach
</div>
