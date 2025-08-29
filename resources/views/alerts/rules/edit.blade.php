@extends('layouts.librenmsv1')

@section('title', __('Edit Alert Rule'))

@section('content')
<div class="row">
    <div class="col-sm-12 col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    {{ __('Edit Alert Rule') }}
                    <a target="_blank" href="https://docs.librenms.org/Alerting/" class="pull-right">
                        <i class="fa fa-book"></i> {{ __('Documentation') }}
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
                    <input type="hidden" name="rule_id" id="rule_id" value="{{ $alertRule->id }}">
                    <input type="hidden" name="type" id="type" value="alert-rules">
                    <input type="hidden" name="builder_json" id="builder_json" value="">

                    @include('alerts.rules._form')
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- Modals reused from create page --}}
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

@section('javascript')
    <script src="{{ asset('js/sql-parser.min.js') }}"></script>
    <script src="{{ asset('js/query-builder.standalone.min.js') }}"></script>
    <script src="{{ asset('js/interact.min.js') }}"></script>
@endsection

@push('scripts')
@include('alerts.rules._form_js', [
    'mode' => 'edit',
    'saveUrl' => route('alert-rule.update', $alertRule),
    'saveMethod' => 'PUT',
    'loadUrl' => route('alert-rule.show', $alertRule),
])
@endpush

