<div class="form-group has-feedback {{ $class }}">
    <label for="{{ $name }}" class="col-sm-4 control-label">{{ $description }}</label>
    @if($help)
        <div data-toggle="tooltip" title="{{ $help }}" class="toolTip fa fa-fw fa-lg fa-question-circle"></div>
    @endif
    <div class="col-sm-6 col-lg-4">
        <input id="{{ $name }}" class="form-control" onkeypress="return (event.charCode === 8 || event.charCode === 0) ? null : event.charCode >= 48 && event.charCode <= 57" type="text" name="{{ $name }}" value="{{ $value }}" data-original="{{ $value }}">
        <span class="form-control-feedback"></span>
    </div>
</div>
