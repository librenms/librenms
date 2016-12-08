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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2016 Neil Lathwood
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */

if (is_admin() === false) {
    die('ERROR: You need to be admin');
}

?>

<div class="modal fade" id="search_rule_modal" tabindex="-1" role="dialog" aria-labelledby="search_rule" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h5 class="modal-title" id="search_rule">Search alert rule collection</h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-8">
                        <input type="hidden" name="template_rule_id" id="template_rule_id" value="">
                        <input type="text" id="rule_suggest" name="rule_suggest" class="form-control" placeholder="Start typing..."/>
                    </div>
                    <div class="col-md-2">
                        <input type="submit" id="rule_from_collection" name="rule_from_collection" value="Create" class="btn btn-sm btn-primary">
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-2">
                        Rule:
                    </div>
                    <div class="col-md-8">
                        <div id="rule_display"></div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    var rule_suggestions = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('name'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        remote: {
            url: "ajax_rulesuggest.php?type=alert_rule_collection&term=%QUERY",
            filter: function (output) {
                return $.map(output, function (item) {
                    return {
                        name: item.name,
                        id: item.id,
                        rule: item.rule,
                    };
                });
            },
            wildcard: "%QUERY"
        }
    });
    rule_suggestions.initialize();
    $('#rule_suggest').typeahead({
            hint: true,
            highlight: true,
            minLength: 1,
            classNames: {
                menu: 'typeahead-left'
            }
        },
        {
            source: rule_suggestions.ttAdapter(),
            async: true,
            displayKey: 'name',
            valueKey: 'id',
            templates: {
                suggestion: Handlebars.compile('<p>&nbsp;{{name}}</p>')
            },
            limit: 20
        });

    $("#rule_suggest").on("typeahead:selected typeahead:autocompleted", function(e,datum) {
        $("#template_rule_id").val(datum.id);
        $("#rule_display").html('<mark>' + datum.rule + '</mark>');
    });

    $("#rule_from_collection").click('', function(e) {
        e.preventDefault();
        $("#template_id").val($("#template_rule_id").val());
        $("#search_rule_modal").modal('hide');
        $("#create-alert").modal('show');
    });

    $("#search_rule_modal").on('hidden.bs.modal', function(e) {
        $("#template_rule_id").val('');
        $("#rule_suggest").val('');
        $("#rule_display").html('');
    });

</script>
