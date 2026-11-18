@extends('layouts.librenmsv1')

@php
    $title = $rule->exists ? __('Edit Alert Rule') : __('Create Alert Rule');
    $action = $rule->exists ? route('alert-rule.update', $rule) : route('alert-rule.store');
    $extra = $draft['extra'] ?? [];
    $advanced = $errors->has('adv_query');
    $oldBuilder = old('builder_json');
    $config = [
        'filters' => new \LibreNMS\Alerting\QueryBuilderFilter('alert'),
        'builder' => is_string($oldBuilder) ? json_decode($oldBuilder, true) : ($draft['builder'] ?? []),
        'createUrl' => route('alert-rule.create', ['device' => $device?->device_id]),
        'openCollection' => $openCollection,
        'messages' => [
            'parseFailed' => __('Your query could not be parsed'),
            'sqlPrompt' => __('Enter your SQL query:'),
            'legacyPrompt' => __('Enter your old alert rule:'),
            'loadFailed' => __('The saved builder rules could not be loaded'),
            'select' => __('Select'),
        ],
    ];
@endphp

@section('title', $title)

@section('content')
    <div class="container" data-alert-rule-editor>
        <x-panel>
            <x-slot:heading>
                <h3 class="panel-title">
                    <a href="{{ $backUrl }}" class="tw:mr-2 tw:text-inherit" title="{{ __('Back to Alert Rules') }}" aria-label="{{ __('Back to Alert Rules') }}">
                        <i class="fa fa-arrow-left fa-fw" aria-hidden="true"></i>
                    </a>
                    {{ $title }}@if($rule->exists) – {{ $rule->name }}@endif
                </h3>
            </x-slot:heading>

            @if($errors->any())
                <div class="alert alert-danger" role="alert">
                    <ul class="tw:mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div id="alert-rule-form-error" class="alert alert-danger" role="alert" hidden></div>

            <ul class="nav nav-tabs tw:mb-4" role="tablist">
                <li role="presentation" @class(['active' => ! $advanced])>
                    <a href="#alert-rule-main" role="tab" data-toggle="tab">{{ __('Main') }}</a>
                </li>
                <li role="presentation" @class(['active' => $advanced])>
                    <a href="#alert-rule-advanced" role="tab" data-toggle="tab">{{ __('Advanced') }}</a>
                </li>
            </ul>

            <form id="alert-rule-form" method="post" action="{{ $action }}" class="form-horizontal">
                @csrf
                @if($rule->exists)
                    @method('PUT')
                @endif
                @if($device)
                    <input type="hidden" name="device" value="{{ $device->device_id }}">
                @endif
                @if(isset($draft['template_id']))
                    <input type="hidden" name="template_id" value="{{ $draft['template_id'] }}">
                @endif
                <input type="hidden" name="builder_json" id="builder_json" value="[]">

                <div class="tab-content">
                    <div id="alert-rule-main" role="tabpanel" @class(['tab-pane', 'active' => ! $advanced])>
                        <div class="form-group @error('name') has-error @enderror">
                            <label for="rule_name" class="col-sm-3 col-md-2 control-label">{{ __('Rule name') }}</label>
                            <div class="col-sm-9 col-md-10">
                                <input type="text" id="rule_name" name="name" class="form-control" maxlength="200" value="{{ old('name', $draft['name'] ?? '') }}" required>
                            </div>
                        </div>

                        <div class="form-group @error('builder_json') has-error @enderror">
                            <div class="col-sm-3 col-md-2">
                                <div class="dropdown">
                                    <button class="btn btn-default dropdown-toggle" type="button" id="import-from" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        {{ __('Import from') }} <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="import-from">
                                        <li><a href="#" id="import-query">{{ __('SQL Query') }}</a></li>
                                        <li><a href="#" id="import-old-format">{{ __('Old Format') }}</a></li>
                                        @can('create', \App\Models\AlertRule::class)
                                            <li><a href="#search_rule_modal" data-toggle="modal">{{ __('Collection') }}</a></li>
                                            <li><a href="#search_alert_rule_modal" data-toggle="modal">{{ __('Alert Rule') }}</a></li>
                                        @endcan
                                    </ul>
                                </div>
                            </div>
                            <div class="col-sm-9 col-md-10">
                                <div id="builder"></div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="severity" class="col-sm-3 col-md-2 control-label">{{ __('Severity') }}</label>
                            <div class="col-sm-4 col-md-3">
                                <select name="severity" id="severity" class="form-control">
                                    @foreach(['ok' => 'OK', 'warning' => __('Warning'), 'critical' => __('Critical')] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('severity', $draft['severity'] ?? LibrenmsConfig::get('alert_rule.severity')) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        @php
                            $toggles = [
                                'invert' => [__('Invert rule match'), $extra['invert'] ?? LibrenmsConfig::get('alert_rule.invert_rule_match'), __('Alert when this rule does not match.')],
                                'mute' => [__('Mute alerts'), $extra['mute'] ?? false, __('Suppress all notifications for this rule. Alerts still appear in the Web UI.')],
                                'recovery' => [__('Recovery alerts'), $extra['recovery'] ?? LibrenmsConfig::get('alert_rule.recovery_alerts'), __('Issue recovery alerts.')],
                                'acknowledgement' => [__('Acknowledgement alerts'), $extra['acknowledgement'] ?? LibrenmsConfig::get('alert_rule.acknowledgement_alerts'), __('Issue acknowledgement alerts.')],
                                'notify_per_entity' => [__('Notify per matching entity'), $draft['notify_per_entity'] ?? false, __('Send a separate notification per matching entity instead of one grouped notification.')],
                            ];
                        @endphp
                        @foreach($toggles as $field => [$label, $checked, $help])
                            <div class="form-group" title="{{ $help }}">
                                <label for="{{ $field }}" class="col-sm-3 col-md-2 control-label">{{ $label }}</label>
                                <div class="col-sm-9 col-md-10 tw:pt-1">
                                    <x-toggle :name="$field" :id="$field" :checked="(bool) old($field, $checked)" />
                                </div>
                            </div>
                        @endforeach

                        <div class="form-group" title="{{ __('When notifying per entity, show and notify separately up to this many matching entities per device. Above the limit, faults are grouped.') }}">
                            <label for="max_entities" class="col-sm-3 col-md-2 control-label">{{ __('Max entities per device') }}</label>
                            <div class="col-sm-4 col-md-3">
                                <input type="number" name="max_entities" id="max_entities" class="form-control" min="1" value="{{ old('max_entities', $draft['max_entities'] ?? max(1, (int) LibrenmsConfig::get('alert_rule.default_max_entities', 10))) }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="alert_operation_id" class="col-sm-3 col-md-2 control-label">{{ __('Operation') }}</label>
                            <div class="col-sm-9 col-md-10">
                                <select name="alert_operation_id" id="alert_operation_id" class="form-control"
                                        data-url="{{ route('ajax.select.alert-operation') }}" data-placeholder="{{ __('None (suppress notifications)') }}">
                                    <option value=""></option>
                                    @if($operationId)
                                        <option value="{{ $operationId }}" selected>{{ $operationName }}</option>
                                    @endif
                                </select>
                                <p class="help-block">{{ __('Leave empty to suppress all notifications for this rule.') }}</p>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="maps" class="col-sm-3 col-md-2 control-label">{{ __('Match devices, groups and locations list') }}</label>
                            <div class="col-sm-9 col-md-10">
                                <select id="maps" name="maps[]" class="form-control" multiple
                                        data-url="{{ route('ajax.select.devices-groups-locations') }}" data-placeholder="{{ __('Devices, Groups or Locations') }}">
                                    @foreach($maps as $map)
                                        <option value="{{ $map['id'] }}" selected>{{ $map['text'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group" title="{{ __('Run this rule on all devices except the selected devices, groups and locations.') }}">
                            <label for="invert_map" class="col-sm-3 col-md-2 control-label">{{ __('All devices except in list') }}</label>
                            <div class="col-sm-9 col-md-10 tw:pt-1">
                                <x-toggle name="invert_map" id="invert_map" :checked="(bool) old('invert_map', $draft['invert_map'] ?? LibrenmsConfig::get('alert_rule.invert_map'))" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="proc" class="col-sm-3 col-md-2 control-label">{{ __('Procedure URL') }}</label>
                            <div class="col-sm-9 col-md-10">
                                <input type="text" id="proc" name="proc" class="form-control" pattern="(http|https)://.*" maxlength="80" value="{{ old('proc', $draft['proc'] ?? '') }}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="notes" class="col-sm-3 col-md-2 control-label">{{ __('Notes') }}</label>
                            <div class="col-sm-9 col-md-10">
                                <textarea class="form-control" id="notes" rows="6" name="notes">{{ old('notes', $draft['notes'] ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div id="alert-rule-advanced" role="tabpanel" @class(['tab-pane', 'active' => $advanced])>
                        <div class="form-group">
                            <label for="override_query" class="col-sm-3 col-md-2 control-label">{{ __('Override SQL') }}</label>
                            <div class="col-sm-9 col-md-10 tw:pt-1">
                                <x-toggle name="override_query" id="override_query" :checked="(bool) old('override_query', $extra['options']['override_query'] ?? false)" />
                            </div>
                        </div>
                        <div class="form-group @error('adv_query') has-error @enderror">
                            <label for="adv_query" class="col-sm-3 col-md-2 control-label">{{ __('Query') }}</label>
                            <div class="col-sm-9 col-md-10">
                                <textarea class="form-control tw:font-mono" id="adv_query" rows="6" name="adv_query">{{ old('adv_query', $draft['adv_query'] ?? $extra['adv_query'] ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <x-slot:footer class="tw:flex tw:items-center tw:gap-2">
                <a href="https://docs.librenms.org/Alerting/" target="_blank" rel="noopener">
                    <i class="fa fa-book" aria-hidden="true"></i> {{ __('Docs') }}
                </a>
                <a class="btn btn-default tw:ml-auto" href="{{ $backUrl }}">{{ __('Cancel') }}</a>
                <button type="submit" class="btn btn-primary" id="btn-save" form="alert-rule-form" disabled>{{ __('Save Rule') }}</button>
            </x-slot:footer>
        </x-panel>
    </div>

    @can('create', \App\Models\AlertRule::class)
        @include('alert-rules.pickers')
    @endcan
@endsection

@push('scripts')
<script src="{{ asset('js/sql-parser.min.js') }}"></script>
<script src="{{ asset('js/query-builder.standalone.min.js') }}"></script>
<script src="{{ asset('js/interact.min.js') }}"></script>
<script>
(() => {
    const $ = window.jQuery;
    const { filters, builder, createUrl, openCollection, messages } = @js($config);
    const $editor = $('[data-alert-rule-editor]');
    const field = (selector) => $editor.find(selector);
    const $saveRule = field('#btn-save');
    let savingRule = false;

    field('#builder').on('afterApplyRuleFlags.queryBuilder afterCreateRuleFilters.queryBuilder', function () {
        field("[name$='_filter']").each(function () {
            $(this).select2({
                dropdownAutoWidth: true,
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
        ],
        filters: filters,
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
            'REGEXP': (v) => ({val: v, op: 'regexp'}),
            'NOT REGEXP': (v) => ({val: v, op: 'not_regexp'})
        }
    });

    if (builder?.rules?.length || builder?.length) {
        try {
            field('#builder').queryBuilder('setRules', builder);
        } catch {
            field('#builder').queryBuilder('reset');
            field('#alert-rule-form-error').text(messages.loadFailed).prop('hidden', false);
        }
    }

    field('#alert-rule-form').on('submit', (event) => {
        if (savingRule) {
            event.preventDefault();
            return;
        }

        const override = field('#override_query').prop('checked');
        const rules = field('#builder').queryBuilder('getRules');
        if (! override && ! rules?.valid) {
            event.preventDefault();
            field('a[href="#alert-rule-main"]').tab('show');
            return;
        }

        field('#builder_json').val(JSON.stringify(rules ?? []));
        savingRule = true;
        $saveRule.prop('disabled', true);
    });

    function importSql(sql) {
        if (sql) {
            try {
                field('#builder').queryBuilder('setRulesFromSQL', sql);
            } catch (e) {
                alert(messages.parseFailed);
            }
        }
    }

    field('#import-query').on('click', function (e) {
        e.preventDefault();
        importSql(window.prompt(messages.sqlPrompt));
    });

    field('#import-old-format').on('click', function (e) {
        e.preventDefault();
        const old = window.prompt(messages.legacyPrompt);
        importSql(old && old.replace(/&&/g, 'AND')
            .replace(/\|\|/g, 'OR')
            .replace(/%/g, '')
            .replace(/"/g, "'")
            .replace(/~/g, 'REGEXP')
            .replace(/@/g, '.*'));
    });

    field('#maps, #alert_operation_id').each(function () {
        $(this).select2({
            theme: 'bootstrap',
            width: '100%',
            placeholder: this.dataset.placeholder,
            allowClear: ! this.multiple,
            ajax: { url: this.dataset.url, delay: 150 },
        });
    });

    $('.alert-rule-picker').each(function () {
        const source = this.dataset.draftSource;
        const $table = $(this);
        $table.on('loaded.rs.jquery.bootgrid', () => {
            $table.find('tbody a').on('click', (event) => event.stopPropagation());
        }).bootgrid({
            caseSensitive: false,
            formatters: {
                select: (_, row) => {
                    const url = new URL(createUrl, window.location.href);
                    url.searchParams.set(source, row.id);

                    return $('<a>', {
                        class: 'btn btn-sm btn-primary',
                        href: url.href,
                        text: messages.select,
                    })[0].outerHTML;
                },
            },
        });
    });

    if (openCollection) {
        $('#search_rule_modal').modal('show');
    }

    $saveRule.prop('disabled', false);
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) {
            savingRule = false;
            $saveRule.prop('disabled', false);
        }
    });
})();
</script>
@endpush
