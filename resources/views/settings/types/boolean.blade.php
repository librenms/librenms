<div class="form-group has-feedback {{ $class }}">
    <label for="{{ $name }}" class="col-sm-4 control-label">{{ $description }}</label>
    @if($help)
        <div data-toggle="tooltip" title="{{ $help }}" class="toolTip fa fa-fw fa-lg fa-question-circle"></div>
    @endif
    <div class="col-sm-6 col-lg-4">
        <input id="{{ $name }}" type="checkbox" name="{{ $name }}" @if($value) checked @endif data-on-text="Yes"
               data-off-text="No" data-size="small" data-config_id="{{ $name }}">
    </div>
</div>
