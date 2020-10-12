<div class="form-group @if($errors->has('name')) has-error @endif">
    <label for="name" class="control-label col-sm-3 col-md-2 text-nowrap">@lang('Name')</label>
    <div class="col-sm-9 col-md-10">
        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $service_template->name) }}">
        <span class="help-block">{{ $errors->first('name') }}</span>
    </div>
</div>

<div class="form-group @if($errors->has('device_group_id')) has-error @endif">
    <label for="device_group_id" class="control-label col-sm-3 col-md-2 text-nowrap">@lang('Device Group')</label>
    <div class="col-sm-9 col-md-10">
        <select class="form-control" id=device_group_id" name="device_group_id[]" multiple>
            @foreach($device_groups as $device_group)
                <option value="{{ $device_group->id }}" selected>{{ $device_group->name() }}</option>
            @endforeach
        </select>
        <span class="help-block">{{ $errors->first('device_group_id') }}</span>
    </div>
</div>

<div class="form-group @if($errors->has('type')) has-error @endif">
    <label for="type" class="control-label col-sm-3 col-md-2">@lang('Check Type')</label>
    <div class="col-sm-9 col-md-10">
        <select class="form-control" id=device_group_id" name="device_group_id[]" multiple>
            @foreach(list_available_services() as $current_service)
                <option value="{{ $current_service }}" selected>{{ $current_service() }}</option>
            @endforeach
        </select>
        <span class="help-block">{{ $errors->first('type') }}</span>
    </div>
</div>

<div class="form-group @if($errors->has('desc')) has-error @endif">
    <label for="desc" class="control-label col-sm-3 col-md-2 text-nowrap">@lang('Description')</label>
    <div class="col-sm-9 col-md-10">
        <input type="text" class="form-control" id="desc" name="desc" value="{{ old('desc', $service_template->desc) }}">
        <span class="help-block">{{ $errors->first('desc') }}</span>
    </div>
</div>

<div class="form-group @if($errors->has('ip')) has-error @endif">
    <label for="ip" class="control-label col-sm-3 col-md-2 text-nowrap">@lang('Remote Host')</label>
    <div class="col-sm-9 col-md-10">
        <input type="text" class="form-control" id="ip" name="ip" value="{{ old('ip', $service_template->ip) }}">
        <span class="help-block">{{ $errors->first('ip') }}</span>
    </div>
</div>

<div class="form-group @if($errors->has('param')) has-error @endif">
    <label for="param" class="control-label col-sm-3 col-md-2 text-nowrap">@lang('Parameters')</label>
    <div class="col-sm-9 col-md-10">
        <input type="text" class="form-control" id="param" name="param" value="{{ old('param', $service_template->param) }}">
        <span class="help-block">{{ $errors->first('param') }}</span>
    </div>
</div>

<div class="form-group row">
    <div class="col-sm-12 alert alert-info">
        <label class='control-label text-left input-sm'>Parameters may be required and will be different depending on the service check.</label>
    </div>
</div>

<div class="form-group @if($errors->has('ignore')) has-error @endif">
    <label for="ignore" class="control-label col-sm-3 col-md-2 text-nowrap">@lang('Ignore alert tag')</label>
    <div class="col-sm-9 col-md-10">
        <input type="checkbox" class="form-control" id="ignore" name="ignore" value="{{ old('ignore', $service_template->ignore) }}">
        <span class="help-block">{{ $errors->first('ignore') }}</span>
    </div>
</div>

<div class="form-group @if($errors->has('ip')) has-error @endif">
    <label for="ip" class="control-label col-sm-3 col-md-2 text-nowrap">@lang('Disable polling and alerting')</label>
    <div class="col-sm-9 col-md-10">
        <input type="checkbox" class="form-control" id="disabled" name="disabled" value="{{ old('disabled', $service_template->disabled) }}">
        <span class="help-block">{{ $errors->first('disabled') }}</span>
    </div>
</div>

