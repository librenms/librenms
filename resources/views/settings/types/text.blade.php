<div class="form-group has-feedback {{ $config->class }}">
    <label for="{{ $config->name }}" class="col-sm-4 control-label" title="{{ $config->name }}">{{ $config->getDescription() }}</label>
    <div class="col-sm-6 col-lg-4">
        <input type="text" class="form-control validation"
               id="{{ $config->name }}"
               name="{{ $config->name }}"
               value="{{ $config->value }}"
               data-current="{{ $config->value }}"
               @if($config->pattern)pattern="{{ $config->pattern }}"
               @endif @if($config->required) required @endif
               @if($config->overridden) disabled title="@lang('settings.readonly')" @endif
        >
        <span class="form-control-feedback"></span>
    </div>
    <div class="col-sm-2">
        <button class="config-undo btn btn-primary"
                title="@lang('Undo')"
                data-target="{{ $config->name }}"
                style="display: none;"
        ><i class="fa fa-undo"></i></button>
        <button class="config-default btn btn-default"
                title="@lang('Reset to default')"
                data-target="{{ $config->name }}"
                @if(empty($config->default) || $config->value == $config->default) style="display: none;" @endif
        ><i class="fa fa-refresh"></i></button>
        @if($config->hasHelp())
            <div data-toggle="tooltip" title="{{ $config->getHelp() }}" class="toolTip fa fa-fw fa-lg fa-question-circle"></div>
        @endif
    </div>
</div>
