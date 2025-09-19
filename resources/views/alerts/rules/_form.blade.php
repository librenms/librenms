<div class="panel panel-default" x-data="alertRuleForm()" x-init="init()">
    <div class="panel-heading">
        <h3 class="panel-title">
            {{ __('Create Alert Rule') }}
            <div class="pull-right">
                <a target="_blank" href="https://docs.librenms.org/Alerting/" class="tw:mr-5"><i class="fa fa-book"></i> {{ __('Documentation') }}</a>
                <a href="javascript:void(0);" onclick="window.history.back();" class="tw:text-gray-700 tw:hover:text-red-600 tw:no-underline tw:text-3xl tw:transition-colors tw:duration-200" title="{{ __('Close') }}">
                    <i class="fa fa-times"></i>
                </a>
            </div>
        </h3>
    </div>
    <div class="panel-body">
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active"><a href="#main" aria-controls="main" role="tab" data-toggle="tab">{{ __('Main') }}&nbsp;</a></li>
            <li role="presentation"><a href="#advanced" aria-controls="advanced" role="tab" data-toggle="tab">{{ __('Advanced') }}&nbsp;</a></li>
        </ul>
        <form method="post" role="form" id="rules" class="form-horizontal alerts-form">
            @csrf

            <div class="tab-content" style="margin-top: 15px;">
    <div role="tabpanel" class="tab-pane active" id="main">
        <legend>{{ __('alerting.rules.setup.legend') }}</legend>
        <div class='form-group'>
            <label for='rule_name' class='col-sm-3 col-md-2 control-label'>{{ __('alerting.rules.setup.name.label') }}</label>
            <div class='col-sm-9 col-md-10'>
                <input type='text' id='rule_name' name='name' class='form-control validation' maxlength='200' required x-model="rule.name">
                <span class="help-block">{{ __('alerting.rules.setup.name.help') }}</span>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-3 col-md-2">
                <div class="dropdown">
                    <button class="btn btn-default dropdown-toggle" type="button" id="import-from" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                        {{ __('alerting.rules.setup.import.button') }}
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="import-from" id="import-dropdown">
                        <li><a href="#" name="import-query" id="import-query">{{ __('alerting.rules.setup.import.sql_query') }}</a></li>
                        <li><a href="#" name="import-old-format" id="import-old-format">{{ __('alerting.rules.setup.import.old_format') }}</a></li>
                        <li><a href="#" name="import-collection" id="import-collection">{{ __('alerting.rules.setup.import.collection') }}</a></li>
                        <li><a href="#" name="import-alert_rule" id="import-alert_rule">{{ __('alerting.rules.setup.import.alert_rule') }}</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-sm-9 col-md-10">
                <div id="builder"></div>
                <span class="help-block">{{ __('alerting.rules.setup.builder.help') }}</span>
            </div>
        </div>
        <div class='form-group'>
            <label for='invert' class='col-sm-3 col-md-2 control-label'>{{ __('alerting.rules.setup.invert_match.label') }} </label>
            <div class='form col-sm-9 col-md-10'>
                <input type="checkbox" name="invert" id="invert">
                <span class="help-block">{{ __('alerting.rules.setup.invert_match.help') }}</span>
            </div>
        </div>

        <legend>{{ __('alerting.rules.targeting.legend') }}</legend>

        <div class="form-group">
            <label for='maps' class='col-sm-3 col-md-2 control-label'>{{ __('alerting.rules.targeting.maps.label') }} </label>
            <div class="col-sm-9 col-md-10">
                <select id="maps" name="maps[]" class="form-control" multiple="multiple"></select>
                <span class="help-block">{{ __('alerting.rules.targeting.maps.help') }}</span>
            </div>
        </div>

        <div class="form-group">
            <label for='invert_map' class='col-sm-3 col-md-2 control-label' text-align="left">{{ __('alerting.rules.targeting.invert_map.label') }} </label>
            <div class="col-sm-9 col-md-10">
                <input type='checkbox' name='invert_map' id='invert_map'>
                <span class="help-block">{{ __('alerting.rules.targeting.invert_map.help') }}</span>
            </div>
        </div>

        <legend>{{ __('alerting.rules.notifications.legend') }}</legend>
        <div class="form-group">
            <label for='severity' class='col-sm-3 col-md-2 control-label'>{{ __('alerting.rules.notifications.severity.label') }}</label>
            <div class="col-sm-9 col-md-10">
                <select name='severity' id='severity' class='form-control' style="max-width: 7.5em" x-model="rule.severity">
                    <option value='ok'>{{ __('alerting.rules.notifications.severity.options.ok') }}</option>
                    <option value='warning'>{{ __('alerting.rules.notifications.severity.options.warning') }}</option>
                    <option value='critical'>{{ __('alerting.rules.notifications.severity.options.critical') }}</option>
                </select>
                <span class="help-block">{{ __('alerting.rules.notifications.severity.help') }}</span>
            </div>
        </div>
        <div class="form-group">
            <label for='delay' class='col-sm-3 col-md-2 control-label'>{{ __('alerting.rules.notifications.delay.label') }}</label>
            <div class="col-sm-9 col-md-10">
                <input type='text' id='delay' name='delay' class='form-control' style="max-width: 7.5em" value="{{ $default_delay ?? '' }}" x-model="rule.extra.delay">
                <span class="help-block">{{ __('alerting.rules.notifications.delay.help') }}</span>
            </div>
        </div>
        <div class="form-group">
            <label for='count' class='col-sm-3 col-md-2 control-label'>{{ __('alerting.rules.notifications.count.label') }}</label>
            <div class="col-sm-9 col-md-10">
                <input type='text' id='count' name='count' class='form-control' style="max-width: 7.5em" value="{{ $default_max_alerts ?? '' }}" x-model="rule.extra.count">
                <span class="help-block">{{ __('alerting.rules.notifications.count.help') }}</span>
            </div>
        </div>
        <div class="form-group">
            <label for='interval' class='col-sm-3 col-md-2 control-label'>{{ __('alerting.rules.notifications.interval.label') }}</label>
            <div class="col-sm-9 col-md-10">
                <input type='text' id='interval' name='interval' class='form-control' style="max-width: 7.5em" value="{{ $default_interval ?? '' }}" x-model="rule.extra.interval">
                <span class="help-block">{{ __('alerting.rules.notifications.interval.help') }}</span>
            </div>
        </div>
        <div class='form-group'>
            <label for='mute' class='col-sm-3 col-md-2 control-label'>{{ __('alerting.rules.notifications.mute.label') }}</label>
            <div class='col-sm-9 col-md-10'>
                <input type="checkbox" x-data="toggleInput()" x-model="rule.extra.mute">
                <span class="help-block">{{ __('alerting.rules.notifications.mute.help') }}</span>
            </div>
        </div>
        <div class='form-group'>
            <label for='recovery' class='col-sm-3 col-md-2 control-label'>{{ __('alerting.rules.notifications.recovery.label') }}</label>
            <div class='col-sm-9 col-md-10'>
                <input type="checkbox" name="recovery" id="recovery" x-model="rule.extra.recovery">
                <span class="help-block">{{ __('alerting.rules.notifications.recovery.help') }}</span>
            </div>
        </div>
        <div class='form-group'>
            <label for='acknowledgement' class='col-sm-3 col-md-2 control-label'>{{ __('alerting.rules.notifications.acknowledgement.label') }}</label>
            <div class='col-sm-9 col-md-10'>
                <input type="checkbox" name="acknowledgement" id="acknowledgement" x-model="rule.extra.acknowledgement">
                <span class="help-block">{{ __('alerting.rules.notifications.acknowledgement.help') }}</span>
            </div>
        </div>

        <legend>{{ __('alerting.rules.notifications.delivery.legend') }}</legend>
        <div class="form-group">
            <label for="transports" class="col-sm-3 col-md-2 control-label">{{ __('alerting.rules.notifications.delivery.label') }}</label>
            <div class="col-sm-9 col-md-10">
                <select id="transports" name="transports[]" class="form-control" multiple="multiple" x-model="rule.template"></select>
                <span class="help-block">{{ __('alerting.rules.notifications.delivery.help') }}</span>
            </div>
        </div>

        <legend>{{ __('alerting.rules.templates.legend') }}</legend>
        <div class="form-group">
            <label for='template_id' class='col-sm-3 col-md-2 control-label'>{{ __('alerting.rules.templates.label') }}</label>
            <div class='col-sm-9 col-md-10'>
                <select id="template_id" name="template_id" class="form-control">
                    <option value="">{{ __('alerting.rules.templates.use_default') }}</option>
                    @foreach(($templates ?? []) as $tpl)
                        <option value="{{ $tpl->id }}">{{ $tpl->name }}</option>
                    @endforeach
                </select>
                <span class="help-block">{{ __('alerting.rules.templates.help') }}</span>
            </div>
        </div>
        <div class="form-group" id="per-transport-templates">
            <label class='col-sm-3 col-md-2 control-label'>{{ __('alerting.rules.templates.per_transport.label') }}</label>
            <div class='col-sm-9 col-md-10'>
                <div id="transport-template-list"></div>
                <span class="help-block">{{ __('alerting.rules.templates.per_transport.help') }}</span>
            </div>
        </div>

        <legend>{{ __('alerting.rules.notes.legend') }}</legend>
        <div class='form-group'>
            <label for='proc' class='col-sm-3 col-md-2 control-label'>{{ __('alerting.rules.notes.proc_url.label') }} </label>
            <div class='col-sm-9 col-md-10'>
                <input type='text' id='proc' name='proc' class='form-control validation' pattern='(http|https)://.*' maxlength='80' x-model="rule.proc">
                <span class="help-block">{{ __('alerting.rules.notes.proc_url.help') }}</span>
            </div>
        </div>
        <div class='form-group'>
            <label for='notes' class='col-sm-3 col-md-2 control-label'>{{ __('alerting.rules.notes.notes.label') }}</label>
            <div class='col-sm-9 col-md-10'>
                <textarea class="form-control" rows="6" name="notes" id='notes' x-model="rule.notes"></textarea>
                <span class="help-block">{{ __('alerting.rules.notes.notes.help') }}</span>
            </div>
        </div>
    </div>
    <div role="tabpanel" class="tab-pane" id="advanced">
        <div class='form-group'>
            <label for='override_query' class='col-sm-3 col-md-2 control-label'>{{ __('alerting.rules.advanced.override_sql.label') }}</label>
            <div class='col-sm-9 col-md-10'>
                <input type='checkbox' name='override_query' id='override_query' x-model="rule.extra.override_query">
            </div>
        </div>
        <div class='form-group'>
            <label for='adv_query' class='col-sm-3 col-md-2 control-label'>{{ __('alerting.rules.advanced.sql.label') }}</label>
            <div class='col-sm-9 col-md-10'>
                <textarea id='adv_query' name='adv_query' class='form-control' rows="3" x-model="rule.extra.adv_query"></textarea>
                <span class="help-block">{{ __('alerting.rules.advanced.sql.help') }}</span>
            </div>
        </div>
    </div>
