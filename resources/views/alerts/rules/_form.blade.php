<div class="tab-content" style="margin-top: 15px;">
    <div role="tabpanel" class="tab-pane active" id="main">
        <legend>{{ __('Rule setup') }}</legend>
        <div class='form-group'>
            <label for='rule_name' class='col-sm-3 col-md-2 control-label'>{{ __('Rule name') }}</label>
            <div class='col-sm-9 col-md-10'>
                <input type='text' id='rule_name' name='name' class='form-control validation' maxlength='200' required>
                <span class="help-block">{{ __('A display name for this alert rule') }}</span>
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
                <span class="help-block">{{ __('Build an SQL query to match rows from the database. If the query returns row(s) this alert will trigger.') }}</span>
            </div>
        </div>
        <div class='form-group'>
            <label for='invert' class='col-sm-3 col-md-2 control-label'>Invert match result </label>
            <div class='form col-sm-9 col-md-10'>
                <input type="checkbox" name="invert" id="invert">
                <span class="help-block">{{ __('Invert the match. If the rule matches, the alert is considered OK.') }}</span>
            </div>
        </div>

        <legend>{{ __('Targeting') }}</legend>

        <div class="form-group">
            <label for='maps' class='col-sm-3 col-md-2 control-label'>Devices, groups, and locations </label>
            <div class="col-sm-9 col-md-10">
                <select id="maps" name="maps[]" class="form-control" multiple="multiple"></select>
                <span class="help-block">{{ __('Restrict this alert rule to the selected devices, groups, or locations.') }}</span>
            </div>
        </div>

        <div class="form-group">
            <label for='invert_map' class='col-sm-3 col-md-2 control-label' text-align="left">Run on all devices except selected </label>
            <div class="col-sm-9 col-md-10">
                <input type='checkbox' name='invert_map' id='invert_map'>
                <span class="help-block">{{ __('If ON, alert rule checks will run on all devices except the selected devices and groups.') }}</span>
            </div>
        </div>

        <legend>{{ __('Notifications') }}</legend>
        <div class="form-group">
            <label for='severity' class='col-sm-3 col-md-2 control-label'>{{ __('Severity') }}</label>
            <div class="col-sm-9 col-md-10">
                <select name='severity' id='severity' class='form-control' style="max-width: 7.5em">
                    <option value='ok' {{ ($default_severity ?? '') === 'ok' ? 'selected' : '' }}>OK</option>
                    <option value='warning' {{ ($default_severity ?? '') === 'warning' ? 'selected' : '' }}>Warning</option>
                    <option value='critical' {{ ($default_severity ?? '') === 'critical' ? 'selected' : '' }}>Critical</option>
                </select>
                <span class="help-block">{{ __('How to display the alert.  OK: green, Warning: yellow, Critical: red') }}</span>
            </div>
        </div>
        <div class="form-group">
            <label for='delay' class='col-sm-3 col-md-2 control-label'>Delay</label>
            <div class="col-sm-9 col-md-10">
                <input type='text' id='delay' name='delay' class='form-control' style="max-width: 7.5em" value="{{ $default_delay ?? '' }}">
                <span class="help-block">{{ __('How long to wait before issuing a notification. If the alert clears before the delay, no notification will be issued. (s,m,h,d)') }}</span>
            </div>
        </div>
        <div class="form-group">
            <label for='count' class='col-sm-3 col-md-2 control-label'>Max alerts</label>
            <div class="col-sm-9 col-md-10">
                <input type='text' id='count' name='count' class='form-control' style="max-width: 7.5em" value="{{ $default_max_alerts ?? '' }}">
                <span class="help-block">{{ __('How many notifications to issue while active before stopping. -1 means no limit. If interval is 0, this has no effect.') }}</span>
            </div>
        </div>
        <div class="form-group">
            <label for='interval' class='col-sm-3 col-md-2 control-label'>Interval</label>
            <div class="col-sm-9 col-md-10">
                <input type='text' id='interval' name='interval' class='form-control' style="max-width: 7.5em" value="{{ $default_interval ?? '' }}">
                <span class="help-block">{{ __('How often to re-issue notifications while this alert is active. 0 means notify once. This is affected by the poller interval. (s,m,h,d)') }}</span>
            </div>
        </div>
        <div class='form-group'>
            <label for='mute' class='col-sm-3 col-md-2 control-label'>Mute alerts </label>
            <div class='col-sm-9 col-md-10'>
                <input type="checkbox" name="mute" id="mute">
                <span class="help-block">{{ __('Show alert status in the webui, but do not issue notifications.') }}</span>
            </div>
        </div>
        <div class='form-group'>
            <label for='recovery' class='col-sm-3 col-md-2 control-label'>Recovery alerts </label>
            <div class='col-sm-9 col-md-10'>
                <input type="checkbox" name="recovery" id="recovery">
                <span class="help-block">{{ __('Send recovery notification when alert clears.') }}</span>
            </div>
        </div>
        <div class='form-group'>
            <label for='acknowledgement' class='col-sm-3 col-md-2 control-label'>Acknowledgement alerts </label>
            <div class='col-sm-9 col-md-10'>
                <input type="checkbox" name="acknowledgement" id="acknowledgement">
                <span class="help-block">{{ __('Send acknowledgement notification when alert is acknowledged.') }}</span>
            </div>
        </div>

        <legend>{{ __('Delivery transports') }}</legend>
        <div class="form-group">
            <label for="transports" class="col-sm-3 col-md-2 control-label">Transports </label>
            <div class="col-sm-9 col-md-10">
                <select id="transports" name="transports[]" class="form-control" multiple="multiple"></select>
                <span class="help-block">{{ __('Restricts this alert rule to specified transports.') }}</span>
            </div>
        </div>

        <legend>{{ __('Templates') }}</legend>
        <div class="form-group">
            <label for='template_id' class='col-sm-3 col-md-2 control-label'>{{ __('Template') }}</label>
            <div class='col-sm-9 col-md-10'>
                <select id="template_id" name="template_id" class="form-control">
                    <option value="">{{ __('Use default template') }}</option>
                    @foreach(($templates ?? []) as $tpl)
                        <option value="{{ $tpl->id }}">{{ $tpl->name }}</option>
                    @endforeach
                </select>
                <span class="help-block">{{ __('Choose template for all transports. You can override per transport below.') }}</span>
            </div>
        </div>
        <div class="form-group" id="per-transport-templates">
            <label class='col-sm-3 col-md-2 control-label'>{{ __('Per-transport overrides') }}</label>
            <div class='col-sm-9 col-md-10'>
                <div id="transport-template-list"></div>
                <span class="help-block">{{ __('After selecting transports above, choose a template for any you want to override.') }}</span>
            </div>
        </div>

        <legend>{{ __('Notes & Documentation') }}</legend>
        <div class='form-group'>
            <label for='proc' class='col-sm-3 col-md-2 control-label'>Procedure URL </label>
            <div class='col-sm-9 col-md-10'>
                <input type='text' id='proc' name='proc' class='form-control validation' pattern='(http|https)://.*' maxlength='80'>
                <span class="help-block">{{ __('A link to some documentation on how to handle this alert. This can be included in notifications.') }}</span>
            </div>
        </div>
        <div class='form-group'>
            <label for='notes' class='col-sm-3 col-md-2 control-label'>Notes</label>
            <div class='col-sm-9 col-md-10'>
                <textarea class="form-control" rows="6" name="notes" id='notes'></textarea>
                <span class="help-block">{{ __('A brief description for this alert rule') }}</span>
            </div>
        </div>
    </div>
    <div role="tabpanel" class="tab-pane" id="advanced">
        <div class='form-group'>
            <label for='override_query' class='col-sm-3 col-md-2 control-label'>Override SQL</label>
            <div class='col-sm-9 col-md-10'>
                <input type='checkbox' name='override_query' id='override_query'>
            </div>
        </div>
        <div class='form-group'>
            <label for='adv_query' class='col-sm-3 col-md-2 control-label'>SQL</label>
            <div class='col-sm-9 col-md-10'>
                <textarea id='adv_query' name='adv_query' class='form-control' rows="3"></textarea>
                <span class="help-block">{{ __('Optional: Provide a raw SQL WHERE clause to override the builder.') }}</span>
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

