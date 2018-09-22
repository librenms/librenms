<form role="form" onsubmit="widget_settings(this); return false;">
    @yield('form')

    @hassection('form')
        <br style="clear:both;"/>
        <div class="form-group">
            <div class="col-sm-2 col-sm-offset-8">
                <button type="submit" class="btn btn-default">@lang('Apply')</button>
            </div>
        </div>
    @else
        No settings for this widget
    @endif
</form>

@yield('javascript')
