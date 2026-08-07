{{-- Quiet view-mode empty state; Configure opens real widget settings (settings=1). --}}
<div class="lnms-widget-needs-config" data-widget-id="{{ $id }}">
    <p class="lnms-widget-needs-config__msg">{{ $message }}</p>
    @if ($canConfigure ?? true)
        <button type="button"
                class="btn btn-sm btn-default lnms-widget-needs-config__btn"
                data-widget-configure
                data-widget-id="{{ $id }}">
            <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
            {{ $actionLabel ?? __('Configure') }}
        </button>
    @endif
</div>
