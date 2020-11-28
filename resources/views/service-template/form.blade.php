<div class="form-group @if($errors->has('name')) has-error @endif">
    <label for="name" class="control-label col-sm-3 col-md-2 text-nowrap">@lang('Name')</label>
    <div class="col-sm-9 col-md-10">
        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $template->name) }}">
        <span class="help-block">{{ $errors->first('name') }}</span>
    </div>
</div>

<div class="form-group @if($errors->has('dtype')) has-error @endif">
    <label for="dtype" class="control-label col-sm-3 col-md-2">@lang('Device Type')</label>
    <div class="col-sm-9 col-md-10">
        <select class="form-control" id="dtype" name="dtype" onchange="change_st_dtype(this)">
            <option value="static"
                    @if(old('dtype', $template->dtype) == 'static') selected @endif>@lang('Static')</option>
            <option value="dynamic"
                    @if(old('dtype', $template->dtype) == 'dynamic') selected @endif>@lang('Dynamic')</option>
        </select>
        <span class="help-block">{{ $errors->first('dtype') }}</span>
    </div>
</div>

<div id="dynamic-st-d-form" class="form-group @if($errors->has('drules')) has-error @endif">
    <label for="drules" class="control-label col-sm-3 col-md-2 text-wrap">@lang('Define Device Rules')</label>
    <div class="col-sm-9 col-md-10">
        <div id="builder"></div>
        <span class="help-block">{{ $errors->first('drules') }}</span>
    </div>
</div>

<div id="static-st-d-form" class="form-group @if($errors->has('devices')) has-error @endif" style="display: none">
    <label for="devices" class="control-label col-sm-3 col-md-2 text-nowrap">@lang('Select Devices')</label>
    <div class="col-sm-9 col-md-10">
        <select class="form-control" id="devices" name="devices[]" multiple>
            @foreach($template->devices as $device)
                <option value="{{ $device->device_id }}" selected>{{ $device->displayName() }}</option>
            @endforeach
        </select>
        <span class="help-block">{{ $errors->first('devices') }}</span>
    </div>
</div>

<div class="form-group @if($errors->has('dgtype')) has-error @endif">
    <label for="dgtype" class="control-label col-sm-3 col-md-2">@lang('Device Group Type')</label>
    <div class="col-sm-9 col-md-10">
        <select class="form-control" id="dgtype" name="dgtype" onchange="change_st_dgtype(this)">
            <option value="static"
                    @if(old('dgtype', $template->dgtype) == 'static') selected @endif>@lang('Static')</option>
            <option value="dynamic"
                    @if(old('dgtype', $template->dgtype) == 'dynamic') selected @endif>@lang('Dynamic')</option>
        </select>
        <span class="help-block">{{ $errors->first('dgtype') }}</span>
    </div>
</div>

<div id="dynamic-st-dg-form" class="form-group @if($errors->has('dgrules')) has-error @endif">
    <label for="dgrules" class="control-label col-sm-3 col-md-2 text-wrap">@lang('Device Group Rules')</label>
    <div class="col-sm-9 col-md-10">
        <div id="builder2"></div>
        <span class="help-block">{{ $errors->first('dgrules') }}</span>
    </div>
</div>

<div id="static-st-dg-form" class="form-group @if($errors->has('groups')) has-error @endif" style="display: none">
    <label for="groups" class="control-label col-sm-3 col-md-2 text-wrap">@lang('Device Groups')</label>
    <div class="col-sm-9 col-md-10">
        <select class="form-control" id="groups" name="groups[]" multiple>
            @foreach($template->groups as $group)
                <option value="{{ $group->id }}" selected>{{ $group->name }}</option>
            @endforeach
        </select>
        <span class="help-block">{{ $errors->first('groups') }}</span>
    </div>
</div>

<div class="form-group @if($errors->has('type')) has-error @endif">
    <label for="type" class="control-label col-sm-3 col-md-2">@lang('Check Type')</label>
    <div class="col-sm-9 col-md-10">
        <select class="form-control" id="type" name="type">
            @foreach($services as $current_service)
                <option value="{{ $current_service }}" @if($current_service == $template->type) selected @endif>{{ $current_service }}</option>
            @endforeach
        </select>
        <span class="help-block">{{ $errors->first('type') }}</span>
    </div>
</div>

<div class="form-group @if($errors->has('desc')) has-error @endif">
    <label for="desc" class="control-label col-sm-3 col-md-2 text-nowrap">@lang('Description')</label>
    <div class="col-sm-9 col-md-10">
        <input type="text" class="form-control" id="desc" name="desc" value="{{ old('desc', $template->desc) }}">
        <span class="help-block">{{ $errors->first('desc') }}</span>
    </div>
