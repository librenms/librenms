<form role="form" class="dashboard-widget-settings" onsubmit="widget_settings(this); return false;">
    @csrf
    @yield('form')

    @hassection('form')
        <div style="margin-top: 8px;">
                <button type="submit" class="btn btn-primary pull-right">@lang('Save')</button>
        </div>
    @else
        No settings for this widget
    @endif
</form>

@yield('javascript')
