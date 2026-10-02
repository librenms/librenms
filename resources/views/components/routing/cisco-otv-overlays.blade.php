<div class="list-group tw:mb-0" id="{{ $id }}">
    @forelse($overlays as $overlay)
        <a @class(['list-group-item', 'list-group-item-danger' => ! $overlay['is_normal']]) data-toggle="collapse" data-target="#{{ $id }}-{{ $overlay['id'] }}" data-parent="#{{ $id }}">
            {{ $overlay['label'] }} - {{ $overlay['detail'] }}
            @if($overlay['is_normal'])
                <span class="text-success tw:float-right">{{ __('Normal') }}</span>
            @else
                <span class="tw:float-right">{{ $overlay['error'] }} - <span class="text-danger">{{ __('Alert') }}</span></span>
            @endif
        </a>
        <div id="{{ $id }}-{{ $overlay['id'] }}" class="sublinks collapse">
            @foreach($overlay['adjacencies'] as $adjacency)
                <a @class(['list-group-item small', 'list-group-item-danger' => ! $adjacency['is_normal']])>
                    <i class="fa fa-chevron-right" aria-hidden="true"></i> {{ $adjacency['label'] }} - {{ $adjacency['detail'] }}
                    @if($adjacency['is_normal'])
                        <span class="text-success tw:float-right">{{ __('Normal') }}</span>
                    @else
                        <span class="tw:float-right">{{ $adjacency['error'] }} - <span class="text-danger">{{ __('Alert') }}</span></span>
                    @endif
                </a>
            @endforeach
        </div>
    @empty
        <div class="list-group-item text-muted">{{ __('No OTV overlays found.') }}</div>
    @endforelse
</div>
