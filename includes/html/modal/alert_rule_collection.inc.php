<?php
/**
 * search_rule_collection.inc.php
 *
 * LibreNMS search_rule_collection modal
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
 * @copyright  2016 Neil Lathwood
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */

use LibreNMS\Alerting\QueryBuilderParser;

if (! Auth::user()->hasGlobalAdmin()) {
    exit('ERROR: You need to be admin');
}

?>

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
                        <?php
                        $tmp_rule_id = 0;
                        foreach (get_rules_from_json() as $rule) {
                            $rule['rule_id'] = $tmp_rule_id;
                            echo "
                                <tr>
                                    <td>{$rule['name']}</td>
                                    <td>";
                            echo ! empty($rule['builder']) ? QueryBuilderParser::fromJson($rule['builder'])->toSql(false) : $rule['rule'];
                            echo "  </td>
                                    <td>{$rule['rule_id']}</td>
                                </tr>
                            ";
                            $tmp_rule_id++;
                        }
                        ?>
                    </table>
                    <script>
                        var grid = $("#rule_collection").bootgrid({
                            caseSensitive: false,
                            formatters: {
                                "action": function (column, row) {
                                    return "<button type=\"button\" id=\"rule_from_collection\" name=\"rule_from_collection\" data-rule_id=\"" + row.action + "\" class=\"btn btn-sm btn-primary rule_from_collection\">Select</button";
                                }
                            },
                            templates: {
                                footer: "<div id=\"{{ctx.id}}\" class=\"{{css.footer}}\"><div class=\"row\"><div class=\"col-sm-12\"><p class=\"{{css.pagination}}\"></p></div></div></div>"
                            }
                        }).on("loaded.rs.jquery.bootgrid", function()
                        {
                            grid.find(".rule_from_collection").on("click", function(e) {
                                var template_rule_id = $(this).data("rule_id");
                                $.ajax({
                                    type: "POST",
                                    url: "ajax_form.php",
                                    data: {type: 'sql-from-alert-collection', template_id: template_rule_id},
                                    dataType: "json",
                                    success: function (data) {
                                        if (data.status == 'ok') {
                                            $("#search_rule_modal").one('hidden.bs.modal', function(event) {
                                                $('#create-alert').modal('show');
                                                loadRule(data);
                                            });
                                            $("#search_rule_modal").modal('hide');
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
    $("#search_rule_modal").on('hidden.bs.modal', function(e) {
        $("#template_rule_id").val('');
        $("#rule_suggest").val('');
        $("#rule_display").html('');
    });
</script>
