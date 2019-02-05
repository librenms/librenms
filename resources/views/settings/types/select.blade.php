<div class="form-group has-feedback {{ $class }}">
    <label for="{{ $name }}" class="col-sm-4 control-label">{{ $description }}</label>
    @if($help)
        <div data-toggle="tooltip" title="{{ $help }}" class="toolTip fa fa-fw fa-lg fa-question-circle"></div>
    @endif
    <div class="col-sm-6 col-lg-4 col-">
        <select id="{{ $name }}" class="form-control bootselect" name="{{ $name }}" data-original="{{ $value }}">
            @foreach($options as $option => $text)
                <option value="{{ $option }}" @if($value == $option) selected @endif>{{ $text }}</option>
            @endforeach
        </select>
        <span class="form-control-feedback"></span>
    </div>
</div>
