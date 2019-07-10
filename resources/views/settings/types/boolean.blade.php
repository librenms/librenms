<div class="form-group has-feedback {{ $config->class }}">
    <label for="{{ $config->name }}" class="col-sm-4 control-label" title="{{ $config->name }}">{{ $config->getDescription() }}</label>
    @if($config->hasHelp())
        <div data-toggle="tooltip" title="{{ $config->getHelp() }}" class="toolTip fa fa-fw fa-lg fa-question-circle"></div>
    @endif
    <div class="col-sm-6 col-lg-4">
        <input id="{{ $config->name }}" type="checkbox" name="{{ $config->name }}" @if($config->value) checked @endif data-on-text="Yes"
               data-off-text="No" data-size="small" data-original="{{ $config->value ? 1 : 0 }}">
    </div>
</div>
