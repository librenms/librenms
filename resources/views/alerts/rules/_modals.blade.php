{{-- Shared modals for importing/searching alert rules and collections --}}
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

<script>
    document.addEventListener("DOMContentLoaded", () => {
        var grid = $("#rule_collection").bootgrid({
            caseSensitive: false,
            formatters: {
                "action": function (column, row) {
                    return "<button type=\"button\" data-rule_id=\"" + row.action + "\" class=\"btn btn-sm btn-primary rule_from_collection\">" + @json(__('alerting.rules.messages.select')) +"<\/button>";
                }
            }
        }).on("loaded.rs.jquery.bootgrid", function () {
            grid.find(".rule_from_collection").on("click", function () {
                var template_rule_id = $(this).data("rule_id");
                $.getJSON('{{ route('alert-rule-template.show', ':template_id') }}'.replace(':template_id', template_rule_id))
                    .done(function (data) {
                        if (data.status === 'ok') {
                            $("#search_rule_modal").modal('hide');
                            loadRule(data);
                        } else {
                            toastr.error(data.message || @json(__('alerting.rules.messages.failed_load_template')));
                        }
                    })
                    .fail(function () {
                        toastr.error(@json(__('alerting.rules.messages.failed_process_template')));
                    });
            }).end();
        });

        var alert_grid = $("#alert_rule_list").bootgrid({
            caseSensitive: false,
            formatters: {
                "alert_action": function (column, row) {
                    return "<button type=\"button\" data-rule_id=\"" + row.alert_action + "\" class=\"btn btn-sm btn-primary alert_rule_from_list\">" + @json(__('alerting.rules.messages.select')) +"<\/button>";
                }
            },
            templates: {footer: "<div id=\"@{{ctx.id}}\" class=\"@{{css.footer}}\"><div class=\"row\"><div class=\"col-sm-12\"><p class=\"@{{css.pagination}}\"></p></div></div></div>"}
        }).on("loaded.rs.jquery.bootgrid", function () {
            alert_grid.find(".alert_rule_from_list").on("click", function () {
                var alert_rule_id = $(this).data("rule_id");
                $.getJSON('{{ route('alert-rule-template.rule', ':rule_id') }}'.replace(':rule_id', alert_rule_id))
                    .done(function (data) {
                        if (data.status === 'ok') {
                            $("#search_alert_rule_modal").modal('hide');
                            loadRule(data);
                        } else {
                            toastr.error(data.message || @json(__('alerting.rules.messages.failed_load_rule')));
                        }
                    })
                    .fail(function () {
                        toastr.error(@json(__('alerting.rules.messages.failed_process_template')));
                    });
            }).end();
        });
    });
</script>
