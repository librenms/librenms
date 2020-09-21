<form role="form" class="dashboard-widget-settings" onsubmit="widget_settings(this); return false;">
    @csrf
    @yield('form')

    @hassection('form')
        <div class="form-group">
            <label for="refresh-{{ $id }}" class="control-label">@lang('Widget refresh')</label>
            <input type="text" class="form-control" name="refresh" id="refresh-{{ $id }}" placeholder="@lang('Custom refresh for widget')" value="{{ $refresh }}">
        </div>
        <div style="margin-top: 8px;">
                <button type="submit" class="btn btn-primary pull-right">@lang('Save')</button>
        </div>
    @else
        No settings for this widget
    @endif
</form>

@yield('javascript')
