<div class="container-fluid" style="padding-left: 0; padding-right: 0;">
    @foreach($actions as $row)
        <div>
            @foreach($row as $action)
                <div class="col-xs-1">
                    <a href="{{ $action['href'] }}" @if($action['external'] ?? true)target="_blank" rel="noopener" @endif title="{{ $action['title'] }}">
                        <i class="fa fa-lg icon-theme {{ $action['icon'] ?? 'fa-external-link' }}"></i>
                    </a>
                </div>
            @endforeach
        </div>
    @endforeach
</div>