</div>

<div class="form-group @if($errors->has('ip')) has-error @endif">
    <label for="ip" class="control-label col-sm-3 col-md-2 text-nowrap">@lang('Remote Host')</label>
    <div class="col-sm-9 col-md-10">
        <input type="text" class="form-control" id="ip" name="ip" value="{{ old('ip', $template->ip) }}">
        <span class="help-block">{{ $errors->first('ip') }}</span>
    </div>
</div>

<div class="form-group @if($errors->has('param')) has-error @endif">
    <label for="param" class="control-label col-sm-3 col-md-2 text-nowrap">@lang('Parameters')</label>
    <div class="col-sm-9 col-md-10">
        <input type="text" class="form-control" id="param" name="param" value="{{ old('param', $template->param) }}">
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
        <input type="hidden" value="0" name="ignore">
        <input type="checkbox" class="form-control" id="ignore" name="ignore" data-size="small" value="{{ old('ignore', $template->ignore) }}"@if(old('ignore', $template->ignore) == 1) checked @endif>
        <span class="help-block">{{ $errors->first('ignore') }}</span>
    </div>
</div>

<div class="form-group @if($errors->has('disabled')) has-error @endif">
    <label for="disabled" class="control-label col-sm-3 col-md-2 text-wrap">@lang('Disable polling and alerting')</label>
    <div class="col-sm-9 col-md-10">
        <input type="hidden" value="0" name="disabled">
        <input type="checkbox" class="form-control" id="disabled" name="disabled" data-size="small" value="{{ old('disabled', $template->disabled) }}"@if(old('disabled', $template->disabled) == 1) checked @endif>
        <span class="help-block">{{ $errors->first('disabled') }}</span>
    </div>
</div>

<script>
    $("[type='checkbox']").bootstrapSwitch('offColor','danger');
    $("#ignore").on( 'switchChange.bootstrapSwitch', function (e, state) {
        var value = $(this).is(':checked') ? "1": "0";
        $('#ignore').val(value);
    });
    $("#disabled").on( 'switchChange.bootstrapSwitch', function (e, state) {
        var value = $(this).is(':checked') ? "1": "0";
        $('#disabled').val(value);
    });
    function change_st_dtype(select) {
        var dtype = select.options[select.selectedIndex].value;
        document.getElementById("dynamic-st-d-form").style.display = (dtype === 'dynamic' ? 'block' : 'none');
        document.getElementById("static-st-d-form").style.display = (dtype === 'dynamic' ? 'none' : 'block');
    }

    change_st_dtype(document.getElementById('dtype'));

    function change_st_dgtype(select) {
        var dgtype = select.options[select.selectedIndex].value;
        document.getElementById("dynamic-st-dg-form").style.display = (dgtype === 'dynamic' ? 'block' : 'none');
        document.getElementById("static-st-dg-form").style.display = (dgtype === 'dynamic' ? 'none' : 'block');
    }

    change_st_dgtype(document.getElementById('dgtype'));

    init_select2('#devices', 'device', {multiple: true});
    init_select2('#groups', 'device-group', {multiple: true});
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
    var builder2 = $('#builder2').on('afterApplyRuleFlags.queryBuilder afterCreateRuleFilters.queryBuilder', function () {
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
    $('.service-template-form').submit(function (eventObj) {
        if ($('#dtype').val() === 'static') {
            return true;
        } else {
            $('<input type="hidden" name="drules" />')
                .attr('value', JSON.stringify(builder.queryBuilder('getRules')))
                .appendTo(this);
            if (!builder.queryBuilder('validate')) {
                return false;
            }
        }
        if ($('#dgtype').val() === 'static') {
            return true;
        } else {
            $('<input type="hidden" name="dgrules" />')
                .attr('value', JSON.stringify(builder2.queryBuilder('getRules')))
                .appendTo(this);
            if (!builder2.queryBuilder('validate')) {
                return false;
            }
        }
        return true;
    });
</script>
<script>
    var drules = {!! json_encode(old('drules') ? json_decode(old('drules')) : $template->drules) !!};
    if (drules) {
        builder.queryBuilder('setRules', drules);
    }
    var dgrules = {!! json_encode(old('dgrules') ? json_decode(old('dgrules')) : $template->dgrules) !!};
    if (dgrules) {
        builder2.queryBuilder('setRules', dgrules);
    }
</script>
