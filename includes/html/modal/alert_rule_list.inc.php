<?php
/**
 * alert_rule_list.inc.php
 *
 * LibreNMS alert_rule_list modal
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2020 Thomas Berberich
 * @author     Thomas Berberich <sourcehhdoctor@gmail.com>
 */

use LibreNMS\Alerting\QueryBuilderParser;

if (! Auth::user()->hasGlobalAdmin()) {
    exit('ERROR: You need to be admin');
}

?>

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
                        <?php
                        $alert_rules = dbFetchRows('SELECT * FROM alert_rules order by name');
                        foreach ($alert_rules as $rule) {
                            if (empty($rule['builder'])) {
                                $rule_display = $rule['rule'];
                            } elseif ($rule_extra['options']['override_query'] === 'on') {
                                $rule_display = 'Custom SQL Query';
                            } else {
                                $rule_display = QueryBuilderParser::fromJson($rule['builder'])->toSql(false);
                            }
                            echo "
                                <tr>
                                    <td>{$rule['name']}</td>
                                    <td><i>" . htmlentities($rule_display) . "</i></td>
                                    <td>{$rule['severity']}</td>
                                    <td>{$rule['id']}</td>
                                </tr>
                            ";
                        }
                        ?>
                    </table>
                    <script>
                        var alert_grid = $("#alert_rule_list").bootgrid({
                            caseSensitive: false,
                            formatters: {
                                "alert_action": function (column, row) {
                                    return "<button type=\"button\" id=\"alert_rule_from_list\" name=\"alert_rule_from_list\" data-rule_id=\"" + row.alert_action + "\" class=\"btn btn-sm btn-primary alert_rule_from_list\">Select</button";
                                }
                            },
                            templates: {
                                footer: "<div id=\"{{ctx.id}}\" class=\"{{css.footer}}\"><div class=\"row\"><div class=\"col-sm-12\"><p class=\"{{css.pagination}}\"></p></div></div></div>"
                            }
                        }).on("loaded.rs.jquery.bootgrid", function()
                        {
                            alert_grid.find(".alert_rule_from_list").on("click", function(e) {
                                var alert_rule_id = $(this).data("rule_id");
                                $.ajax({
                                    type: "POST",
                                    url: "ajax_form.php",
                                    data: {type: 'sql-from-alert-rules', rule_id: alert_rule_id},
                                    dataType: "json",
                                    success: function (data) {
                                        if (data.status == 'ok') {
                                            $("#search_alert_rule_modal").one('hidden.bs.modal', function(event) {
                                                $('#create-alert').modal('show');
                                                loadRule(data);
                                            });
                                            $("#search_alert_rule_modal").modal('hide');
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
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $("#search_alert_rule_modal").on('hidden.bs.modal', function(e) {
        $("#alert_rule_id").val('');
        $("#rule_suggest").val('');
        $("#rule_display").html('');
    });
</script>
