<div class="form-group has-feedback {{ $config->class }}">
    <label for="{{ $config->name }}" class="col-sm-4 control-label" title="{{ $config->name }}">{{ $config->getDescription() }}</label>
    @if($config->hasHelp())
        <div data-toggle="tooltip" title="{{ $config->getHelp() }}" class="toolTip fa fa-fw fa-lg fa-question-circle"></div>
    @endif
    <div class="col-sm-6 col-lg-4">
        <input id="{{ $config->name }}" class="form-control validation" type="text" name="{{ $config->name }}" value="{{ $config->value }}"
               data-original="{{ $config->value }}" data-config_id="{{ $config->name }}" @if($config->pattern)pattern="{{ $config->pattern }}"
               @endif @if($config->required) required @endif
               @if(in_array($config->name, $readonly)) disabled title="@lang('settings.readonly')" @endif
        >
        <span class="form-control-feedback"></span>
    </div>
</div>
