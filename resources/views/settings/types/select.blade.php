<div class="form-group has-feedback {{ $class }}">
    <label for="{{ $name }}" class="col-sm-4 control-label">{{ $description }}</label>
    @if($help)
        <div data-toggle="tooltip" title="{{ $help }}" class="toolTip fa fa-fw fa-lg fa-question-circle"></div>
    @endif
    <div class="col-sm-6 col-lg-4 col-">
        <select id="{{ $name }}" class="form-control bootselect" name="{{ $name }}">
            if (!empty($item['options'])) {
            $options = is_string($item['options']) ? Config::get($item['options']) : $item['options'];
            foreach ($options as $option) {
            @foreach($options as $option)
                <option value="{{ $option['value'] }}" @if($value == $option['value']) selected @endif>{{ $option['description'] }}</option>
            @endforeach
        </select>
        <span class="form-control-feedback"></span>
    </div>
</div>
