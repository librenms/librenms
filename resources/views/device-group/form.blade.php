<div class="form-group @if($errors->has('name')) has-error @endif">
    <label for="name" class="control-label col-sm-3 col-md-2 text-nowrap">@lang('Name')</label>
    <div class="col-sm-9 col-md-10">
        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $device_group->name) }}">
        <span class="help-block">{{ $errors->first('name') }}</span>
    </div>
</div>

<div class="form-group @if($errors->has('desc')) has-error @endif">
    <label for="desc" class="control-label col-sm-3 col-md-2 text-nowrap">@lang('Description')</label>
    <div class="col-sm-9 col-md-10">
        <input type="text" class="form-control" id="desc" name="desc" value="{{ old('desc', $device_group->desc) }}">
        <span class="help-block">{{ $errors->first('desc') }}</span>
    </div>
</div>

<div class="form-group @if($errors->has('type')) has-error @endif">
    <label for="type" class="control-label col-sm-3 col-md-2">@lang('Type')</label>
    <div class="col-sm-9 col-md-10">
        <select class="form-control" id="type" name="type" onchange="change_dg_type(this)">
            <option value="dynamic"
                    @if(old('type', $device_group->type) == 'dynamic') selected @endif>@lang('Dynamic')</option>
            <option value="static"
                    @if(old('type', $device_group->type) == 'static') selected @endif>@lang('Static')</option>
        </select>
        <span class="help-block">{{ $errors->first('type') }}</span>
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
            @foreach($device_group->devices as $device)
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

    $('.device-group-form').on("submit", function (eventObj) {
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
    var rules = {!! json_encode(old('rules') ? json_decode(old('rules')) : $device_group->rules) !!};
    if (rules) {
        builder.queryBuilder('setRules', rules);
    }
</script>