</div>

<div class="form-group">
    <div class="col-sm-offset-3 col-sm-9 col-md-offset-2 col-md-10">
        <button @click.prevent="save" class="btn btn-primary">{{ __('Save') }}</button>
        <a href="javascript:void(0);" onclick="window.history.back();" class="btn btn-default">{{ __('Cancel') }}</a>
    </div>
</div>

        </form>
    </div>
</div>

@section('javascript')
    <script src="{{ asset('js/sql-parser.min.js') }}"></script>
    <script src="{{ asset('js/query-builder.standalone.min.js') }}"></script>
    <script src="{{ asset('js/interact.min.js') }}"></script>
@endsection

@push('scripts')
<script>
    function alertRuleForm() {
        return {
            mode: @json($mode ?? 'create'),
            saveUrl: @json($saveUrl ?? url('alert-rule')),
            saveMethod: @json($saveMethod ?? 'POST'),
            loadUrl: @json($loadUrl ?? null),
            rule: {
                name: '',
                severity: 'critical',
                notes: '',
                builder: null,
                proc: '',
                extra: {
                    mute: {{ ($default_mute_alerts ?? false) ? 'true' : 'false' }},
                    invert: {{ ($default_invert_rule_match ?? false) ? 'true' : 'false' }},
                    recovery: {{ ($default_recovery_alerts ?? false) ? 'true' : 'false' }},
                    acknowledgement: {{ ($default_acknowledgement_alerts ?? false) ? 'true' : 'false' }},
                    override_query: false,
                    adv_query: '',
                },
                maps: [],
                transports: [],
            },

            init() {
                this.initQueryBuilder();
                this.initSelect2('#maps', 'devices_groups_locations', v => this.rule.maps = v);
                this.initSelect2('#transports', 'transport_groups', v => this.rule.transports = v);

                if (this.mode === 'edit' && this.loadUrl) {
                    fetch(this.loadUrl)
                        .then(r => r.json())
                        .then(data => this.loadRule(data))
                        .catch(() => toastr.error("Failed to load rule"));
                }
            },

            initQueryBuilder() {
                $('#builder').queryBuilder({
                    plugins: ['bt-tooltip-errors'],
                    allow_empty: true,
                    filters: {!! $filters !!},
                });
            },

            initSelect2(el, type, callback) {
                $(el).select2({
                    width: '100%',
                    ajax: {
                        url: 'ajax_list.php',
                        delay: 250,
                        data: params => ({ type, search: params.term })
                    }
                }).on('change', () => callback($(el).val()));
            },

            loadRule(rule) {
                this.rule = {
                    ...this.rule,
                    ...rule,
                    extra: { ...this.rule.extra, ...(rule.extra || {}) },
                    maps: rule.maps?.map(m => m.id) || [],
                    transports: rule.transports?.map(t => t.id) || [],
                };
                $('#builder').queryBuilder('setRules', this.rule.builder || {});
                $('#maps').val(this.rule.maps).trigger('change');
                $('#transports').val(this.rule.transports).trigger('change');
            },

            async save() {
                const rules = $('#builder').queryBuilder('getRules');
                if (!rules?.valid) {
                    toastr.error("Invalid rule");
                    return;
                }
                this.rule.builder = rules;

                try {
                    const res = await fetch(this.saveUrl, {
                        method: this.saveMethod,
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(this.rule)
                    });
                    const data = await res.json();
                    if (data.status === 'ok') {
                        toastr.success(data.message);
                        window.location.href = "{{ url('alert-rules') }}";
                    } else {
                        toastr.error(data.message);
                    }
                } catch {
                    toastr.error("Failed to process rule");
                }
            },

            importSql() {
                const sql = window.prompt("Enter SQL query");
                if (!sql) return;
                try {
                    $("#builder").queryBuilder("setRulesFromSQL", sql);
                } catch {
                    alert("Could not parse SQL");
                }
            },

            importOld() {
                let input = window.prompt("Enter old rule format");
                if (!input) return;
                try {
                    input = input
                        .replace(/&&/g, 'AND')
                        .replace(/\|\|/g, 'OR')
                        .replace(/%/g, '')
                        .replace(/"/g, "'")
                        .replace(/~/g, 'REGEXP')
                        .replace(/@/g, '.*');
                    $("#builder").queryBuilder("setRulesFromSQL", input);
                } catch {
                    alert("Could not parse old rule");
                }
            }
        }
    }
</script>
<script>
    // helper to format seconds to s/m/h/d string
    function formatDuration(val) {
        if (val == null || isNaN(val)) { return ''; }
        if ((val / 86400) >= 1) { return (val / 86400) + 'd'; }
        if ((val / 3600) >= 1) { return (val / 3600) + 'h'; }
        if ((val / 60) >= 1) { return (val / 60) + 'm'; }
        return String(val);
    }

    // Load a rule object into the form
    function loadRule(rule) {
        try {
            if (rule.name) { $('#rule_name').val(rule.name); }
            if (rule.builder) { $('#builder').queryBuilder('setRules', rule.builder); }
            if (rule.severity) { $('#severity').val(rule.severity).trigger('change'); }

            var extra = rule.extra || {};
            if (typeof extra.count !== 'undefined') { $('#count').val(extra.count); }
            if (typeof extra.delay !== 'undefined') { $('#delay').val(formatDuration(extra.delay)); }
            if (typeof extra.interval !== 'undefined') { $('#interval').val(formatDuration(extra.interval)); }
            if (extra.adv_query) { $('#adv_query').val(extra.adv_query); }

            // proc url and notes
            if (typeof rule.proc !== 'undefined') { $('#proc').val(rule.proc); }
            if (typeof rule.notes !== 'undefined') { $('#notes').val(rule.notes); }

            // maps
            var $maps = $('#maps');
            $maps.empty();
            $maps.val(null).trigger('change');
            if (rule.maps == null) {
                if (typeof window.setRuleDevice === 'function') { window.setRuleDevice(); }
            } else {
                $.each(rule.maps, function(index, value) {
                    var option = new Option(value.text, value.id, true, true);
                    $maps.append(option).trigger('change');
                });
            }

            // transports
            var $transports = $('#transports');
            $transports.empty();
            $transports.val(null).trigger('change');
            if (rule.transports != null) {
                $.each(rule.transports, function(index, value) {
                    var option = new Option(value.text, value.id, true, true);
                    $transports.append(option).trigger('change');
                });
            }

            // Set switches
            if (typeof extra.mute !== 'undefined') { $("[name='mute']").bootstrapSwitch('state', !!extra.mute); }
            if (typeof extra.invert !== 'undefined') { $("[name='invert']").bootstrapSwitch('state', !!extra.invert); }

            var recovery = (typeof extra.recovery !== 'undefined') ? !!extra.recovery : {{ ($default_recovery_alerts ?? false) ? 'true' : 'false' }};
            var acknowledgement = (typeof extra.acknowledgement !== 'undefined') ? !!extra.acknowledgement : {{ ($default_acknowledgement_alerts ?? false) ? 'true' : 'false' }};
            var override = (extra.options && typeof extra.options.override_query !== 'undefined') ? !!extra.options.override_query : false;

            $("[name='recovery']").bootstrapSwitch('state', recovery);
            $("[name='acknowledgement']").bootstrapSwitch('state', acknowledgement);
            $("[name='invert_map']").bootstrapSwitch('state', (rule.invert_map == 1));
            $("[name='override_query']").bootstrapSwitch('state', override);
        } catch (e) {
            console.error('Failed to load rule into form', e);
        }
    }

    (function(){
        var MODE = @json($mode ?? 'create');
        var SAVE_URL = @json($saveUrl ?? url('alert-rule'));
        var SAVE_METHOD = @json($saveMethod ?? 'POST');
        var LOAD_URL = @json($loadUrl ?? null);


        $(function() {
            // Initialize switches
            $('#mute').bootstrapSwitch();
            $('#invert').bootstrapSwitch();
            $('#recovery').bootstrapSwitch();
            $('#acknowledgement').bootstrapSwitch();
            $('#override_query').bootstrapSwitch();
            $('#invert_map').bootstrapSwitch();

            // Initialize select2 for maps and transports
            window.setRuleDevice = function() {
                var device_id = $('#device_id').val();
                if (device_id > 0) {
                    var device_name = $('#device_name').val();
                    var option = new Option(device_name, device_id, true, true);
                    $('#maps').append(option).trigger('change');
                }
            }
            $('#maps').select2({ width: '100%', placeholder: @json(__('alerting.rules.placeholders.maps')), ajax: { url: 'ajax_list.php', delay: 250, data: function (params) { return { type: 'devices_groups_locations', search: params.term }; } } });
            $('#transports').select2({ width: '100%', placeholder: @json(__('alerting.rules.placeholders.transports')), ajax: { url: 'ajax_list.php', delay: 250, data: function (params) { return { type: 'transport_groups', search: params.term }; } } });

            // Templates JS data
            var templateOptions = @json(($templates ?? collect())->toArray());
            var perTransportNoOverride = @json(__('alerting.rules.templates.per_transport.no_override'));
            function renderPerTransportTemplates() {
                var $list = $('#transport-template-list');
                $list.empty();
                var data = ($('#transports').data('select2') ? $('#transports').select2('data') : []) || [];
                if (!data.length) { return; }
                var added = {}; var rows = [];
                function addRow(id, text) {
                    if (!id || added[id]) { return; }
                    added[id] = true;
                    var labelText = text || ('Transport #' + id);
                    var row = $('<div class="form-inline" style="margin-bottom:6px;"></div>');
                    var label = $('<label class="control-label" style="min-width:220px; margin-right:8px;"></label>').text(labelText);
                    var select = $('<select class="form-control" style="min-width:260px;"></select>').attr('name', 'template_transports[' + id + ']');
                    select.append($('<option value=""></option>').text(perTransportNoOverride));
                    templateOptions.forEach(function(opt){ select.append($('<option></option>').attr('value', opt.id).text(opt.name)); });
                    row.append(label).append(select);
                    rows.push(row);
                }
                var ajaxCalls = [];
                data.forEach(function(item) {
                    var key = item.id;
                    if (String(key).startsWith('g')) {
                        var groupId = String(key).substring(1);
                        var call = $.getJSON('{{ route('alert.transport-groups.members', ':group_id') }}'.replace(':group_id', groupId))
                            .done(function(resp){ if (resp && resp.members && resp.members.length) { resp.members.forEach(function(member){ addRow(member.id, member.text); }); } })
                            .fail(function(){});
                        ajaxCalls.push(call);
                    } else { addRow(key, item.text); }
                });
                $.when.apply($, ajaxCalls).always(function(){ rows.forEach(function(r){ $list.append(r); }); });
            }
            $('#transports').on('change', renderPerTransportTemplates);

            // Initialize query builder
            var filters = {!! $filters !!};
            $('#builder').queryBuilder({
                plugins: ['bt-tooltip-errors'], allow_empty: true, filters: filters,
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
                sqlRuleOperator: { 'REGEXP': function (v) { return {val: v, op: 'regexp'}; }, 'NOT REGEXP': function (v) { return {val: v, op: 'not_regexp'}; } }
            });

            if (MODE === 'create') {
                $('#rule_id').val('');
                var $severity = $('#severity');
                if (!$severity.find('option:selected').length) { $severity.val($severity.find("option[selected]").val()); }
                $("#mute").bootstrapSwitch('state', {{ ($default_mute_alerts ?? false) ? 'true' : 'false' }});
                $("#invert").bootstrapSwitch('state', {{ ($default_invert_rule_match ?? false) ? 'true' : 'false' }});
                $("#recovery").bootstrapSwitch('state', {{ ($default_recovery_alerts ?? false) ? 'true' : 'false' }});
                $("#acknowledgement").bootstrapSwitch('state', {{ ($default_acknowledgement_alerts ?? false) ? 'true' : 'false' }});
                $("#override_query").bootstrapSwitch('state', false);
                $("#invert_map").bootstrapSwitch('state', {{ ($default_invert_map ?? false) ? 'true' : 'false' }});
                $('#proc').val(''); $('#notes').val(''); $('#maps').val(null).trigger('change'); $('#transports').val(null).trigger('change');
                if (typeof window.setRuleDevice === 'function') { setRuleDevice(); }
            } else if (MODE === 'edit' && LOAD_URL) {
                $.getJSON(LOAD_URL).done(function (data) { loadRule(data); }).fail(function () { toastr.error(@json(__('alerting.rules.messages.failed_load_rule'))); });
            }

            // Save handler
            $('#btn-save').on('click', function (e) {
                e.preventDefault();
                var result_json = $('#builder').queryBuilder('getRules');
                if (result_json !== null && result_json.valid) {
                    $('#builder_json').val(JSON.stringify(result_json));
                    $.ajax({ type: SAVE_METHOD, url: SAVE_URL, data: $('form.alerts-form').serializeArray(), dataType: 'json',
                        success: function (data) {
                            if (data.status === 'ok') { toastr.success(data.message); window.location.href = "{{ url('alert-rules') }}"; }
                            else { toastr.error(data.message); }
                        },
                        error: function () { toastr.error(@json(__('alerting.rules.messages.failed_process_rule'))); }
                    });
                } else { toastr.error(@json(__('alerting.rules.messages.invalid_rule'))); }
            });

            // Import helpers
            $('#import-query').on('click', function (e) {
                e.preventDefault();
                var sql_import = window.prompt(@json(__('alerting.rules.messages.prompt_sql_query')));
                if (sql_import) { try { $("#builder").queryBuilder("setRulesFromSQL", sql_import); } catch (e) { alert(@json(__('alerting.rules.messages.query_not_parsed'))); } }
            });
            $('#import-old-format').on('click', function (e) {
                e.preventDefault();
                var old_import = window.prompt(@json(__('alerting.rules.messages.prompt_old_rule')));
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
            $('#import-collection').on('click', function (e) { e.preventDefault(); $("#search_rule_modal").modal('show'); });
            $('#import-alert_rule').on('click', function (e) { e.preventDefault(); $("#search_alert_rule_modal").modal('show'); });
        });
    })();
</script>
@endpush
