@extends('layouts.librenmsv1')

@section('title', __('Create Alert Rule'))

@section('content')
<div class="row">
    <div class="col-sm-12 col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    {{ __('Create Alert Rule') }}
                    <a target="_blank" href="https://docs.librenms.org/Alerting/" class="pull-right">
                        <i class="fa fa-book"></i> Docs
                    </a>
                </h3>
            </div>
            <div class="panel-body">
                <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active"><a href="#main" aria-controls="main" role="tab" data-toggle="tab">{{ __('Main') }}&nbsp;</a></li>
                    <li role="presentation"><a href="#advanced" aria-controls="advanced" role="tab" data-toggle="tab">{{ __('Advanced') }}&nbsp;</a></li>
                </ul>
                <form method="post" role="form" id="rules" class="form-horizontal alerts-form">
                    @csrf
                    <input type="hidden" name="device_id" id="device_id" value="{{ $device_id }}">
                    <input type="hidden" name="device_name" id="device_name" value="{{ $deviceName }}">
                    <input type="hidden" name="rule_id" id="rule_id" value="">
                    <input type="hidden" name="type" id="type" value="alert-rules">
                    <input type="hidden" name="template_id" id="template_id" value="">
                    <input type="hidden" name="builder_json" id="builder_json" value="">

                    <div class="tab-content" style="margin-top: 15px;">
                        <div role="tabpanel" class="tab-pane active" id="main">
                            <div class='form-group' title="The description of this alert rule.">
                                <label for='rule_name' class='col-sm-3 col-md-2 control-label'>{{ __('Rule name') }}</label>
                                <div class='col-sm-9 col-md-10'>
                                    <input type='text' id='rule_name' name='name' class='form-control validation' maxlength='200' required>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-3 col-md-2">
                                    <div class="dropdown">
                                        <button class="btn btn-default dropdown-toggle" type="button" id="import-from" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                            {{ __('Import from') }}
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
                                <label for='severity' class='col-sm-3 col-md-2 control-label'>{{ __('Severity') }}</label>
                                <div class="col-sm-2">
                                    <select name='severity' id='severity' class='form-control'>
                                        <option value='ok' {{ $default_severity === 'ok' ? 'selected' : '' }}>OK</option>
                                        <option value='warning' {{ $default_severity === 'warning' ? 'selected' : '' }}>Warning</option>
                                        <option value='critical' {{ $default_severity === 'critical' ? 'selected' : '' }}>Critical</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group form-inline">
                                <label for='count' class='col-sm-3 col-md-2 control-label' title="How many notifications to issue while active before stopping. -1 means no limit. If interval is 0, this has no effect.">Max alerts </label>
                                <div class="col-sm-2" title="How many notifications to issue while active before stopping. -1 means no limit. If interval is 0, this has no effect.">
                                    <input type='text' id='count' name='count' class='form-control' size="4" value="{{ $default_max_alerts }}">
                                </div>
                                <div class="col-sm-3" title="How long to wait before issuing a notification. If the alert clears before the delay, no notification will be issued. (s,m,h,d)">
                                    <label for='delay' class='control-label' style="vertical-align: top;">Delay </label>
                                    <input type='text' id='delay' name='delay' class='form-control' size="4" value="{{ $default_delay }}">
                                </div>
                                <div class="col-sm-4 col-md-3" title="How often to re-issue notifications while this alert is active. 0 means notify once. This is affected by the poller interval. (s,m,h,d)">
                                    <label for='interval' class='control-label' style="vertical-align: top;">Interval </label>
                                    <input type='text' id='interval' name='interval' class='form-control' size="4" value="{{ $default_interval }}">
                                </div>
                            </div>
                            <div class='form-group form-inline'>
                                <label for='mute' class='col-sm-3 col-md-2 control-label' title="Show alert status in the webui, but do not issue notifications.">Mute alerts </label>
                                <div class='col-sm-2' title="Show alert status in the webui, but do not issue notifications.">
                                    <input type="checkbox" name="mute" id="mute">
                                </div>
                            </div>
                            <div class='form-group form-inline'>
                                <label for='recovery' class='col-sm-3 col-md-2 control-label' title="Send recovery notification when alert clears.">Recovery alerts </label>
                                <div class='col-sm-2' title="Send recovery notification when alert clears.">
                                    <input type="checkbox" name="recovery" id="recovery">
                                </div>
                            </div>
                            <div class='form-group form-inline'>
                                <label for='acknowledgement' class='col-sm-3 col-md-2 control-label' title="Send acknowledgement notification when alert is acknowledged.">Acknowledgement alerts </label>
                                <div class='col-sm-2' title="Send acknowledgement notification when alert is acknowledged.">
                                    <input type="checkbox" name="acknowledgement" id="acknowledgement">
                                </div>
                            </div>

                            <div class='form-group form-inline'>
                                <label for='invert' class='col-sm-3 col-md-2 control-label' title="Invert the match.  If the rule matches, the alert is considered OK.">Invert rule match </label>
                                <div class='col-sm-2' title="Invert the match.  If the rule matches, the alert is considered OK.">
                                    <input type="checkbox" name="invert" id="invert">
                                </div>
                            </div>

                            <div class='form-group form-inline'>
                                <label for='override_query' class='col-sm-3 col-md-2 control-label' title="Force the query to run even if the rule is disabled.">Override Query </label>
                                <div class='col-sm-2' title="Force the query to run even if the rule is disabled.">
                                    <input type="checkbox" name="override_query" id="override_query">
                                </div>
                            </div>

                            <div class='form-group form-inline'>
                                <label for='invert_map' class='col-sm-3 col-md-2 control-label' title="Invert the device/group/location mapping.">Invert map </label>
                                <div class='col-sm-2' title="Invert the device/group/location mapping.">
                                    <input type="checkbox" name="invert_map" id="invert_map">
                                </div>
                            </div>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="advanced">
                            <div class='form-group'>
                                <label for='rule' class='col-sm-3 col-md-2 control-label'>SQL</label>
                                <div class='col-sm-9 col-md-10'>
                                    <textarea id='rule' name='rule' class='form-control' rows="3"></textarea>
                                    <span class="help-block">{{ __('Optional: Provide a raw SQL WHERE clause to override the builder.') }}</span>
                                </div>
                            </div>
                            <div class='form-group'>
                                <label for='query_json' class='col-sm-3 col-md-2 control-label'>JSON</label>
                                <div class='col-sm-9 col-md-10'>
                                    <textarea id='query_json' name='query_json' class='form-control' rows="3"></textarea>
                                    <span class="help-block">{{ __('Optional: Provide a raw QueryBuilder JSON to override the builder.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-offset-3 col-sm-9 col-md-offset-2 col-md-10">
                            <button id="btn-save" type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                            <a href="{{ url('alert-rules') }}" class="btn btn-default">{{ __('Cancel') }}</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('javascript')
    <script src="{{ asset('js/sql-parser.min.js') }}"></script>
    <script src="{{ asset('js/query-builder.standalone.min.js') }}"></script>
    <script src="{{ asset('js/interact.min.js') }}"></script>
@endsection

{{-- Ported modals for importing rules (logic moved to controller) --}}
<div class="modal fade" id="search_rule_modal" tabindex="-1" role="dialog" aria-labelledby="search_rule" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h5 class="modal-title" id="search_rule">Alert rule collection</h5>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="rule_collection" class="table table-condensed table-hover">
                        <thead>
                        <tr>
                            <th data-column-id="name" data-width="200px">Name</th>
                            <th data-column-id="rule">Rule</th>
                            <td data-column-id="action" data-formatter="action"></td>
                        </tr>
                        </thead>
                        @foreach($collectionRules as $cRule)
                            <tr>
                                <td>{{ $cRule['name'] }}</td>
                                <td>{!! $cRule['sql'] !!}</td>
                                <td>{{ $cRule['id'] }}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="search_alert_rule_modal" tabindex="-1" role="dialog" aria-labelledby="search_alert_rule_list" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h5 class="modal-title" id="search_alert_rule_list">Running Alert rules</h5>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="alert_rule_list" class="table table-condensed table-hover">
                        <thead>
                        <tr>
                            <th data-column-id="alert_name" data-width="200px">Name</th>
                            <th data-column-id="alert_rule">Rule</th>
                            <th data-column-id="alert_severity">Severity</th>
                            <td data-column-id="alert_action" data-formatter="alert_action"></td>
                        </tr>
                        </thead>
                        @foreach($dbRules as $r)
                            <tr>
                                <td>{{ $r['name'] }}</td>
                                <td><i>{{ $r['display'] }}</i></td>
                                <td>{{ $r['severity'] }}</td>
                                <td>{{ $r['id'] }}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // helper to format seconds to s/m/h/d string
    function formatDuration(val) {
        if (val == null || isNaN(val)) { return ''; }
        if ((val / 86400) >= 1) { return (val / 86400) + 'd'; }
        if ((val / 3600) >= 1) { return (val / 3600) + 'h'; }
        if ((val / 60) >= 1) { return (val / 60) + 'm'; }
        return String(val);
    }

    // simplified loader compatible with ajax_form.php responses
    function loadRule(rule) {
        try {
            if (rule.name) { $('#rule_name').val(rule.name); }
            if (rule.builder) { $('#builder').queryBuilder('setRules', rule.builder); }
            if (rule.severity) { $('#severity').val(rule.severity).trigger('change'); }

            var extra = rule.extra || {};
            if (typeof extra.count !== 'undefined') { $('#count').val(extra.count); }
            if (typeof extra.delay !== 'undefined') { $('#delay').val(formatDuration(extra.delay)); }
            if (typeof extra.interval !== 'undefined') { $('#interval').val(formatDuration(extra.interval)); }
            if (extra.adv_query) { $('#rule').val(extra.adv_query); }
            if (rule.query_json) { $('#query_json').val(rule.query_json); }

            // Set switches
            if (typeof extra.mute !== 'undefined') { $("[name='mute']").bootstrapSwitch('state', !!extra.mute); }
            if (typeof extra.invert !== 'undefined') { $("[name='invert']").bootstrapSwitch('state', !!extra.invert); }

            var recovery = (typeof extra.recovery !== 'undefined') ? !!extra.recovery : {{ $default_recovery_alerts ? 'true' : 'false' }};
            var acknowledgement = (typeof extra.acknowledgement !== 'undefined') ? !!extra.acknowledgement : {{ $default_acknowledgement_alerts ? 'true' : 'false' }};
            var override = (extra.options && typeof extra.options.override_query !== 'undefined') ? !!extra.options.override_query : false;

            $("[name='recovery']").bootstrapSwitch('state', recovery);
            $("[name='acknowledgement']").bootstrapSwitch('state', acknowledgement);
            $("[name='invert_map']").bootstrapSwitch('state', (rule.invert_map == 1));
            $("[name='override_query']").bootstrapSwitch('state', override);
        } catch (e) {
            console.error('Failed to load rule into form', e);
        }
    }

    var grid = $("#rule_collection").bootgrid({
        caseSensitive: false,
        formatters: {
            "action": function (column, row) {
                return "<button type=\"button\" data-rule_id=\"" + row.action + "\" class=\"btn btn-sm btn-primary rule_from_collection\">Select<\/button>";
            }
        }
    }).on("loaded.rs.jquery.bootgrid", function () {
        grid.find(".rule_from_collection").on("click", function () {
            var template_rule_id = $(this).data("rule_id");
            $.ajax({
                type: "POST",
                url: "ajax_form.php",
                data: {type: 'sql-from-alert-collection', template_id: template_rule_id},
                dataType: "json",
                success: function (data) {
                    if (data.status == 'ok') {
                        $("#search_rule_modal").modal('hide');
                        loadRule(data);
                    } else {
                        toastr.error(data.message);
                    }
                },
                error: function () {
                    toastr.error('Failed to process template');
                }
            });
        }).end();
    });

    var alert_grid = $("#alert_rule_list").bootgrid({
        caseSensitive: false,
        formatters: {
            "alert_action": function (column, row) {
                return "<button type=\"button\" data-rule_id=\"" + row.alert_action + "\" class=\"btn btn-sm btn-primary alert_rule_from_list\">Select<\/button>";
            }
        },
        templates: { footer: "<div id=\"@{{ctx.id}}\" class=\"@{{css.footer}}\"><div class=\"row\"><div class=\"col-sm-12\"><p class=\"@{{css.pagination}}\"></p></div></div></div>" }
    }).on("loaded.rs.jquery.bootgrid", function() {
        alert_grid.find(".alert_rule_from_list").on("click", function() {
            var alert_rule_id = $(this).data("rule_id");
            $.ajax({
                type: "POST",
                url: "ajax_form.php",
                data: {type: 'sql-from-alert-rules', rule_id: alert_rule_id},
                dataType: "json",
                success: function (data) {
                    if (data.status == 'ok') {
                        $("#search_alert_rule_modal").modal('hide');
                        loadRule(data);
                    } else { toastr.error(data.message); }
                },
                error: function () { toastr.error('Failed to process template'); }
            });
        }).end();
    });

    $(function() {
        // Initialize switches
        $('#mute').bootstrapSwitch();
        $('#invert').bootstrapSwitch();
        $('#recovery').bootstrapSwitch();
        $('#acknowledgement').bootstrapSwitch();
        $('#override_query').bootstrapSwitch();
        $('#invert_map').bootstrapSwitch();

        // Initialize query builder
        var filters = {!! $filters !!};
        $('#builder').queryBuilder({
            plugins: ['bt-tooltip-errors'],
            allow_empty: true,
            filters: filters,
            operators: [
                {type: 'equal', nb_inputs: 1, multiple: false, apply_to: ['string', 'number', 'datetime']},
                {type: 'not_equal', nb_inputs: 1, multiple: false, apply_to: ['string', 'number', 'datetime']},
                {type: 'in', nb_inputs: 1, multiple: true, apply_to: ['string', 'number']},
                {type: 'not_in', nb_inputs: 1, multiple: true, apply_to: ['string', 'number']},
                {type: 'less', nb_inputs: 1, multiple: false, apply_to: ['string', 'number', 'datetime']},
                {type: 'less_or_equal', nb_inputs: 1, multiple: false, apply_to: ['string', 'number', 'datetime']},
                {type: 'greater', nb_inputs: 1, multiple: false, apply_to: ['string', 'number', 'datetime']},
                {type: 'greater_or_equal', nb_inputs: 1, multiple: false, apply_to: ['string', 'number', 'datetime']},
                {type: 'regex', nb_inputs: 1, multiple: false, apply_to: ['string', 'number']},
                {type: 'not_regex', nb_inputs: 1, multiple: false, apply_to: ['string', 'number']}
            ],
            lang: { operators: { regexp: 'regex', not_regex: 'not regex' } },
            sqlOperators: { regexp: {op: 'REGEXP'}, not_regexp: {op: 'NOT REGEXP'} },
            sqlRuleOperator: {
                'REGEXP': function (v) { return {val: v, op: 'regexp'}; },
                'NOT REGEXP': function (v) { return {val: v, op: 'not_regexp'}; }
            }
        });

        // default state for create page
        $('#rule_id').val('');
        var $severity = $('#severity');
        if (!$severity.find('option:selected').length) {
            $severity.val($severity.find("option[selected]").val());
        }
        $("#mute").bootstrapSwitch('state', {{ $default_mute_alerts ? 'true' : 'false' }});
        $("#invert").bootstrapSwitch('state', {{ $default_invert_rule_match ? 'true' : 'false' }});
        $("#recovery").bootstrapSwitch('state', {{ $default_recovery_alerts ? 'true' : 'false' }});
        $("#acknowledgement").bootstrapSwitch('state', {{ $default_acknowledgement_alerts ? 'true' : 'false' }});
        $("#override_query").bootstrapSwitch('state', false);
        $("#invert_map").bootstrapSwitch('state', {{ $default_invert_map ? 'true' : 'false' }});

        $('#btn-save').on('click', function (e) {
            e.preventDefault();
            var result_json = $('#builder').queryBuilder('getRules');
            if (result_json !== null && result_json.valid) {
                $('#builder_json').val(JSON.stringify(result_json));
                $.ajax({
                    type: 'POST',
                    url: 'ajax_form.php',
                    data: $('form.alerts-form').serializeArray(),
                    dataType: 'json',
                    success: function (data) {
                        if (data.status === 'ok') {
                            toastr.success(data.message);
                            window.location.href = '{{ url('alert-rules') }}';
                        } else {
                            toastr.error(data.message);
                        }
                    },
                    error: function () {
                        toastr.error('Failed to process rule');
                    }
                });
            } else {
                toastr.error('Invalid rule, please complete the required fields');
            }
        });

        $('#import-query').on('click', function (e) {
            e.preventDefault();
            var sql_import = window.prompt('Enter your SQL query:');
            if (sql_import) {
                try { $("#builder").queryBuilder("setRulesFromSQL", sql_import); }
                catch (e) { alert('Your query could not be parsed'); }
            }
        });

        $('#import-old-format').on('click', function (e) {
            e.preventDefault();
            var old_import = window.prompt('Enter your old alert rule:');
            if (old_import) {
                try {
                    old_import = old_import.replace(/&&/g, 'AND');
                    old_import = old_import.replace(/\|\|/g, 'OR');
                    old_import = old_import.replace(/%/g, '');
                    old_import = old_import.replace(/"/g, "'");
                    old_import = old_import.replace(/~/g, 'REGEXP');
                    old_import = old_import.replace(/@/g, '.*');
                    $("#builder").queryBuilder("setRulesFromSQL", old_import);
                } catch (e) { alert('Your query could not be parsed'); }
            }
        });

        $('#import-collection').on('click', function (e) {
            e.preventDefault();
            $("#search_rule_modal").modal('show');
        });

        $('#import-alert_rule').on('click', function (e) {
            e.preventDefault();
            $("#search_alert_rule_modal").modal('show');
        });

    });
</script>
@endpush