<div id="dynamic-dg-form" class="form-group @if($errors->has('rules')) has-error @endif">
    <label for="pattern" class="control-label col-sm-3 col-md-2 text-nowrap">@lang('Define Rules')</label>
    <div class="col-sm-9 col-md-10">
        <div id="builder"></div>
        <span class="help-block">{{ $errors->first('rules') }}</span>
    </div>
</div>

<div id="static-dg-form" class="form-group @if($errors->has('devices')) has-error @endif" style="display: none">
    <label for="devices" class="control-label col-sm-3 col-md-2 text-nowrap">@lang('Select Devices')</label>
    <div class="col-sm-9 col-md-10">
        <select class="form-control" id="devices" name="devices[]" multiple>
            @foreach($service_template->devices as $device)
                <option value="{{ $device->device_id }}" selected>{{ $device->displayName() }}</option>
            @endforeach
        </select>
        <span class="help-block">{{ $errors->first('devices') }}</span>
    </div>
</div>

<script>
    function change_dg_type(select) {
        var type = select.options[select.selectedIndex].value;
        document.getElementById("dynamic-dg-form").style.display = (type === 'dynamic' ? 'block' : 'none');
        document.getElementById("static-dg-form").style.display = (type === 'dynamic' ? 'none' : 'block');
    }

    change_dg_type(document.getElementById('type'));

    init_select2('#devices', 'device', {multiple: true});

    var builder = $('#builder').on('afterApplyRuleFlags.queryBuilder afterCreateRuleFilters.queryBuilder', function () {
        $("[name$='_filter']").each(function () {
            $(this).select2({
                dropdownAutoWidth: true,
                width: 'auto'
            });
        });
    }).on('ruleToSQL.queryBuilder.filter', function (e, rule) {
        if (rule.operator === 'regexp') {
            e.value += ' \'' + rule.value + '\'';
        }
    }).queryBuilder({
        plugins: [
            'bt-tooltip-errors'
            // 'not-group'
        ],

        filters: {!! $filters !!},
        operators: [
            'equal', 'not_equal', 'between', 'not_between', 'begins_with', 'not_begins_with', 'contains', 'not_contains', 'ends_with', 'not_ends_with', 'is_empty', 'is_not_empty', 'is_null', 'is_not_null', 'in', 'not_in',
            {type: 'less', nb_inputs: 1, multiple: false, apply_to: ['string', 'number', 'datetime']},
            {type: 'less_or_equal', nb_inputs: 1, multiple: false, apply_to: ['string', 'number', 'datetime']},
            {type: 'greater', nb_inputs: 1, multiple: false, apply_to: ['string', 'number', 'datetime']},
            {type: 'greater_or_equal', nb_inputs: 1, multiple: false, apply_to: ['string', 'number', 'datetime']},
            {type: 'regex', nb_inputs: 1, multiple: false, apply_to: ['string', 'number']},
            {type: 'not_regex', nb_inputs: 1, multiple: false, apply_to: ['string', 'number']}
        ],
        lang: {
            operators: {
                regexp: 'regex',
                not_regex: 'not regex'
            }
        },
        sqlOperators: {
            regexp: {op: 'REGEXP'},
            not_regexp: {op: 'NOT REGEXP'}
        },
        sqlRuleOperator: {
            'REGEXP': function (v) {
                return {val: v, op: 'regexp'};
            },
            'NOT REGEXP': function (v) {
                return {val: v, op: 'not_regexp'};
            }
        }
    });

    $('.device-group-form').submit(function (eventObj) {
        if ($('#type').val() === 'static') {
            return true;
        }

        if (!builder.queryBuilder('validate')) {
            return false;
        }

        $('<input type="hidden" name="rules" />')
            .attr('value', JSON.stringify(builder.queryBuilder('getRules')))
            .appendTo(this);
        return true;
    });
</script>
<script>
    var rules = {!! json_encode(old('rules') ? json_decode(old('rules')) : $service_template->rules) !!};
    if (rules) {
        builder.queryBuilder('setRules', rules);
    }
</script>
