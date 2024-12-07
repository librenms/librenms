@extends('widgets.settings.base')

@section('form')
    <div class="form-group">
        <label for="title-{{ $id }}" class="control-label">{{ __('Widget title') }}</label>
        <input type="text" class="form-control" name="title" id="title-{{ $id }}" placeholder="{{ __('Custom title') }}" value="{{ $title }}">
    </div>
    <div class="form-group">
        <label for="custom-map-{{ $id }}" class="control-label">{{ __('Custom Map') }}</label>
        <select class="form-control" name="custom_map" id="custom_map-{{ $id }}" data-placeholder="{{ __('Select Map') }}">
            @if($map)
                <option value="{{ $map->custom_map_id }}" selected>{{ $map->name }}</option>
            @endif
        </select>
    </div>
    <div class="form-group">
        <label for="screenshot-{{ $id }}" class="control-label">{{ __('Screenshot Mode') }}</label>
        <input type="checkbox" class="form-control" name="screenshot" id="screenshot-{{ $id }}" value=0 data-size="normal" @if($screenshot) checked @endif>
    </div>
@endsection

@section('javascript')
    <script type="text/javascript">
        init_select2('#custom_map-{{ $id }}', 'custom-map', {});

        $('#screenshot-{{ $id }}')
            .bootstrapSwitch('offColor','danger')
            .on('switchChange.bootstrapSwitch', function (e, data) {
                let thisval = $(this).is(':checked') ? "1": "0";
                $('#screenshot-{{ $id }}').val(thisval);
            });
    </script>
@endsection
