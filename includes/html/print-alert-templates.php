<?php

use App\Models\AlertRule;
use App\Models\AlertTemplate;
use App\Models\AlertTemplateMap;

$no_refresh = true;

require_once 'includes/html/modal/alert_template.inc.php';
require_once 'includes/html/modal/delete_alert_template.inc.php';
?>
<div class="table-responsive">
    <table id="templatetable" class="table table-hover table-condensed" width="100%">
      <thead>
          <tr>
            <th data-column-id="id" data-searchable="false" data-identifier="true" data-type="numeric">#</th>
            <th data-column-id="templatename">Name</th>
            <th data-column-id="alert_rules" data-searchable="false" data-formatter="alert_rules">Alert Rules</th>
            <th data-column-id="actions" data-searchable="false" data-formatter="commands">Action</th>
            <th data-column-id="old_template" data-searchable="false" data-visible="false">Old template</th>
          </tr>
      </thead>
      <tbody>
<?php
$full_query = AlertTemplate::select('id', 'name', 'template')->get();

foreach ($full_query as $template) {
    $single_template = ['name' => $template->name,
        'template' => $template->template,
    ];

    if ($template->name == 'Default Alert Template') {
        $default_tplid = $template->id;
        $single_template['id'] = 0;
        $single_template['alert_rules'] = AlertRule::whereNotIn('id', AlertTemplateMap::pluck('alert_rule_id'))
                                                     ->select('id', 'name')
                                                     ->orderBy('name')
                                                     ->get();
    } else {
        $single_template['id'] = $template->id;
        $single_template['alert_rules'] = $template->alert_rules;
    }

    $templates[] = $single_template;
}

$template_ids = array_column($templates, 'id');
array_multisort($templates, SORT_ASC, $template_ids);
foreach ($templates as $template) {
    $old_template = strpos($template['template'], '{/if}') !== false ? '1' : '';
    echo '<tr data-row-id="' . $template['id'] . '">
            <td>' . $template['id'] . '</td>
            <td>' . $template['name'] . '</td>
            <td>' . json_encode($template['alert_rules']) . '</td>
            <td>' . $old_template . '</td>
          </tr>';
}

?>
        </tbody>
    </table>
</div>
<script>
$(document).ready(function() {
    var grid = $('#templatetable').bootgrid({
        rowCount: [50, 100, 250, -1],
        templates: {
        header: '<div id="{{ctx.id}}" class="{{css.header}}"> \
                    <div class="row"> \
<?php if (Auth::user()->hasGlobalAdmin()) { ?>
                        <div class="col-sm-8 actionBar"> \
                            <span class="pull-left"> \
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#alert-template" data-template_id="">Create new alert template</button> \
                            </span> \
                        </div> \
                <div class="col-sm-4 actionBar"><p class="{{css.search}}"></p><p class="{{css.actions}}"></p></div></div></div>'
<?php } else { ?>
                <div class="actionBar"><p class="{{css.search}}"></p><p class="{{css.actions}}"></p></div></div></div>'

<?php } ?>
        },
        formatters: {
            "commands": function(column, row) {
                var response = '';
                //FIXME remove Deprecated template
                if (row.old_template == "1") {
                    response = "<button type='button' class='btn btn-xs btn-warning' data-content=' class='btn btn-xs btn-warning' data-content='><i class='fa fa-exclamation-triangle' title='This is a legacy template and needs converting, please edit this template and click convert then save'><i class='fa fa-exclamation-triangle'></i></button> ";
                }
                if(row.id == 0) {
                    response = response + "<button type=\"button\" class=\"btn btn-xs btn-primary command-edit\" data-toggle='modal' data-target='#alert-template' data-template_id=\"" + row.id + "\" data-template_action='edit' name='edit-alert-template'><i class=\"fa fa-pencil\" aria-hidden=\"true\"></i></button> " + "<button type=\"button\" class=\"btn btn-xs btn-danger command-delete\" disabled=\"disabled\"><i class=\"fa fa-trash-o\" aria-hidden=\"true\"></i></button>";
                } else {
                    response = response + "<button type=\"button\" class=\"btn btn-xs btn-primary command-edit\" data-toggle='modal' data-target='#alert-template' data-template_id=\"" + row.id + "\" data-template_action='edit' name='edit-alert-template'><i class=\"fa fa-pencil\" aria-hidden=\"true\"></i></button> " + "<button type=\"button\" class=\"btn btn-xs btn-danger command-delete\" data-toggle=\"modal\" data-target='#confirm-delete-alert-template' data-template_id=\"" + row.id + "\" name='delete-alert-template'><i class=\"fa fa-trash-o\" aria-hidden=\"true\"></i></button>";
                }
                return response;
            },
            "alert_rules": function(column, row) {
                var response = '';
                alert_rules = JSON.parse(row.alert_rules);
                $.each(alert_rules, function(_, alert_rule) {
                    response = response + alert_rule.name + '<br>';
                });
                return response;
            },
        },
    }).on("loaded.rs.jquery.bootgrid", function() {
        /* Executes after data is loaded and rendered */
        grid.find(".command-edit").on("click", function(e) {
            var localtmpl_id = $(this).data("template_id");
            if(localtmpl_id == 0) {
                $('#default_template').val("1");
                $('#template_id').val(<?=$default_tplid?>);
            } else {
                $('#default_template').val("0");
                $('#template_id').val(localtmpl_id);
            }
            $("#alert-template").modal('show');
        }).end().find(".command-delete").on("click", function(e) {
            $('#template_id').val($(this).data("template_id"));
            $('#confirm-delete-alert-template').modal('show');
        });
    });
});
</script>
