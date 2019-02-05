<div class="form-group has-feedback {{ $class }}">
    <label for="{{ $name }}" class="col-sm-4 control-label">{{ $description }}</label>
    @if($help)
        <div data-toggle="tooltip" title="{{ $help }}" class="toolTip fa fa-fw fa-lg fa-question-circle"></div>
    @endif
    <div class="col-sm-6 col-lg-4">
        <input id="{{ $name }}" class="form-control validation" type="password" name="{{ $name }}" value="{{ $value }}" data-config_id="{{ $name }}" @if($pattern)pattern="{{ $pattern }}"@endif>
        <span class="form-control-feedback"></span>
    </div>
</div>
