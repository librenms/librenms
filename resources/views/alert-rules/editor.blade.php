@php
    $defaults = [
        'severity' => \App\Facades\LibrenmsConfig::get('alert_rule.severity'),
        'invert' => (bool) \App\Facades\LibrenmsConfig::get('alert_rule.invert_rule_match'),
        'recovery' => (bool) \App\Facades\LibrenmsConfig::get('alert_rule.recovery_alerts'),
        'acknowledgement' => (bool) \App\Facades\LibrenmsConfig::get('alert_rule.acknowledgement_alerts'),
        'invert_map' => (bool) \App\Facades\LibrenmsConfig::get('alert_rule.invert_map'),
        'max_entities' => max(1, (int) \App\Facades\LibrenmsConfig::get('alert_rule.default_max_entities', 10)),
    ];
@endphp
<div class="modal fade" id="create-alert" tabindex="-1" role="dialog"
     aria-labelledby="Create" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h5 class="modal-title" id="Create">Alert Rule :: <a target="_blank" href="https://docs.librenms.org/Alerting/"><i class="fa fa-book fa-1x"></i> Docs</a> </h5>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active"><a href="#main" aria-controls="main" role="tab" data-toggle="tab">Main </a></li>
                    <li role="presentation"><a href="#advanced" aria-controls="advanced" role="tab" data-toggle="tab">Advanced</a></li>
                </ul>
                <br />
                <form method="post" role="form" id="rules" class="form-horizontal alerts-form">
                    <input type="hidden" name="rule_id" id="rule_id" value="">
                    <input type="hidden" name="builder_json" id="builder_json" value="">
                    <div id="alert-rule-form-error" class="alert alert-danger" style="display: none; margin-top: 10px;"></div>
                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane active" id="main">
                            <div class='form-group' title="The description of this alert rule.">
                                <label for='rule_name' class='col-sm-3 col-md-2 control-label'>Rule name </label>
                                <div class='col-sm-9 col-md-10'>
                                    <input type='text' id='rule_name' name='name' class='form-control validation' maxlength='200' required>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-3 col-md-2">
                                    <div class="dropdown">
                                        <button class="btn btn-default dropdown-toggle" type="button"
                                                id="import-from" data-toggle="dropdown" aria-haspopup="true"
                                                aria-expanded="true">
                                            Import from
                                            <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="import-from" id="import-dropdown">
                                            <li><a href="#" name="import-query" id="import-query">SQL Query</a></li>
                                            <li><a href="#" name="import-old-format" id="import-old-format">Old Format</a></li>
                                            <li><a href="#" name="import-collection" id="import-collection">Collection</a></li>
                                            <li><a href="#" name="import-alert_rule" id="import-alert_rule">Alert Rule</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-sm-9 col-md-10">
                                    <div id="builder"></div>
                                </div>
                            </div>
                            <div class="form-group" title="How to display the alert.  OK: green, Warning: yellow, Critical: red">
                                <label for='severity' class='col-sm-3 col-md-2 control-label'>Severity </label>
                                <div class="col-sm-2">
                                    <select name='severity' id='severity' class='form-control'>
                                        <option value='ok'>OK</option>
                                        <option value='warning'>Warning</option>
                                        <option value='critical' selected>Critical</option>
                                    </select>
                                </div>
                            </div>
                            <div class='form-group form-inline'>
                                <label for='invert' class='col-sm-3 col-md-2 control-label' title="Alert when this rule doesn't match.">Invert rule match </label>
                                <div class='col-sm-2' title="Alert when this rule doesn't match.">
                                    <x-toggle name="invert" id="invert" />
                                </div>
                                <label for='mute' class='col-sm-3 col-md-3 control-label' title="Suppress all notifications for this rule (alerts still appear in the Web UI)." style="vertical-align: top;">Mute alerts </label>
                                <div class='col-sm-2' title="Suppress all notifications for this rule (alerts still appear in the Web UI).">
                                    <x-toggle name="mute" id="mute" />
                                </div>
                            </div>
                            <div class="form-group form-inline">
                                <label for='recovery' class='col-sm-3 col-md-2 control-label' title="Issue recovery alerts.">Recovery alerts </label>
                                <div class='col-sm-2' title="Issue recovery alerts.">
                                    <x-toggle name="recovery" id="recovery" />
                                </div>
                                <label for='acknowledgement' class='col-sm-3 col-md-3 control-label' title="Issue acknowledgement alerts." style="vertical-align: top;">Acknowledgement alerts </label>
                                <div class='col-sm-2' title="Issue acknowledgement alerts.">
                                    <x-toggle name="acknowledgement" id="acknowledgement" />
                                </div>
                            </div>
                            <div class="form-group form-inline">
                                <label for='notify_per_entity' class='col-sm-3 col-md-2 control-label' title="Send a separate notification per matching entity (e.g. each port) instead of one grouped notification.">Notify per matching entity </label>
                                <div class='col-sm-2' title="Send a separate notification per matching entity (e.g. each port) instead of one grouped notification.">
                                    <x-toggle name="notify_per_entity" id="notify_per_entity" />
                                </div>
                                <label for='max_entities' class='col-sm-3 col-md-3 control-label' title="When notifying per entity, show and notify separately up to this many matching entities per device; above the limit, faults are grouped into one alert.">Max entities per device </label>
                                <div class='col-sm-2' title="When notifying per entity, show and notify separately up to this many matching entities per device; above the limit, faults are grouped into one alert.">
                                    <input type='number' name='max_entities' id='max_entities' class='form-control' min='1' value='{{ $defaults['max_entities'] }}'>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="alert_operation_id" class="col-sm-3 col-md-2 control-label" title="Notification behaviour (escalation, transports). Configure under Alerts → Operations.">Operation </label>
                                <div class="col-sm-9 col-md-10">
                                    <input type="hidden" name="alert_operation_id" id="alert_operation_id_input" value="">
                                    <select id="alert_operation_id" class="form-control" style="width: 100%;" data-placeholder="{{ __('None (suppress notifications)') }}">
                                    </select>
                                    <p class="help-block">{{ __('Leave empty to suppress all notifications for this rule.') }}</p>
                                </div>
                            </div>
                            <div class="form-group form-inline">
                                <label for='maps' class='col-sm-3 col-md-2 control-label' title="Restricts this alert rule to the selected devices, groups and locations.">Match devices, groups and locations list </label>
                                <div class="col-sm-7" style="width: 56%;">
                                    <select id="maps" name="maps[]" class="form-control" multiple="multiple"></select>
                                </div>
                                <div>
                                    <label for='invert_map' class='col-md-1' style="width: 14.1333%;" text-align="left" title="If ON, alert rule check will run on all devices except the selected devices and groups.">All devices except in list </label>
                                    <x-toggle name="invert_map" id="invert_map" />
                                </div>
                            </div>
                            <div class='form-group' title="A link to some documentation on how to handle this alert. This will be included in notifications.">
                                <label for='proc' class='col-sm-3 col-md-2 control-label'>Procedure URL </label>
                                <div class='col-sm-9 col-md-10'>
                                    <input type='text' id='proc' name='proc' class='form-control validation' pattern='(http|https)://.*' maxlength='80'>
                                </div>
                            </div>
                            <div class='form-group' title="A brief description for this alert rule">
                                <label for='notes' class='col-sm-3 col-md-2 control-label'>Notes</label>
                                <div class='col-sm-9 col-md-10'>
                                    <textarea class="form-control" rows="6" name="notes" id='notes'></textarea>
                                </div>
                            </div>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="advanced">
                            <div class="form-group">
                                <label for="override_query" class="col-sm-3 col-md-2 control-label">Override SQL</label>
                                <div class="col-sm-9 col-md-10">
                                    <x-toggle name="override_query" id="override_query" />
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="adv_query" class="col-sm-3 col-md-2 control-label">Query</label>
                                <div class="col-sm-9 col-md-10">
                                    <textarea class="form-control code" rows="6" name="adv_query" id='adv_query' style="font-family: Menlo, Monaco, Consolas, 'Courier New', monospace;"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-12 text-center">
                            <button type="button" class="btn btn-success" id="btn-save" name="save-alert">
                                Save Rule
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/sql-parser.min.js') }}"></script>
<script src="{{ asset('js/query-builder.standalone.min.js') }}"></script>
<script src="{{ asset('js/interact.min.js') }}"></script>
<script>
    const alertRuleDefaults = @js($defaults);
    const alertRuleDevice = @js($device ? ['id' => $device->device_id, 'text' => $device->displayName()] : null);

    $('#builder').on('afterApplyRuleFlags.queryBuilder afterCreateRuleFilters.queryBuilder', function () {
        $("[name$='_filter']").each(function () {
            $(this).select2({
                dropdownParent: $("#create-alert"),
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
            'bt-tooltip-errors',
            'sortable'
        ],
        filters: @js(new \LibreNMS\Alerting\QueryBuilderFilter('alert')),
        operators: [
            'equal', 'not_equal', 'between', 'not_between', 'begins_with', 'not_begins_with', 'contains', 'not_contains', 'ends_with', 'not_ends_with', 'is_empty', 'is_not_empty', 'is_null', 'is_not_null',
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
            'REGEXP': (v) => ({val: v, op: 'regexp'}),
            'NOT REGEXP': (v) => ({val: v, op: 'not_regexp'})
        }
    });

    $('#btn-save').on('click', function (e) {
        e.preventDefault();
        $('#alert-rule-form-error').hide().text('');

        const builder = $('#builder').queryBuilder('getRules');
        if (builder === null || ! builder.valid) {
            return;
        }

        $('#alert_operation_id_input').val($('#alert_operation_id').val() ?? '');
        $('#builder_json').val(JSON.stringify(builder));

        const ruleId = $('#rule_id').val();
        axios({
            method: ruleId ? 'put' : 'post',
            url: ruleId ? route('alert-rule.update', ruleId) : route('alert-rule.store'),
            data: $('form.alerts-form').serialize(),
        }).then((response) => {
            if (response.data.status === 'ok') {
                toastr.success(response.data.message);
                window.location.reload();
            } else {
                toastr.error(response.data.message);
            }
        }).catch((error) => {
            const errors = error.response?.data?.errors ?? {};
            $('#alert-rule-form-error').text(errors.alert_operation_id?.[0] ?? error.response?.data?.message ?? @js(__('Request failed'))).show();
        });
    });

    function importSql(sql) {
        if (sql) {
            try {
                $('#builder').queryBuilder('setRulesFromSQL', sql);
            } catch (e) {
                alert(@js(__('Your query could not be parsed')));
            }
        }
    }

    $('#import-query').on('click', function (e) {
        e.preventDefault();
        importSql(window.prompt(@js(__('Enter your SQL query:'))));
    });

    $('#import-old-format').on('click', function (e) {
        e.preventDefault();
        const old = window.prompt(@js(__('Enter your old alert rule:')));
        importSql(old && old.replace(/&&/g, 'AND')
            .replace(/\|\|/g, 'OR')
            .replace(/%/g, '')
            .replace(/"/g, "'")
            .replace(/~/g, 'REGEXP')
            .replace(/@/g, '.*'));
    });

    $('#import-collection').on('click', function (e) {
        e.preventDefault();
        $('#search_rule_modal').modal('show');
    });

    $('#import-alert_rule').on('click', function (e) {
        e.preventDefault();
        $('#search_alert_rule_modal').modal('show');
    });

    $('#create-alert').on('show.bs.modal', function (e) {
        $('#alert-rule-form-error').hide().text('');
        // opened from a picker after loadRule() filled it in
        if (! e.relatedTarget) {
            return;
        }

        const ruleId = $(e.relatedTarget).data('rule_id');
        $('#rule_id').val(ruleId ?? '');

        if (ruleId) {
            axios.get(route('alert-rule.show', ruleId)).then((response) => loadRule(response.data));
            return;
        }

        // new rule, reset the form
        $('#builder').queryBuilder('reset');
        $(this).find('input[type=text]').val('');
        $('#adv_query, #notes').val('');
        $('#severity').val(alertRuleDefaults.severity);
        $('#max_entities').val(alertRuleDefaults.max_entities);
        $('#invert').prop('checked', alertRuleDefaults.invert);
        $('#recovery').prop('checked', alertRuleDefaults.recovery);
        $('#acknowledgement').prop('checked', alertRuleDefaults.acknowledgement);
        $('#invert_map').prop('checked', alertRuleDefaults.invert_map);
        $('#mute, #override_query, #notify_per_entity').prop('checked', false);
        $('#alert_operation_id').empty().val(null).trigger('change');
        setMaps(null);
    });

    function loadRule(rule) {
        $('#rule_name').val(rule.name);
        $('#proc').val(rule.proc);
        $('#builder').queryBuilder('setRules', rule.builder);
        $('#severity').val(rule.severity).trigger('change');
        $('#adv_query').val(rule.adv_query);
        $('#notes').val(rule.notes);

        const $operation = $('#alert_operation_id').empty().val(null);
        if (rule.alert_operation_id) {
            $operation.append(new Option(rule.alert_operation_name || ('#' + rule.alert_operation_id), rule.alert_operation_id, true, true));
        }
        $operation.trigger('change');

        setMaps(rule.maps);

        const extra = rule.extra ?? {};
        if (extra.adv_query) {
            $('#adv_query').val(extra.adv_query);
        }
        $('#invert').prop('checked', Boolean(extra.invert));
        $('#recovery').prop('checked', extra.recovery ?? alertRuleDefaults.recovery);
        $('#acknowledgement').prop('checked', extra.acknowledgement ?? alertRuleDefaults.acknowledgement);
        $('#mute').prop('checked', Boolean(extra.mute));
        $('#override_query').prop('checked', Boolean(extra.options?.override_query));
        $('#invert_map').prop('checked', rule.invert_map == 1);
        $('#notify_per_entity').prop('checked', rule.notify_per_entity == 1);
        $('#max_entities').val(rule.max_entities ?? alertRuleDefaults.max_entities);
    }

    // rules from the collection have no maps, so they get the device of this page
    function setMaps(maps) {
        const $maps = $('#maps').empty();
        (maps ?? (alertRuleDevice ? [alertRuleDevice] : [])).forEach((map) => $maps.append(new Option(map.text, map.id, true, true)));
        $maps.trigger('change');
    }

    $('#maps').select2({
        width: '100%',
        placeholder: @js(__('Devices, Groups or Locations')),
        ajax: {
            url: @js(route('ajax.select.devices-groups-locations')),
            delay: 150
        }
    });

    $('#alert_operation_id').select2({
        width: '100%',
        placeholder: @js(__('None (suppress notifications)')),
        allowClear: true,
        ajax: {
            url: @js(route('ajax.select.alert-operation')),
            delay: 150
        },
        dropdownParent: $('#create-alert')
    });
</script>
@endpush
