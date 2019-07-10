<div class="form-group has-feedback {{ $config->class }}">
    <label for="{{ $config->name }}" class="col-sm-4 control-label" title="{{ $config->name }}">{{ $config->getDescription() }}</label>
    @if($config->hasHelp())
        <div data-toggle="tooltip" title="{{ $config->getHelp() }}" class="toolTip fa fa-fw fa-lg fa-question-circle"></div>
    @endif
    <div class="col-sm-6 col-lg-4 col-">
        <select id="{{ $config->name }}" class="form-control bootselect" name="{{ $config->name }}" data-original="{{ $config->value }}"
            @if(in_array($config->name, $readonly)) disabled title="@lang('settings.readonly')" @endif
        >
            @foreach($config->getOptions() as $option => $text)
                <option value="{{ $option }}" @if($config->value == $option) selected @endif>{{ $text }}</option>
            @endforeach
        </select>
        <span class="form-control-feedback"></span>
    </div>
</div>
