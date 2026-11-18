{{-- pick a rule from the collection or copy an existing rule into the editor --}}
@php
    $pickers = [
        'search_rule_modal' => [
            'title' => __('Alert rule collection'),
            'source' => 'template',
            'rows' => collect($collection)->map(fn ($rule, $id) => [
                'id' => $id,
                'name' => $rule['name'],
                'rule' => \LibreNMS\Alerting\QueryBuilderParser::fromJson($rule['builder'])->toSql(false),
                'severity' => $rule['severity'] ?? '',
            ]),
        ],
        'search_alert_rule_modal' => [
            'title' => __('Running Alert rules'),
            'source' => 'copy',
            'rows' => $copyableRules->map(fn ($rule) => [
                'id' => $rule->id,
                'name' => $rule->name,
                'rule' => in_array($rule->extra['options']['override_query'] ?? null, ['on', true], true) ? __('Custom SQL Query') : \LibreNMS\Alerting\QueryBuilderParser::fromJson($rule->builder)->toSql(false),
                'severity' => $rule->severity,
            ]),
        ],
    ];
@endphp
@foreach($pickers as $modalId => $picker)
    <div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-labelledby="{{ $modalId }}-title" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h5 class="modal-title" id="{{ $modalId }}-title">{{ $picker['title'] }}</h5>
                </div>
                <div class="modal-body">
                    <table class="table table-condensed table-hover alert-rule-picker" data-draft-source="{{ $picker['source'] }}">
                        <thead>
                        <tr>
                            <th data-column-id="name" data-width="200px">{{ __('Name') }}</th>
                            <th data-column-id="rule">{{ __('Rule') }}</th>
                            <th data-column-id="severity">{{ __('Severity') }}</th>
                            <th data-column-id="id" data-formatter="select" data-sortable="false"></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($picker['rows'] as $row)
                            <tr>
                                <td>{{ $row['name'] }}</td>
                                <td><i>{{ $row['rule'] }}</i></td>
                                <td>{{ $row['severity'] }}</td>
                                <td>{{ $row['id'] }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endforeach
