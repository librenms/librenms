<div class="form-group has-feedback {{ $config->class }}">
    <label for="{{ $config->name }}" class="col-sm-4 control-label">{{ $config->getDescription() }}</label>
    @if($config->hasHelp())
        <div data-toggle="tooltip" title="{{ $config->getHelp() }}" class="toolTip fa fa-fw fa-lg fa-question-circle"></div>
    @endif
    <div class="col-sm-6 col-lg-4">
        <input id="{{ $config->name }}" class="form-control"
               onkeypress="return (event.charCode === 8 || event.charCode === 0) ? null : event.charCode >= 48 && event.charCode <= 57"
               type="text" name="{{ $config->name }}" value="{{ $config->value }}" data-original="{{ $config->value }}" @if($config->required) required @endif>
        <span class="form-control-feedback"></span>
    </div>
</div>
