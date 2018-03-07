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

use LibreNMS\QueryBuilderFilter;

if (is_admin()) {
    $device['device_id'] = isset($vars['device']) ? $vars['device'] : '-1';
    $filters = json_encode(new QueryBuilderFilter('alert'));

    ?>

    <div class="modal fade" id="create-alert" tabindex="-1" role="dialog"
         aria-labelledby="Create" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h5 class="modal-title" id="Create">Alert Rules</h5>
                </div>
                <div class="modal-body">

                    <form method="post" role="form" id="rules" class="form-horizontal alerts-form">
                        <input type="hidden" name="device_id" id="device_id" value="<?php echo $device['device_id']; ?>">
                        <input type="hidden" name="rule_id" id="rule_id" value="">
                        <input type="hidden" name="type" id="type" value="alert-rules">
                        <input type="hidden" name="template_id" id="template_id" value="">
                        <input type="hidden" name="query" id="query" value="">
                        <input type="hidden" name="json" id="json" value="">
                        <div class='form-group'>
                            <label for='name' class='col-sm-3 control-label'>Rule name: </label>
                            <div class='col-sm-9'>
                                <input type='text' id='name' name='name' class='form-control' maxlength='200'>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-3">
                                <div class="pull-right">
                                    <div class="dropdown">
                                        <button class="btn btn-default dropdown-toggle" type="button"
                                                id="import-from" data-toggle="dropdown" aria-haspopup="true"
                                                aria-expanded="true">
                                            Import from
                                            <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="import-from"
                                            id="import-dropdown">
                                            <li><a href="#" name="import-query" id="import-query">SQL Query</a>
                                            </li>
                                            <li><a href="#" name="import-old-format" id="import-old-format">Old
                                                    Format</a></li>
                                            <li><a href="#" name="import-collection" id="import-collection">Collection</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-9">
                                <div id="builder"></div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for='severity' class='col-sm-3 control-label'>Severity: </label>
                            <div class="col-sm-9">
                                <select name='severity' id='severity' class='form-control'>
                                    <option value='ok'>OK</option>
                                    <option value='warning'>Warning</option>
                                    <option value='critical' selected>Critical</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group form-inline">
                            <label for='count' class='col-sm-3 control-label'>Max alerts: </label>
                            <div class="col-sm-2">
                                <input type='text' id='count' name='count' class='form-control' size="4">
                            </div>
                            <div class="col-sm-3">
                                <label for='delay' class='control-label' style="vertical-align: top;">Delay: </label>
                                <input type='text' id='delay' name='delay' class='form-control' size="4">
                            </div>
                            <div class="col-sm-4">
                                <label for='interval' class='control-label align-top' style="vertical-align: top;">Interval: </label>
                                <input type='text' id='interval' name='interval' class='form-control' size="4">
                            </div>
                        </div>
                        <div class='form-group'>
                            <label for='mute' class='col-sm-3 control-label'>Mute alerts: </label>
                            <div class='col-sm-2'>
                                <input type="checkbox" name="mute" id="mute">
                            </div>
                            <label for='invert' class='col-sm-3 control-label'>Invert match: </label>
                            <div class='col-sm-2'>
                                <input type='checkbox' name='invert' id='invert'>
                            </div>
                        </div>
                        <div id="preseed-maps">
                            <div class="form-group">
                                <label for='map-stub' class='col-sm-3 control-label'>Map To: </label>
                                <div class="col-sm-4">
                                    <input type='text' id='map-stub' name='map-stub' class='form-control'/>
                                </div>
                                <div class="col-sm-1">
                                    <button class="btn btn-primary btn-sm" type="button" name="add-map"
                                            id="add-map" value="Add">Add
                                    </button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-8">
                                    <span id="map-tags"></span>
                                </div>
                            </div>
                        </div>
                        <div class='form-group'>
                            <label for='proc' class='col-sm-3 control-label'>Procedure URL: </label>
                            <div class='col-sm-5'>
                                <input type='text' id='proc' name='proc' class='form-control' maxlength='80'>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-3 col-sm-3">
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
                'not-group'
            ],

            filters: <?php echo $filters; ?>,
            operators: [
                'equal', 'not_equal', 'in', 'not_in', 'between', 'not_between', 'begins_with', 'not_begins_with', 'contains', 'not_contains', 'ends_with', 'not_ends_with', 'is_empty', 'is_not_empty', 'is_null', 'is_not_null',
                {type: 'less', nb_inputs: 1, multiple: false, apply_to: ['string', 'number', 'datetime']},
                {type: 'less_or_equal', nb_inputs: 1, multiple: false, apply_to: ['string', 'number', 'datetime']},
                {type: 'greater', nb_inputs: 1, multiple: false, apply_to: ['string', 'number', 'datetime']},
                {type: 'greater_or_equal', nb_inputs: 1, multiple: false, apply_to: ['string', 'number', 'datetime']},
                {type: 'regexp', nb_inputs: 1, multiple: false, apply_to: ['string']}
            ],
            lang: {
                operators: {
                    regexp: 'regex'
                }
            },
            sqlOperators: {
                regexp: {op: 'REGEXP'}
            },
            sqlRuleOperator: {
                'REGEXP': function (v) {
                    return {val: v, op: 'regexp'};
                }
            }
        });

        $('#btn-save').on('click', function (e) {
            e.preventDefault();
            var $builder = $('#builder');
            var result_sql = $builder.queryBuilder('getSQL', false);
            var result_json = $builder.queryBuilder('getRules');
            if (result_sql) {
                if (result_sql.sql.length) {
                    $('#query').val(result_sql.sql);
                    $('#json').val(JSON.stringify(result_json, null, 2));
                    $.ajax({
                        type: "POST",
                        url: "ajax_form.php",
                        data: $('form.alerts-form').serialize(),
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
                        error: function () {
                            toastr.error('Failed to process rule');
                        }
                    });
                }
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

        $('#create-alert').on('show.bs.modal', function(e) {
            //get data-id attribute of the clicked element
            var rule_id = $(e.relatedTarget).data('rule_id');
            $('#rule_id').val(rule_id);

            if (rule_id >= 0) {
                $.ajax({
                    type: "POST",
                    url: "ajax_form.php",
                    data: { type: "parse-alert-rule", alert_id: rule_id },
                    dataType: "json",
                    success: function (data) {
                        loadRule(data);
                    }
                });
            } else {
                // new, reset form
                $("#builder").queryBuilder("reset");
                var $severity = $('#severity');
                $severity.val($severity.find("option[selected]").val());
                $("#mute").bootstrapSwitch('state', false);
                $("#invert").bootstrapSwitch('state', false);
                $(this).find("input[type=text]").val("");
            }
        });

        function loadRule(rule) {
            $("#builder").queryBuilder("setRulesFromSQL", rule['query_builder']);
            $('#severity').val(rule['severity']).change();
            var extra = $.parseJSON(rule['extra']);
            if (extra != null) {
                $('#count').val(extra['count']);
                if ((extra['delay'] / 86400) >= 1) {
                    var delay = extra['delay'] / 86400 + ' d';
                } else if ((extra['delay'] / 3600) >= 1) {
                    var delay = extra['delay'] / 3600 + ' h';
                } else if ((extra['delay'] / 60) >= 1) {
                    var delay = extra['delay'] / 60 + ' m';
                } else {
                    var delay = extra['delay'];
                }
                $('#delay').val(delay);
                if ((extra['interval'] / 86400) >= 1) {
                    var interval = extra['interval'] / 86400 + ' d';
                } else if ((extra['interval'] / 3600) >= 1) {
                    var interval = extra['interval'] / 3600 + ' h';
                } else if ((extra['interval'] / 60) >= 1) {
                    var interval = extra['interval'] / 60 + ' m';
                } else {
                    var interval = extra['interval'];
                }
                $('#interval').val(interval);
                $("[name='mute']").bootstrapSwitch('state', extra['mute']);
                $("[name='invert']").bootstrapSwitch('state', extra['invert']);
            }
            $('#name').val(rule['name']);
            $('#proc').val(rule['proc']);
        }
    </script>
    <?php
}