@push('scripts-legacy')
<script>
    function formatDuration(val) {
        if (val == null || isNaN(val)) { return ''; }
        if ((val / 86400) >= 1) { return (val / 86400) + 'd'; }
        if ((val / 3600) >= 1) { return (val / 3600) + 'h'; }
        if ((val / 60) >= 1) { return (val / 60) + 'm'; }
        return String(val);
    }

    function loadRule(rule) {
        try {
            if (rule.name) { $('#rule_name').val(rule.name); }
            if (rule.builder) { $('#builder').queryBuilder('setRules', rule.builder); }
            if (rule.severity) { $('#severity').val(rule.severity).trigger('change'); }

            var extra = rule.extra || {};
            if (typeof extra.count !== 'undefined') { $('#count').val(extra.count); }
            if (typeof extra.delay !== 'undefined') { $('#delay').val(formatDuration(extra.delay)); }
            if (typeof extra.interval !== 'undefined') { $('#interval').val(formatDuration(extra.interval)); }
            if (rule.adv_query) { $('#adv_query').val(rule.adv_query); }

            if (typeof rule.proc !== 'undefined') { $('#proc').val(rule.proc); }
            if (typeof rule.notes !== 'undefined') { $('#notes').val(rule.notes); }

            var $maps = $('#maps');
            $maps.empty();
            $maps.val(null).trigger('change');
            if (rule.maps != null) {
                $.each(rule.maps, function(index, value) {
                    var option = new Option(value.text, value.id, true, true);
                    $maps.append(option).trigger('change');
                });
            }

            var $transports = $('#transports');
            $transports.empty();
            $transports.val(null).trigger('change');
            if (rule.transports != null) {
                $.each(rule.transports, function(index, value) {
                    var option = new Option(value.text, value.id, true, true);
                    $transports.append(option).trigger('change');
                });
            }

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

    $(function() {
        // Initialize switches
        $('#mute').bootstrapSwitch();
        $('#invert').bootstrapSwitch();
        $('#recovery').bootstrapSwitch();
        $('#acknowledgement').bootstrapSwitch();
        $('#override_query').bootstrapSwitch();
        $('#invert_map').bootstrapSwitch();

        // Initialize select2 for maps and transports
        $('#maps').select2({
            width: '100%',
            placeholder: 'Devices, Groups or Locations',
            ajax: {
                url: 'ajax_list.php',
                delay: 250,
                data: function (params) {
                    return { type: 'devices_groups_locations', search: params.term };
                },
                processResults: function (data) {
                    return { results: data.items || [] };
                }
            }
        });
        $('#transports').select2({
            width: '100%',
            placeholder: 'Transport/Group Name',
            ajax: {
                url: 'ajax_list.php',
                delay: 250,
                data: function (params) {
                    return { type: 'transport_groups', search: params.term };
                }
            }
        });

        // Templates JS data
        var templateOptions = @json($templates->toArray());
        function renderPerTransportTemplates() {
            var $list = $('#transport-template-list');
            $list.empty();
            var data = ($('#transports').data('select2') ? $('#transports').select2('data') : []) || [];
            if (!data.length) { return; }

            var added = {}; // transport_id => true to avoid duplicates
            var rows = [];

            function addRow(id, text) {
                if (!id || added[id]) { return; }
                added[id] = true;
                var labelText = text || ('Transport #' + id);
                var row = $('<div class="form-inline" style="margin-bottom:6px;"></div>');
                var label = $('<label class="control-label" style="min-width:220px; margin-right:8px;"></label>').text(labelText);
                var select = $('<select class="form-control" style="min-width:260px;"></select>')
                    .attr('name', 'template_transports[' + id + ']');
                select.append($('<option value=""></option>').text('— ' + 'Use global or default' + ' —'));
                templateOptions.forEach(function(opt){
                    select.append($('<option></option>').attr('value', opt.id).text(opt.name));
                });
                row.append(label).append(select);
                rows.push(row);
            }

            var ajaxCalls = [];
            data.forEach(function(item) {
                var key = item.id;
                if (String(key).startsWith('g')) {
                    var groupId = String(key).substring(1);
                    var call = $.getJSON('{{ route('alert.transport-groups.members', ':group_id') }}'.replace(':group_id', groupId))
                        .done(function(resp){
                            if (resp && resp.members && resp.members.length) {
                                resp.members.forEach(function(member){
                                    addRow(member.id, member.text);
                                });
                            }
                        }).fail(function(){ /* ignore errors */ });
                    ajaxCalls.push(call);
                } else {
                    addRow(key, item.text);
                }
            });

            $.when.apply($, ajaxCalls).always(function(){
                rows.forEach(function(r){ $list.append(r); });
            });
        }
        $('#transports').on('change', renderPerTransportTemplates);

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

        // Load existing rule data
        $.getJSON("{{ route('alert-rule.show', $alertRule) }}")
            .done(function (data) { loadRule(data); })
            .fail(function () { toastr.error('Failed to load rule'); });

        // Save changes
        $('#btn-save').on('click', function (e) {
            e.preventDefault();
            var result_json = $('#builder').queryBuilder('getRules');
            if (result_json !== null && result_json.valid) {
                $('#builder_json').val(JSON.stringify(result_json));
                $.ajax({
                    type: 'PUT',
                    url: '{{ route('alert-rule.update', $alertRule) }}',
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
                    error: function () { toastr.error('Failed to process rule'); }
                });
            } else {
                toastr.error('Invalid rule, please complete the required fields');
            }
        });

        // Import helpers
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

        // Lazy init of collection and alert rule lists on demand
        var collectionInit = false;
        $('#import-collection').on('click', function (e) {
            e.preventDefault();
            if (!collectionInit) {
                $('#rule_collection').bootgrid({
                    ajax: true,
                    url: '{{ route('alert-rule-template') }}', // returns collection list
                    responseHandler: function (res) {
                        // Expecting { rows: [...], total: n }
                        var rows = (res || {}).rows || [];
                        return { rows: rows.map(function(r){ return { name: r.name, rule: r.sql, action: r.id }; }), total: rows.length };
                    },
                    formatters: { "action": function (column, row) { return "<button type=\"button\" data-rule_id=\"" + row.action + "\" class=\"btn btn-sm btn-primary rule_from_collection\">Select<\/button>"; } }
                }).on("loaded.rs.jquery.bootgrid", function () {
                    collectionInit = true;
                    $(this).find(".rule_from_collection").on("click", function () {
                        var template_rule_id = $(this).data("rule_id");
                        $.getJSON('{{ route('alert-rule-template', ':template_id') }}'.replace(':template_id', template_rule_id))
                            .done(function (data) {
                                if (data.status === 'ok') { $("#search_rule_modal").modal('hide'); loadRule(data); }
                                else { toastr.error(data.message || 'Failed to load template'); }
                            })
                            .fail(function () { toastr.error('Failed to process template'); });
                    });
                });
            }
            $("#search_rule_modal").modal('show');
        });

        var alertListInit = false;
        $('#import-alert_rule').on('click', function (e) {
            e.preventDefault();
            if (!alertListInit) {
                $('#alert_rule_list').bootgrid({
                    ajax: true,
                    url: '{{ url('alert-rules') }}', // legacy list page adapter (handled by LegacyController)
                    formatters: { "alert_action": function (column, row) { return "<button type=\"button\" data-rule_id=\"" + row.alert_action + "\" class=\"btn btn-sm btn-primary alert_rule_from_list\">Select<\/button>"; } },
                    templates: { footer: "<div id=\"@{{ctx.id}}\" class=\"@{{css.footer}}\"><div class=\"row\"><div class=\"col-sm-12\"><p class=\"@{{css.pagination}}\"></p></div></div></div>" }
                }).on("loaded.rs.jquery.bootgrid", function() {
                    alertListInit = true;
                    $(this).find(".alert_rule_from_list").on("click", function() {
                        var alert_rule_id = $(this).data("rule_id");
                        $.getJSON('{{ route('alert-rule-template.rule', ':rule_id') }}'.replace(':rule_id', alert_rule_id))
                            .done(function (data) {
                                if (data.status === 'ok') { $("#search_alert_rule_modal").modal('hide'); loadRule(data); }
                                else { toastr.error(data.message || 'Failed to load rule'); }
                            })
                            .fail(function () { toastr.error('Failed to process template'); });
                    });
                });
            }
            $("#search_alert_rule_modal").modal('show');
        });
    });
</script>
@endpush