@section('javascript')
    <script src="{{ asset('js/sql-parser.min.js') }}"></script>
    <script src="{{ asset('js/query-builder.standalone.min.js') }}"></script>
    <script src="{{ asset('js/interact.min.js') }}"></script>
@endsection

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

        // Collection modal grid (if present on page)
        var $collectionTable = $("#rule_collection");
        if ($collectionTable.length) {
            var grid = $collectionTable.bootgrid({
                caseSensitive: false,
                formatters: { "action": function (column, row) { return "<button type=\"button\" data-rule_id=\"" + row.action + "\" class=\"btn btn-sm btn-primary rule_from_collection\">Select<\/button>"; } }
            }).on("loaded.rs.jquery.bootgrid", function () {
                grid.find(".rule_from_collection").on("click", function () {
                    var template_rule_id = $(this).data("rule_id");
                    $.getJSON('{{ route('alert-rule-template.show', ':template_id') }}'.replace(':template_id', template_rule_id))
                        .done(function (data) { if (data.status === 'ok') { $("#search_rule_modal").modal('hide'); loadRule(data); } else { toastr.error(data.message || 'Failed to load template'); } })
                        .fail(function () { toastr.error('Failed to process template'); });
                }).end();
            });
        }

        // Existing alert rules modal grid (if present)
        var $alertTable = $("#alert_rule_list");
        if ($alertTable.length) {
            var alert_grid = $alertTable.bootgrid({
                caseSensitive: false,
                formatters: { "alert_action": function (column, row) { return "<button type=\"button\" data-rule_id=\"" + row.alert_action + "\" class=\"btn btn-sm btn-primary alert_rule_from_list\">Select<\/button>"; } },
                templates: { footer: "<div id=\"@{{ctx.id}}\" class=\"@{{css.footer}}\"><div class=\"row\"><div class=\"col-sm-12\"><p class=\"@{{css.pagination}}\"></p></div></div></div>" }
            }).on("loaded.rs.jquery.bootgrid", function() {
                alert_grid.find(".alert_rule_from_list").on("click", function() {
                    var alert_rule_id = $(this).data("rule_id");
                    $.getJSON('{{ route('alert-rule-template.rule', ':rule_id') }}'.replace(':rule_id', alert_rule_id))
                        .done(function (data) { if (data.status === 'ok') { $("#search_alert_rule_modal").modal('hide'); loadRule(data); } else { toastr.error(data.message || 'Failed to load rule'); } })
                        .fail(function () { toastr.error('Failed to process template'); });
                }).end();
            });
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
            window.setRuleDevice = function() {
                var device_id = $('#device_id').val();
                if (device_id > 0) {
                    var device_name = $('#device_name').val();
                    var option = new Option(device_name, device_id, true, true);
                    $('#maps').append(option).trigger('change');
                }
            }
            $('#maps').select2({ width: '100%', placeholder: 'Devices, Groups or Locations', ajax: { url: 'ajax_list.php', delay: 250, data: function (params) { return { type: 'devices_groups_locations', search: params.term }; } } });
            $('#transports').select2({ width: '100%', placeholder: 'Use default transport', ajax: { url: 'ajax_list.php', delay: 250, data: function (params) { return { type: 'transport_groups', search: params.term }; } } });

            // Templates JS data
            var templateOptions = @json(($templates ?? collect())->toArray());
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
                    select.append($('<option value=""></option>').text('— No Override —'));
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
                $.getJSON(LOAD_URL).done(function (data) { loadRule(data); }).fail(function () { toastr.error('Failed to load rule'); });
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
                        error: function () { toastr.error('Failed to process rule'); }
                    });
                } else { toastr.error('Invalid rule, please complete the required fields'); }
            });

            // Import helpers
            $('#import-query').on('click', function (e) {
                e.preventDefault();
                var sql_import = window.prompt('Enter your SQL query:');
                if (sql_import) { try { $("#builder").queryBuilder("setRulesFromSQL", sql_import); } catch (e) { alert('Your query could not be parsed'); } }
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
            $('#import-collection').on('click', function (e) { e.preventDefault(); $("#search_rule_modal").modal('show'); });
            $('#import-alert_rule').on('click', function (e) { e.preventDefault(); $("#search_alert_rule_modal").modal('show'); });
        });
    })();
</script>
@endpush
