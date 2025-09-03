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
