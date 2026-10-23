{{--
    Pick one billable source: a source type select, a device filter and a select per type.
    Only the select of the chosen type is enabled, so the form posts a single source_type + source_id.

    $labelCols     bootstrap columns of the labels
    $selectedType  source type alias to preselect
    $selected      initial select2 values: ['device' => [...], '<source type>' => [...]]
--}}
@php
    $sourceTypes = \App\Models\Bill::sourceTypes();
    $labelCols ??= 4;
    $selected ??= [];
    $selectedType = old('source_type', $selectedType ?? array_key_first($sourceTypes));
@endphp
<div class="form-group">
    <label class="col-sm-{{ $labelCols }} control-label" for="source_type">{{ __('Source') }}</label>
    <div class="col-sm-{{ 12 - $labelCols }}">
        <select class="form-control input-sm" id="source_type" name="source_type">
            @foreach($sourceTypes as $type => $class)
                <option value="{{ $type }}" @selected($type === $selectedType)>{{ $class::billingTypeName() }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-group">
    <label class="col-sm-{{ $labelCols }} control-label" for="device">{{ __('Device') }}</label>
    <div class="col-sm-{{ 12 - $labelCols }}">
        <select class="form-control input-sm" id="device"></select>
    </div>
</div>
@foreach($sourceTypes as $type => $class)
    <div class="form-group bill-source @error('source_id') has-error @enderror" data-source-type="{{ $type }}">
        <label class="col-sm-{{ $labelCols }} control-label" for="source-{{ $type }}">{{ $class::billingTypeName() }}</label>
        <div class="col-sm-{{ 12 - $labelCols }}">
            <select class="form-control input-sm" id="source-{{ $type }}" name="source_id"></select>
            <span class="help-block">{{ $errors->first('source_id') }}</span>
        </div>
    </div>
@endforeach
<script>
    $(function () {
        const sourceData = function (params) {
            params.device = $('#device').val();
            return params;
        };
        init_select2('#device', 'device', {}, @js($selected['device'] ?? null), @js(__('Select Device')), {width: '100%'});
        @foreach($sourceTypes as $type => $class)
        init_select2(@js('#source-' . $type), @js($class::billingSelectType()), sourceData, @js($selected[$type] ?? null), @js(__('Select :type', ['type' => $class::billingTypeName()])), {width: '100%'});
        @endforeach
        $('#device').on('change', function () {
            $('.bill-source select').val(null).trigger('change');
        });
        const sourceChanged = function () {
            const type = $('#source_type').val();
            $('.bill-source').each(function () {
                const active = $(this).data('source-type') === type;
                $(this).toggle(active);
                $(this).find('select').prop('disabled', !active); // only the visible source is submitted
            });
        };
        $('#source_type').select2({theme: 'bootstrap', width: '100%', minimumResultsForSearch: Infinity}).on('change', sourceChanged);
        sourceChanged();
    });
</script>
