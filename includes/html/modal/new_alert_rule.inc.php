<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2018 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk/fa>
 * Copyright (c) 2018 Tony Murray <murraytony@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

use App\Facades\LibrenmsConfig;
use LibreNMS\Alerting\QueryBuilderFilter;

$default_severity = LibrenmsConfig::get('alert_rule.severity');
$default_default_op_step_duration = LibrenmsConfig::get('alert_rule.default_operation_step_duration', LibrenmsConfig::get('alert_rule.interval')) . 'm';
$max_alerts_cfg = (int) LibrenmsConfig::get('alert_rule.default_operation_steps_to', LibrenmsConfig::get('alert_rule.max_alerts'));
$default_operation_row_json = json_encode([
    'operation_phase' => 'problem',
    'escalation_step_from' => 1,
    'escalation_step_to' => $max_alerts_cfg === -1 ? null : max(1, $max_alerts_cfg),
    'start_in_seconds' => max(0, 60 * (int) LibrenmsConfig::get('alert_rule.default_operation_start_in', LibrenmsConfig::get('alert_rule.delay'))),
    'step_duration_seconds' => max(0, 60 * (int) LibrenmsConfig::get('alert_rule.default_operation_step_duration', LibrenmsConfig::get('alert_rule.interval'))),
    'transports' => [],
]);
$default_invert_rule_match = LibrenmsConfig::get('alert_rule.invert_rule_match');
$default_recovery_alerts = LibrenmsConfig::get('alert_rule.recovery_alerts');
$default_acknowledgement_alerts = LibrenmsConfig::get('alert_rule.acknowledgement_alerts');
$default_invert_map = LibrenmsConfig::get('alert_rule.invert_map');

    $device_id = $device['device_id'] ?? -1;
    $filters = json_encode(new QueryBuilderFilter('alert')); ?>

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
                        <?php echo csrf_field() ?>
                        <input type="hidden" name="device_id" id="device_id" value="<?php echo $device_id; ?>">
                        <input type="hidden" name="device_name" id="device_name" value="<?php echo htmlentities((string) DeviceCache::get($device_id)->displayName()); ?>">
                        <input type="hidden" name="rule_id" id="rule_id" value="">
                        <input type="hidden" name="builder_json" id="builder_json" value="">
                        <div id="operations-form-error" class="alert alert-danger" style="display: none; margin-top: 10px;"></div>
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
                                <div class="form-group form-inline">
                                    <label for="default_operation_step_duration" class="col-sm-3 col-md-2 control-label" title="Default step duration when an operation step duration is 0 (repeat interval).">Default step duration </label>
                                    <div class="col-sm-3" title="Duration suffix: s,m,h,d — used when an operation step duration is 0.">
                                        <input type="text" id="default_operation_step_duration" name="default_operation_step_duration" class="form-control" size="8" value="<?php echo htmlspecialchars($default_default_op_step_duration); ?>">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 col-md-2 control-label">Operations</label>
                                    <div class="col-sm-9 col-md-10">
                                        <p class="help-block">Configure notifications per phase (problem / recovery / update). Per-operation: <em>Start in</em> (seconds), <em>Step duration</em> (seconds, 0 = use default above), <em>Steps from</em> / <em>Steps to</em> (escalation range; leave &ldquo;Steps to&rdquo; empty for no limit). <strong>Each operation must have at least one transport or group.</strong> Leave <strong>no operations</strong> to suppress all notifications for this rule (alerts can still appear in the UI). See <a href="https://www.zabbix.com/documentation/current/en/manual/config/notifications/action/operation" target="_blank" rel="noopener">Zabbix operations</a>.</p>
                                        <input type="hidden" name="operations_json" id="operations_json" value="">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-condensed" id="operations-table">
                                                <thead>
                                                    <tr>
                                                        <th>Steps from</th>
                                                        <th>Steps to</th>
                                                        <th>Start in (s)</th>
                                                        <th>Step dur. (s)</th>
                                                        <th class="text-center" style="width: 3em;"></th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                        <button type="button" class="btn btn-default btn-sm" id="btn-add-operation">Add operation</button>
                                    </div>
                                </div>
                                <div class='form-group form-inline'>
                                    <label for='invert' class='col-sm-3 col-md-2 control-label' title="Alert when this rule doesn't match.">Invert rule match </label>
                                    <div class='col-sm-2' title="Alert when this rule doesn't match.">
                                        <input type='checkbox' name='invert' id='invert'>
                                    </div>
                                </div>
                                <div class="form-group form-inline">
                                    <label for='recovery' class='col-sm-3 col-md-2 control-label' title="Issue recovery alerts.">Recovery alerts </label>
                                    <div class='col-sm-2' title="Issue recovery alerts.">
                                        <input type='checkbox' name='recovery' id='recovery'>
                                    </div>
                                    <label for='acknowledgement' class='col-sm-3 col-md-3 control-label' title="Issue acknowledgement alerts." style="vertical-align: top;">Acknowledgement alerts </label>
                                    <div class='col-sm-2' title="Issue acknowledgement alerts.">
                                        <input type='checkbox' name='acknowledgement' id='acknowledgement'>
                                    </div>
                                </div>
                                <div class="form-group form-inline">
                                    <label for='maps' class='col-sm-3 col-md-2 control-label' title="Restricts this alert rule to the selected devices, groups and locations.">Match devices, groups and locations list </label>
                                    <div class="col-sm-7" style="width: 56%;">
                                        <select id="maps" name="maps[]" class="form-control" multiple="multiple"></select>
                                    </div>
                                    <div>
                                        <label for='invert_map' class='col-md-1' style="width: 14.1333%;" text-align="left" title="If ON, alert rule check will run on all devices except the selected devices and groups.">All devices except in list </label>
                                        <input type='checkbox' name='invert_map' id='invert_map'>
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
                                        <input type='checkbox' name='override_query' id='override_query'>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="adv_query" class="col-sm-3 col-md-2 control-label">Query</label>
                                    <div class="col-sm-9 col-md-10">
                                        <textarea class="form-control code" rows="6" name="adv_query" id='adv_query' style="font-family: Menlo, Monaco, Consolas, 'Courier New', monospace;";></textarea>
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

    <script src="js/sql-parser.min.js"></script>
    <script src="js/query-builder.standalone.min.js"></script>
    <script src="js/interact.min.js"></script>
    <script>
        $('#builder').on('afterApplyRuleFlags.queryBuilder afterCreateRuleFilters.queryBuilder', function () {
            $("[name$='_filter']").each(function () {
                $(this).select2({
                    dropdownParent: $("#create-alert"),
                    dropdownAutoWidth : true,
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
                // 'not-group'
            ],

            filters: <?php echo $filters; ?>,
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
                'REGEXP': function (v) {
                    return {val: v, op: 'regexp'};
                },
                'NOT REGEXP': function (v) {
                    return {val: v, op: 'not_regexp'};
                }
            }
        });

        var DEFAULT_OPERATION_ROW = <?php echo $default_operation_row_json . "\n"; ?>;

        function secondsToDurationInput(sec) {
            sec = parseInt(sec, 10) || 0;
            if (sec >= 86400) {
                return (sec / 86400) + 'd';
            }
            if (sec >= 3600) {
                return (sec / 3600) + 'h';
            }
            if (sec >= 60) {
                return (sec / 60) + 'm';
            }
            return String(sec);
        }

        function initOperationTransportSelect($sel) {
            $sel.select2({
                width: '100%',
                placeholder: 'Transport / group',
                allowClear: true,
                ajax: {
                    url: '<?php echo route('ajax.select.alert-transports-groups') ?>',
                    delay: 150
                },
                dropdownParent: $('#create-alert')
            });
        }

        function addOperationRow(op) {
            op = $.extend({}, DEFAULT_OPERATION_ROW, op || {});
            var toVal = op.escalation_step_to === null || typeof op.escalation_step_to === 'undefined' ? '' : String(op.escalation_step_to);
            var $tbody = $('<tbody class="operation-group"></tbody>');
            var $trMain = $('<tr class="operation-row-main"></tr>');
            $trMain.append('<td><input type="number" class="form-control input-sm op-from" min="1" value="' + (op.escalation_step_from || 1) + '"></td>');
            $trMain.append('<td><input type="number" class="form-control input-sm op-to" min="1" placeholder="∞" value="' + toVal + '"></td>');
            $trMain.append('<td><input type="number" class="form-control input-sm op-start" min="0" value="' + (op.start_in_seconds || 0) + '"></td>');
            $trMain.append('<td><input type="number" class="form-control input-sm op-step-dur" min="0" value="' + (op.step_duration_seconds || 0) + '"></td>');
            $trMain.append('<td class="text-center"><button type="button" class="btn btn-danger btn-xs btn-remove-operation" title="Remove operation">&times;</button></td>');
            var $trTrans = $('<tr class="operation-row-transports"></tr>');
            $trTrans.append(
                '<td colspan="6" class="operation-transports-cell" style="border-top: none; padding-top: 4px; padding-bottom: 10px;">' +
                '<label class="control-label" style="display: block; margin-bottom: 4px; font-weight: 600; font-size: 12px;">Transports / groups</label>' +
                '<select class="form-control input-sm op-transports" multiple="multiple"></select>' +
                '</td>'
            );
            $tbody.append($trMain, $trTrans);
            $('#operations-table').append($tbody);
            $tbody.find('.op-phase').val(op.operation_phase || 'problem');
            initOperationTransportSelect($tbody.find('.op-transports'));
            if (op.transports && op.transports.length) {
                $.each(op.transports, function (i, t) {
                    var opt = new Option(t.text, t.id, true, true);
                    $tbody.find('.op-transports').append(opt);
                });
                $tbody.find('.op-transports').trigger('change');
            }
        }

        /**
         * @returns {string|null} Error message or null if OK. Empty table (no operations) is allowed (notifications suppressed).
         */
        function validateOperationsTransports() {
            var groups = $('#operations-table tbody.operation-group');
            if (groups.length === 0) {
                return null;
            }
            var missing = false;
            groups.each(function () {
                var v = $(this).find('.op-transports').val();
                if (!v || !v.length) {
                    missing = true;
                    return false;
                }
            });
            return missing
                ? 'Each operation must have at least one transport or transport group selected.'
                : null;
        }

        function buildOperationsJson() {
            var ops = [];
            $('#operations-table tbody.operation-group').each(function () {
                var $grp = $(this);
                var $tr = $grp.find('tr.operation-row-main');
                var toRaw = $tr.find('.op-to').val();
                ops.push({
                    operation_phase: $tr.find('.op-phase').val(),
                    escalation_step_from: parseInt($tr.find('.op-from').val(), 10) || 1,
                    escalation_step_to: (toRaw === '' || toRaw === null) ? null : parseInt(toRaw, 10),
                    start_in_seconds: parseInt($tr.find('.op-start').val(), 10) || 0,
                    step_duration_seconds: parseInt($tr.find('.op-step-dur').val(), 10) || 0,
                    transports: $grp.find('.op-transports').val() || []
                });
            });
            $('#operations_json').val(JSON.stringify(ops));
        }

        $('#btn-add-operation').on('click', function () {
            addOperationRow(null);
        });

        $('#operations-table').on('click', '.btn-remove-operation', function () {
            $(this).closest('tbody.operation-group').remove();
        });

        $('#btn-save').on('click', function (e) {
            e.preventDefault();

            var url = '<?php echo route('alert-rule.store') ?>';
            var method = 'POST';
            var rule_id = $('#rule_id').val();
            if  (rule_id) {
                url = '<?php echo route('alert-rule.update', ':alert_id') ?>'.replace(':alert_id', rule_id);
                method = 'PUT';
            }
            var result_json = $('#builder').queryBuilder('getRules');

            // Clear any previous inline form error
            $('#operations-form-error').hide().text('');

            if (result_json !== null && result_json.valid) {
                var opErr = validateOperationsTransports();
                if (opErr) {
                    $('#operations-form-error').text(opErr).show();
                    return;
                }
                $('#builder_json').val(JSON.stringify(result_json));
                buildOperationsJson();
                $.ajax({
                    type: method,
                    url: url,
                    data: $('form.alerts-form').serializeArray(),
                    dataType: "json",
                    success: function (data) {
                        if (data.status == 'ok') {
                            toastr.success(data.message);
                            $('#create-alert').modal('hide');
                            window.location.reload(); // FIXME: reload table
                        } else {
                            toastr.error(data.message);
                        }
                    },
                    error: function (xhr) {
                        var msg = 'Request failed';
                        if (xhr.responseJSON) {
                            if (xhr.responseJSON.errors && xhr.responseJSON.errors.operations_json && xhr.responseJSON.errors.operations_json[0]) {
                                msg = xhr.responseJSON.errors.operations_json[0];
                            } else if (xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                        }

                        $('#operations-form-error').text(msg).show();
                    }
                });
            }
        });
        $('#import-query').on('click', function (e) {
            e.preventDefault();
            var sql_import = window.prompt("Enter your SQL query:");
            if (sql_import) {
                try {
                    $("#builder").queryBuilder("setRulesFromSQL", sql_import);
                } catch (e) {
                    alert('Your query could not be parsed');
                }
            }
        });

        $('#import-old-format').on('click', function (e) {
            e.preventDefault();
            var old_import = window.prompt("Enter your old alert rule:");
            if (old_import) {
                try {
                    old_import = old_import.replace(/&&/g, 'AND');
                    old_import = old_import.replace(/\|\|/g, 'OR');
                    old_import = old_import.replace(/%/g, '');
                    old_import = old_import.replace(/"/g, "'");
                    old_import = old_import.replace(/~/g, "REGEXP");
                    old_import = old_import.replace(/@/g, ".*");
                    $("#builder").queryBuilder("setRulesFromSQL", old_import);
                } catch (e) {
                    alert('Your query could not be parsed');
                }
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

        $('#create-alert').on('show.bs.modal', function(e) {
            //get data-id attribute of the clicked element
            var rule_id = $(e.relatedTarget).data('rule_id');
            $('#rule_id').val(rule_id);

            if (rule_id >= 0) {
                $.ajax({
                    type: "GET",
                    url: "<?php echo route('alert-rule.show', ':alert_id') ?>".replace(':alert_id', rule_id),
                    success: function (data) {
                        loadRule(data);
                    }
                });
            } else {
                // new, reset form
                $("#builder").queryBuilder("reset");
                var $severity = $('#severity');
                $severity.val($severity.find("option[selected]").val());
                $("#invert").bootstrapSwitch('state', <?=$default_invert_rule_match?>);
                $("#recovery").bootstrapSwitch('state', <?=$default_recovery_alerts?>);
                $("#acknowledgement").bootstrapSwitch('state', <?=$default_acknowledgement_alerts?>);
                $("#override_query").bootstrapSwitch('state', false);
                $("#invert_map").bootstrapSwitch('state', <?=$default_invert_map?>);
                $(this).find("input[type=text]").val("");
                $('#default_operation_step_duration').val('<?php echo htmlspecialchars($default_default_op_step_duration); ?>');
                $('#adv_query').val('');
                $('#notes').val('');
                $('#severity').val('<?=$default_severity?>');

                var $maps = $('#maps');
                $maps.empty();
                $maps.val(null).trigger('change');
                setRuleDevice();// pre-populate device in the maps if this is a per-device rule

                $('#operations-table tbody.operation-group').remove();
            }
        });

        function loadRule(rule) {
            $('#rule_name').val(rule.name);
            $('#proc').val(rule.proc);
            $('#builder').queryBuilder("setRules", rule.builder);
            $('#severity').val(rule.severity).trigger('change');
            $('#adv_query').val(rule.adv_query);
            $('#notes').val(rule.notes);

            if (rule.default_operation_step_duration_seconds != null) {
                $('#default_operation_step_duration').val(secondsToDurationInput(rule.default_operation_step_duration_seconds));
            }

            $('#operations-table tbody.operation-group').remove();
            if (rule.operations && rule.operations.length) {
                $.each(rule.operations, function (i, op) {
                    addOperationRow(op);
                });
            }

            var $maps = $('#maps');
            $maps.empty();
            $maps.val(null).trigger('change'); // clear
            if (rule.maps == null) {
                // collection rule
                setRuleDevice()
            } else {
                $.each(rule.maps, function(index, value) {
                    var option = new Option(value.text, value.id, true, true);
                    $maps.append(option).trigger('change')
                });
            }

            if (rule.extra != null) {
                var extra = rule.extra;
                if (extra.adv_query) {
                    $('#adv_query').val(extra.adv_query);
                }
                $("[name='invert']").bootstrapSwitch('state', extra.invert);
                if (typeof extra.recovery == 'undefined') {
                    extra.recovery = '<?=$default_recovery_alerts?>';
                }
                if (typeof extra.acknowledgement == 'undefined') {
                    extra.acknowledgement = '<?=$default_acknowledgement_alerts?>';
                }

                if (typeof extra.options == 'undefined') {
                    extra.options = new Array();
                }
                if (typeof extra.options.override_query == 'undefined') {
                    extra.options.override_query = false;
                }
                $("[name='recovery']").bootstrapSwitch('state', extra.recovery);
                $("[name='acknowledgement']").bootstrapSwitch('state', extra.acknowledgement);

                if (rule.invert_map == 1) {
                    $("[name='invert_map']").bootstrapSwitch('state', true);
                }else{
                    $("[name='invert_map']").bootstrapSwitch('state', false);
                }

                $("[name='override_query']").bootstrapSwitch('state', extra.options.override_query);
            }
        }

        function setRuleDevice() {
            // pre-populate device in the maps if this is a per-device rule
            var device_id = $('#device_id').val();
            if (device_id > 0) {
                var device_name = $('#device_name').val();
                var option = new Option(device_name, device_id, true, true);
                $('#maps').append(option).trigger('change')
            }
        }

        $("#maps").select2({
            width: '100%',
            placeholder: "Devices, Groups or Locations",
            ajax: {
                url: '<?php echo route('ajax.select.devices-groups-locations') ?>',
                delay: 150
            }
        });
    </script>
