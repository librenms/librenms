<form role="form" class="dashboard-widget-settings" onsubmit="widget_settings(this); return false;">
    @csrf
    @yield('form')

    <div class="form-group">
        <label for="refresh-{{ $id }}" class="control-label">@lang('Widget refresh interval (s)')</label>
        <input type="number" step="1" min="1" class="form-control" name="refresh" id="refresh-{{ $id }}" value="{{ $refresh }}">
    </div>
    <div style="margin-top: 8px;">
            <button type="submit" class="btn btn-primary pull-right">@lang('Save')</button>
    </div>
</form>

@yield('javascript')
