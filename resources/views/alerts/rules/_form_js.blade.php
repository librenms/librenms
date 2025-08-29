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

        // Collection modal grid
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
                $.getJSON({{ json_encode(route('alert-rule-template', ':template_id')) }}.replace(':template_id', template_rule_id))
                    .done(function (data) {
                        if (data.status === 'ok') {
                            $("#search_rule_modal").modal('hide');
                            loadRule(data);
                        } else {
                            toastr.error(data.message || 'Failed to load template');
                        }
                    })
                    .fail(function () { toastr.error('Failed to process template'); });
            }).end();
        });

        // Existing alert rules modal grid
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
                $.getJSON({{ json_encode(route('alert-rule-template.rule', ':rule_id')) }}.replace(':rule_id', alert_rule_id))
                    .done(function (data) {
                        if (data.status === 'ok') {
                            $("#search_alert_rule_modal").modal('hide');
                            loadRule(data);
                        } else { toastr.error(data.message || 'Failed to load rule'); }
                    })
                    .fail(function () { toastr.error('Failed to process template'); });
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

            // Initialize select2 for maps and transports
            window.setRuleDevice = function() {
                var device_id = $('#device_id').val();
                if (device_id > 0) {
                    var device_name = $('#device_name').val();
                    var option = new Option(device_name, device_id, true, true);
                    $('#maps').append(option).trigger('change');
                }
            }
            $('#maps').select2({
                width: '100%',
                placeholder: 'Devices, Groups or Locations',
                ajax: {
                    url: 'ajax_list.php',
                    delay: 250,
                    data: function (params) {
                        return { type: 'devices_groups_locations', search: params.term };
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
            var templateOptions = @json(($templates ?? collect())->toArray());
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
                        var call = $.getJSON({{ json_encode(route('alert.transport-groups.members', ':group_id')) }}.replace(':group_id', groupId))
                            .done(function(resp){
                                if (resp && resp.members && resp.members.length) {
                                    resp.members.forEach(function(member){
                                        addRow(member.id, member.text);
                                    });
                                }
                            }).fail(function(){ /* ignore group load errors */ });
                        ajaxCalls.push(call);
                    } else {
                        addRow(key, item.text);
                    }
                });

                // Once all group members are loaded, render rows
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

            if (MODE === 'create') {
                // default state for create page
                $('#rule_id').val('');
                var $severity = $('#severity');
                if (!$severity.find('option:selected').length) {
                    $severity.val($severity.find("option[selected]").val());
                }
                $("#mute").bootstrapSwitch('state', {{ ($default_mute_alerts ?? false) ? 'true' : 'false' }});
                $("#invert").bootstrapSwitch('state', {{ ($default_invert_rule_match ?? false) ? 'true' : 'false' }});
                $("#recovery").bootstrapSwitch('state', {{ ($default_recovery_alerts ?? false) ? 'true' : 'false' }});
                $("#acknowledgement").bootstrapSwitch('state', {{ ($default_acknowledgement_alerts ?? false) ? 'true' : 'false' }});
                $("#override_query").bootstrapSwitch('state', false);
                $("#invert_map").bootstrapSwitch('state', {{ ($default_invert_map ?? false) ? 'true' : 'false' }});

                // reset text fields and selects
                $('#proc').val('');
                $('#notes').val('');
                $('#maps').val(null).trigger('change');
                $('#transports').val(null).trigger('change');
                if (typeof window.setRuleDevice === 'function') { setRuleDevice(); }
            } else if (MODE === 'edit' && LOAD_URL) {
                // Load existing rule data
                $.getJSON(LOAD_URL)
                    .done(function (data) { loadRule(data); })
                    .fail(function () { toastr.error('Failed to load rule'); });
            }

            // Save handler
            $('#btn-save').on('click', function (e) {
                e.preventDefault();
                var result_json = $('#builder').queryBuilder('getRules');
                if (result_json !== null && result_json.valid) {
                    $('#builder_json').val(JSON.stringify(result_json));
                    $.ajax({
                        type: SAVE_METHOD,
                        url: SAVE_URL,
                        data: $('form.alerts-form').serializeArray(),
                        dataType: 'json',
                        success: function (data) {
                            if (data.status === 'ok') {
                                toastr.success(data.message);
                                window.location.href = {{ json_encode(url('alert-rules')) }};
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

            $('#import-collection').on('click', function (e) {
                e.preventDefault();
                $("#search_rule_modal").modal('show');
            });

            $('#import-alert_rule').on('click', function (e) {
                e.preventDefault();
                $("#search_alert_rule_modal").modal('show');
            });
        });
    })();
</script>
