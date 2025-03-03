<div class="col-xs-1">
    <a href="{{ $href }}" @if($external ?? true)target="_blank" rel="noopener" @endif title="{{ $title }}">
        <i class="fa fa-lg icon-theme {{ $icon ?? 'fa-external-link' }}"></i>
    </a>
</div>
