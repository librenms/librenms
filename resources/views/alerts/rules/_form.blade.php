
<div class="panel panel-default" x-data="alertRuleForm()" x-init="init()">
    <div class="panel-heading">
        <h3 class="panel-title">
            {{ __('Create Alert Rule') }}
            <div class="pull-right">
                <button type="button" class="btn btn-sm btn-default tw:mr-2" x-on:click="showAllHelp = !showAllHelp">
                    <i class="fa" :class="showAllHelp ? 'fa-eye-slash' : 'fa-eye'"></i>
                    <span x-text="showAllHelp ? '{{ __('Hide Help') }}' : '{{ __('Show All Help') }}'"></span>
                </button>
                <a target="_blank" href="https://docs.librenms.org/Alerting/" class="tw:mr-5"><i class="fa fa-book"></i> {{ __('Documentation') }}</a>
                <a href="javascript:void(0);" onclick="window.history.back();" class="tw:text-gray-700 tw:hover:text-red-600 tw:no-underline tw:text-3xl tw:transition-colors tw:duration-200" title="{{ __('Close') }}">
                    <i class="fa fa-times"></i>
                </a>
            </div>
        </h3>
    </div>
    <div class="panel-body">
        <form method="post" role="form" id="rules" class="form-horizontal alerts-form">
            @csrf

            <legend class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:border-b-2 tw:border-blue-500 tw:pb-2 tw:mb-5 tw:mt-8 first:tw:mt-0">
                <span class="tw:inline-flex tw:items-center tw:justify-center tw:w-7 tw:h-7 tw:bg-blue-500 tw:text-white tw:rounded-full tw:text-sm tw:font-bold tw:mr-2">1</span>
                {{ __('alerting.rules.setup.legend') }}
            </legend>

            <div class='form-group'>
                <label for='rule_name' class='col-sm-3 col-md-2 control-label'>
                    {{ __('alerting.rules.setup.name.label') }}
                    <i class="fa fa-question-circle tw:text-gray-500 tw:ml-1 tw:text-sm tw:cursor-help hover:tw:text-blue-500 tw:transition-colors"
                       data-toggle="tooltip"
                       data-placement="right"
                       title="{{ __('alerting.rules.setup.name.help') }}"></i>
                </label>
                <div class='col-sm-9 col-md-10'>
                    <input type='text' id='rule_name' name='name' class='form-control validation' maxlength='200' required x-model="rule.name">
                    <span class="help-block tw:mt-1 tw:text-gray-600 tw:text-xs" x-show="showAllHelp" x-transition>{{ __('alerting.rules.setup.name.help') }}</span>
                </div>
            </div>

            <div class="form-group"  x-show="! rule.extra.override_query">
                <div class="col-sm-3 col-md-2">
                    <div class="dropdown">
                        <button class="btn btn-default dropdown-toggle" type="button" id="import-from" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                            {{ __('alerting.rules.setup.import.button') }}
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="import-from" id="import-dropdown">
                            <li><a href="#" x-on:click.prevent="importSql">{{ __('alerting.rules.setup.import.sql_query') }}</a></li>
                            <li><a href="#" x-on:click.prevent="importOld">{{ __('alerting.rules.setup.import.old_format') }}</a></li>
                            <li><a href="#" name="import-collection" id="import-collection" x-on:click.prevent="openModal('search_rule_modal')">{{ __('alerting.rules.setup.import.collection') }}</a></li>
                            <li><a href="#" name="import-alert_rule" id="import-alert_rule" x-on:click.prevent="openModal('search_alert_rule_modal')">{{ __('alerting.rules.setup.import.alert_rule') }}</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-sm-9 col-md-10">
                    <div id="builder"></div>
                    <span class="help-block tw:mt-1 tw:text-gray-600 tw:text-xs">{{ __('alerting.rules.setup.builder.help') }}</span>
                </div>
            </div>

            <div class='form-group' x-show="rule.extra.override_query">
                <label for='adv_query' class='col-sm-3 col-md-2 control-label'>
                    {{ __('alerting.rules.setup.sql.label') }}
                    <i class="fa fa-question-circle tw:text-gray-500 tw:ml-1 tw:text-sm tw:cursor-help hover:tw:text-blue-500 tw:transition-colors"
                       data-toggle="tooltip"
                       data-placement="right"
                       title="{{ __('alerting.rules.setup.sql.help') }}"></i>
                </label>
                <div class='col-sm-9 col-md-10'>
                    <textarea id='adv_query' name='adv_query' class='form-control' rows="4" x-model="rule.extra.adv_query"></textarea>
                    <span class="help-block tw:mt-1 tw:text-gray-600 tw:text-xs" x-show="showAllHelp" x-transition>{{ __('alerting.rules.setup.sql.help') }}</span>
                </div>
            </div>

            <div class='form-group'>
                <label for='override_query' class='col-sm-3 col-md-2 control-label'>
                    {{ __('alerting.rules.setup.override_sql.label') }}
                    <i class="fa fa-question-circle tw:text-gray-500 tw:ml-1 tw:text-sm tw:cursor-help hover:tw:text-blue-500 tw:transition-colors"
                       data-toggle="tooltip"
                       data-placement="right"
                       title="{{ __('alerting.rules.setup.override_sql.help') }}"></i>
                </label>
                <div class='col-sm-9 col-md-10'>
                    <input type='checkbox' name='override_query' id='override_query' x-data="toggleInput()" x-model="rule.extra.override_query">
                    <span class="help-block tw:mt-1 tw:text-gray-600 tw:text-xs" x-show="showAllHelp" x-transition>{{ __('alerting.rules.setup.override_sql.help') }}</span>
                </div>
            </div>

            <div class='form-group'>
                <label for='invert' class='col-sm-3 col-md-2 control-label'>
                    {{ __('alerting.rules.setup.invert_match.label') }}
                    <i class="fa fa-question-circle tw:text-gray-500 tw:ml-1 tw:text-sm tw:cursor-help hover:tw:text-blue-500 tw:transition-colors"
                       data-toggle="tooltip"
                       data-placement="right"
                       title="{{ __('alerting.rules.setup.invert_match.help') }}"></i>
                </label>
                <div class='form col-sm-9 col-md-10'>
                    <input type="checkbox" name="invert" id="invert" x-data="toggleInput()" x-model="rule.extra.invert">
                    <span class="help-block tw:mt-1 tw:text-gray-600 tw:text-xs" x-show="showAllHelp" x-transition>{{ __('alerting.rules.setup.invert_match.help') }}</span>
                </div>
            </div>

            <legend class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:border-b-2 tw:border-blue-500 tw:pb-2 tw:mb-5 tw:mt-8">
                <span class="tw:inline-flex tw:items-center tw:justify-center tw:w-7 tw:h-7 tw:bg-blue-500 tw:text-white tw:rounded-full tw:text-sm tw:font-bold tw:mr-2">2</span>
                {{ __('alerting.rules.targeting.legend') }}
            </legend>

            <div class="form-group">
                <label for='maps' class='col-sm-3 col-md-2 control-label'>
                    {{ __('alerting.rules.targeting.maps.label') }}
                    <i class="fa fa-question-circle tw:text-gray-500 tw:ml-1 tw:text-sm tw:cursor-help hover:tw:text-blue-500 tw:transition-colors"
                       data-toggle="tooltip"
                       data-placement="right"
                       title="{{ __('alerting.rules.targeting.maps.help') }}"></i>
                </label>
                <div class="col-sm-9 col-md-10">
                    <select id="maps" name="maps[]" class="form-control" multiple="multiple"></select>
                    <span class="help-block tw:mt-1 tw:text-gray-600 tw:text-xs" x-show="showAllHelp" x-transition>{{ __('alerting.rules.targeting.maps.help') }}</span>
                </div>
            </div>

            <div class="form-group">
                <label for='invert_map' class='col-sm-3 col-md-2 control-label'>
                    {{ __('alerting.rules.targeting.invert_map.label') }}
                    <i class="fa fa-question-circle tw:text-gray-500 tw:ml-1 tw:text-sm tw:cursor-help hover:tw:text-blue-500 tw:transition-colors"
                       data-toggle="tooltip"
                       data-placement="right"
                       title="{{ __('alerting.rules.targeting.invert_map.help') }}"></i>
                </label>
                <div class="col-sm-9 col-md-10">
                    <input type='checkbox' name='invert_map' id='invert_map' x-data="toggleInput()" x-model="rule.invert_map">
                    <span class="help-block tw:mt-1 tw:text-gray-600 tw:text-xs" x-show="showAllHelp" x-transition>{{ __('alerting.rules.targeting.invert_map.help') }}</span>
                </div>
            </div>

            <legend class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:border-b-2 tw:border-blue-500 tw:pb-2 tw:mb-5 tw:mt-8">
                <span class="tw:inline-flex tw:items-center tw:justify-center tw:w-7 tw:h-7 tw:bg-blue-500 tw:text-white tw:rounded-full tw:text-sm tw:font-bold tw:mr-2">3</span>
                {{ __('alerting.rules.notifications.legend') }}
            </legend>

            <div class="form-group">
                <label for='severity' class='col-sm-3 col-md-2 control-label'>
                    {{ __('alerting.rules.notifications.severity.label') }}
                </label>
                <div class="col-sm-9 col-md-10">
                    <select name='severity' id='severity' class='form-control' style="max-width: 7.5em" x-model="rule.severity">
                        <option value='ok'>{{ __('alerting.rules.notifications.severity.options.ok') }}</option>
                        <option value='warning'>{{ __('alerting.rules.notifications.severity.options.warning') }}</option>
                        <option value='critical'>{{ __('alerting.rules.notifications.severity.options.critical') }}</option>
                    </select>
                    <span class="tw:block tw:mt-1 tw:text-gray-600 tw:text-xs">{{ __('alerting.rules.notifications.severity.help') }}</span>
                </div>
            </div>

            <div class='form-group'>
                <label for='mute' class='col-sm-3 col-md-2 control-label'>
                    {{ __('alerting.rules.notifications.mute.label') }}
                    <i class="fa fa-question-circle tw:text-gray-500 tw:ml-1 tw:text-sm tw:cursor-help hover:tw:text-blue-500 tw:transition-colors"
                       data-toggle="tooltip"
                       data-placement="right"
                       title="{{ __('alerting.rules.notifications.mute.help') }}"></i>
                </label>
                <div class='col-sm-9 col-md-10'>
                    <input type="checkbox" x-data="toggleInput()" x-model="rule.extra.mute">
                    <span class="help-block tw:mt-1 tw:text-gray-600 tw:text-xs" x-show="showAllHelp" x-transition>{{ __('alerting.rules.notifications.mute.help') }}</span>
                </div>
            </div>

            <div class="form-group" x-show="! rule.extra.mute">
                <label for='delay' class='col-sm-3 col-md-2 control-label'>
                    {{ __('alerting.rules.notifications.delay.label') }}
                    <button type="button" class="tw:bg-transparent tw:border-0 tw:text-blue-500 tw:cursor-pointer tw:p-0 tw:ml-1 tw:text-sm hover:tw:text-blue-700 tw:transition-colors focus:tw:outline-none" x-on:click="helpExpanded.delay = !helpExpanded.delay">
                        <i class="fa" :class="helpExpanded.delay ? 'fa-chevron-up' : 'fa-info-circle'"></i>
                    </button>
                </label>
                <div class="col-sm-9 col-md-10">
                    <input type='text' id='delay' name='delay' class='form-control' style="max-width: 7.5em" value="{{ $default_delay ?? '' }}" x-model="rule.extra.delay">
                    <div class="tw:mt-2 tw:p-2.5 tw:bg-gray-50 tw:border-l-4 tw:border-blue-500 tw:rounded" x-show="helpExpanded.delay || showAllHelp" x-transition>
                        <span class="tw:block tw:m-0 tw:text-gray-700 tw:text-xs">{{ __('alerting.rules.notifications.delay.help') }}</span>
                    </div>
                </div>
            </div>

            <div class="form-group" x-show="! rule.extra.mute">
                <label for='interval' class='col-sm-3 col-md-2 control-label'>
                    {{ __('alerting.rules.notifications.interval.label') }}
                    <button type="button" class="tw:bg-transparent tw:border-0 tw:text-blue-500 tw:cursor-pointer tw:p-0 tw:ml-1 tw:text-sm hover:tw:text-blue-700 tw:transition-colors focus:tw:outline-none" x-on:click="helpExpanded.interval = !helpExpanded.interval">
                        <i class="fa" :class="helpExpanded.interval ? 'fa-chevron-up' : 'fa-info-circle'"></i>
                    </button>
                </label>
                <div class="col-sm-9 col-md-10">
                    <input type='text' id='interval' name='interval' class='form-control' style="max-width: 7.5em" value="{{ $default_interval ?? '' }}" x-model="rule.extra.interval">
                    <div class="tw:mt-2 tw:p-2.5 tw:bg-gray-50 tw:border-l-4 tw:border-blue-500 tw:rounded" x-show="helpExpanded.interval || showAllHelp" x-transition>
                        <span class="tw:block tw:m-0 tw:text-gray-700 tw:text-xs">{{ __('alerting.rules.notifications.interval.help') }}</span>
                    </div>
                </div>
            </div>

            <div class="form-group" x-show="! rule.extra.mute">
                <label for='count' class='col-sm-3 col-md-2 control-label'>
                    {{ __('alerting.rules.notifications.count.label') }}
                    <button type="button" class="tw:bg-transparent tw:border-0 tw:text-blue-500 tw:cursor-pointer tw:p-0 tw:ml-1 tw:text-sm hover:tw:text-blue-700 tw:transition-colors focus:tw:outline-none" x-on:click="helpExpanded.count = !helpExpanded.count">
                        <i class="fa" :class="helpExpanded.count ? 'fa-chevron-up' : 'fa-info-circle'"></i>
                    </button>
                </label>
                <div class="col-sm-9 col-md-10">
                    <input type='text' id='count' name='count' class='form-control' style="max-width: 7.5em" value="{{ $default_max_alerts ?? '' }}" x-model="rule.extra.count">
                    <div class="tw:mt-2 tw:p-2.5 tw:bg-gray-50 tw:border-l-4 tw:border-blue-500 tw:rounded" x-show="helpExpanded.count || showAllHelp" x-transition>
                        <span class="tw:block tw:m-0 tw:text-gray-700 tw:text-xs">{{ __('alerting.rules.notifications.count.help') }}</span>
                    </div>
                </div>
            </div>

            <div class='form-group' x-show="! rule.extra.mute">
                <label for='recovery' class='col-sm-3 col-md-2 control-label'>
                    {{ __('alerting.rules.notifications.recovery.label') }}
                    <i class="fa fa-question-circle tw:text-gray-500 tw:ml-1 tw:text-sm tw:cursor-help hover:tw:text-blue-500 tw:transition-colors"
                       data-toggle="tooltip"
                       data-placement="right"
                       title="{{ __('alerting.rules.notifications.recovery.help') }}"></i>
                </label>
                <div class='col-sm-9 col-md-10'>
                    <input type="checkbox" name="recovery" id="recovery" x-data="toggleInput()" x-model="rule.extra.recovery">
                    <span class="help-block tw:mt-1 tw:text-gray-600 tw:text-xs" x-show="showAllHelp" x-transition>{{ __('alerting.rules.notifications.recovery.help') }}</span>
                </div>
            </div>

            <div class='form-group' x-show="! rule.extra.mute">
                <label for='acknowledgement' class='col-sm-3 col-md-2 control-label'>
                    {{ __('alerting.rules.notifications.acknowledgement.label') }}
                    <i class="fa fa-question-circle tw:text-gray-500 tw:ml-1 tw:text-sm tw:cursor-help hover:tw:text-blue-500 tw:transition-colors"
                       data-toggle="tooltip"
                       data-placement="right"
                       title="{{ __('alerting.rules.notifications.acknowledgement.help') }}"></i>
                </label>
                <div class='col-sm-9 col-md-10'>
                    <input type="checkbox" name="acknowledgement" id="acknowledgement" x-data="toggleInput()" x-model="rule.extra.acknowledgement">
                    <span class="help-block tw:mt-1 tw:text-gray-600 tw:text-xs" x-show="showAllHelp" x-transition>{{ __('alerting.rules.notifications.acknowledgement.help') }}</span>
                </div>
            </div>

            <div x-show="! rule.extra.mute">
                <legend class="tw:text-base tw:font-semibold tw:text-gray-700 tw:border-b tw:border-gray-300 tw:pb-2 tw:mb-4 tw:mt-6">
                    <span class="tw:inline-flex tw:items-center tw:justify-center tw:w-8 tw:h-7 tw:bg-gray-500 tw:text-white tw:rounded-full tw:text-xs tw:font-bold tw:mr-2">3a</span>
                    {{ __('alerting.rules.notifications.delivery.legend') }}
                </legend>

                <div class="form-group">
                    <label for="transports" class="col-sm-3 col-md-2 control-label">
                        {{ __('alerting.rules.notifications.delivery.label') }}
                        <i class="fa fa-question-circle tw:text-gray-500 tw:ml-1 tw:text-sm tw:cursor-help hover:tw:text-blue-500 tw:transition-colors"
                           data-toggle="tooltip"
                           data-placement="right"
                           title="{{ __('alerting.rules.notifications.delivery.help') }}"></i>
                    </label>
                    <div class="col-sm-9 col-md-10">
                        <select id="transports" name="transports[]" class="form-control" multiple="multiple"></select>
                        <span class="help-block tw:mt-1 tw:text-gray-600 tw:text-xs" x-show="showAllHelp" x-transition>{{ __('alerting.rules.notifications.delivery.help') }}</span>
                    </div>
                </div>
            </div>

            <div x-show="! rule.extra.mute">
                <legend class="tw:text-base tw:font-semibold tw:text-gray-700 tw:border-b tw:border-gray-300 tw:pb-2 tw:mb-4 tw:mt-6">
                    <span class="tw:inline-flex tw:items-center tw:justify-center tw:w-8 tw:h-7 tw:bg-gray-500 tw:text-white tw:rounded-full tw:text-xs tw:font-bold tw:mr-2">3b</span>
                    {{ __('alerting.rules.templates.legend') }}
                </legend>

                <div class="form-group">
                    <label for='template_id' class='col-sm-3 col-md-2 control-label'>
                        {{ __('alerting.rules.templates.label') }}
                        <i class="fa fa-question-circle tw:text-gray-500 tw:ml-1 tw:text-sm tw:cursor-help hover:tw:text-blue-500 tw:transition-colors"
                           data-toggle="tooltip"
                           data-placement="right"
                           title="{{ __('alerting.rules.templates.help') }}"></i>
                    </label>
                    <div class='col-sm-9 col-md-10'>
                        <select id="template_id" name="template_id" class="form-control" x-model="rule.template">
                            <option value="">{{ __('alerting.rules.templates.use_default') }}</option>
                            @foreach(($templates ?? []) as $tpl)
                                <option value="{{ $tpl->id }}">{{ $tpl->name }}</option>
                            @endforeach
                        </select>
                        <span class="help-block tw:mt-1 tw:text-gray-600 tw:text-xs" x-show="showAllHelp" x-transition>{{ __('alerting.rules.templates.help') }}</span>
                    </div>
                </div>

                <div class="form-group" id="per-transport-templates">
                    <label class='col-sm-3 col-md-2 control-label'>
                        {{ __('alerting.rules.templates.per_transport.label') }}
                        <i class="fa fa-question-circle tw:text-gray-500 tw:ml-1 tw:text-sm tw:cursor-help hover:tw:text-blue-500 tw:transition-colors"
                           data-toggle="tooltip"
                           data-placement="right"
                           title="{{ __('alerting.rules.templates.per_transport.help') }}"></i>
                    </label>
                    <div class='col-sm-9 col-md-10'>
                        <div id="transport-template-list">
                            <template x-for="id in rule.transports" :key="id">
                                <div class="form-inline tw:mb-1.5">
                                    <label class="control-label tw:min-w-[220px] tw:mr-2" x-text="transportLabels[id] ?? ('Transport #' + id)"></label>
                                    <select class="form-control tw:min-w-[260px]" :name="'template_transports[' + id + ']'">
                                        <option value="" x-text="perTransportNoOverride"></option>
                                        <template x-for="opt in templateOptions" :key="opt.id">
                                            <option :value="opt.id" x-text="opt.name"></option>
                                        </template>
                                    </select>
                                </div>
                            </template>
                        </div>
                        <span class="help-block tw:mt-1 tw:text-gray-600 tw:text-xs" x-show="showAllHelp" x-transition>{{ __('alerting.rules.templates.per_transport.help') }}</span>
                    </div>
                </div>
            </div>

            <legend class="tw:text-lg tw:font-semibold tw:text-gray-800 tw:border-b-2 tw:border-blue-500 tw:pb-2 tw:mb-5 tw:mt-8">
                <span class="tw:inline-flex tw:items-center tw:justify-center tw:w-7 tw:h-7 tw:bg-blue-500 tw:text-white tw:rounded-full tw:text-sm tw:font-bold tw:mr-2">4</span>
                {{ __('alerting.rules.notes.legend') }}
            </legend>

            <div class='form-group'>
                <label for='proc' class='col-sm-3 col-md-2 control-label'>
                    {{ __('alerting.rules.notes.proc_url.label') }}
                    <i class="fa fa-question-circle tw:text-gray-500 tw:ml-1 tw:text-sm tw:cursor-help hover:tw:text-blue-500 tw:transition-colors"
                       data-toggle="tooltip"
                       data-placement="right"
                       title="{{ __('alerting.rules.notes.proc_url.help') }}"></i>
                </label>
                <div class='col-sm-9 col-md-10'>
                    <input type='text' id='proc' name='proc' class='form-control validation' pattern='(http|https)://.*' maxlength='80' x-model="rule.proc">
                    <span class="help-block tw:mt-1 tw:text-gray-600 tw:text-xs" x-show="showAllHelp" x-transition>{{ __('alerting.rules.notes.proc_url.help') }}</span>
                </div>
            </div>

            <div class='form-group'>
                <label for='notes' class='col-sm-3 col-md-2 control-label'>
                    {{ __('alerting.rules.notes.notes.label') }}
                    <i class="fa fa-question-circle tw:text-gray-500 tw:ml-1 tw:text-sm tw:cursor-help hover:tw:text-blue-500 tw:transition-colors"
                       data-toggle="tooltip"
                       data-placement="right"
                       title="{{ __('alerting.rules.notes.notes.help') }}"></i>
                </label>
                <div class='col-sm-9 col-md-10'>
                    <textarea class="form-control" rows="6" name="notes" id='notes' x-model="rule.notes"></textarea>
                    <span class="help-block tw:mt-1 tw:text-gray-600 tw:text-xs" x-show="showAllHelp" x-transition>{{ __('alerting.rules.notes.notes.help') }}</span>
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-9 col-md-offset-2 col-md-10">
                    <button @click.prevent="save" class="btn btn-primary">{{ __('Save') }}</button>
                    <a href="javascript:void(0);" onclick="window.history.back();" class="btn btn-default">{{ __('Cancel') }}</a>
                </div>
            </div>

        </form>
    </div>
</div>

@section('javascript')
    <script src="{{ asset('js/sql-parser.min.js') }}"></script>
    <script src="{{ asset('js/query-builder.standalone.min.js') }}"></script>
    <script src="{{ asset('js/interact.min.js') }}"></script>
@endsection

@push('scripts')
<script>
    function alertRuleForm() {
        return {
            mode: @json($mode ?? 'create'),
            saveUrl: @json($saveUrl ?? url('alert-rule')),
            saveMethod: @json($saveMethod ?? 'POST'),
            loadUrl: @json($loadUrl ?? null),
            rule: {
                name: '',
                severity: 'critical',
                notes: '',
                builder: null,
                proc: '',
                extra: {
                    mute: {{ ($default_mute_alerts ?? false) ? 'true' : 'false' }},
                    invert: {{ ($default_invert_rule_match ?? false) ? 'true' : 'false' }},
                    recovery: {{ ($default_recovery_alerts ?? false) ? 'true' : 'false' }},
                    acknowledgement: {{ ($default_acknowledgement_alerts ?? false) ? 'true' : 'false' }},
                    override_query: {{ ($default_invert_map ?? false) ? 'true' : 'false' }},
                    adv_query: '',
                    count: 1,
                    delay: 0,
                    interval: 0,
                },
                invert_map: false,
                maps: [],
                transports: [],
                template: null,
            },
            templateOptions: @json(($templates ?? collect())->toArray()),
            perTransportNoOverride: @json(__('alerting.rules.templates.per_transport.no_override')),
            transportLabels: {},
            showAllHelp: false,
            helpExpanded: {
                delay: false,
                interval: false,
                count: false
            },

            init() {
                // expose Alpine component globally so external scripts (like modals) can interact
                window.alertRule = this;

                this.initQueryBuilder();
                this.initSelect2('#maps', '{{ route('ajax.select.alert-transports-groups') }}', v => this.rule.maps = v);
                this.initSelect2('#transports', '{{ route('ajax.select.alert-transport-group') }}', v => this.rule.transports = v);

                if (this.mode === 'edit' && this.loadUrl) {
                    fetch(this.loadUrl)
                        .then(r => r.json())
                        .then(data => this.loadRule(data))
                        .catch(() => toastr.error("Failed to load rule"));
                }

                // Initialize Bootstrap tooltips
                this.$nextTick(() => {
                    $('[data-toggle="tooltip"]').tooltip({
                        container: 'body',
                        html: true
                    });
                });
            },

            initQueryBuilder() {
                var filters = {!! $filters !!};
                $('#builder').queryBuilder({
                    plugins: ['bt-tooltip-errors'],
                    allow_empty: true,
                    filters: filters,
                    operators: [
                        {type: 'equal', nb_inputs: 1, multiple: false, apply_to: ['string', 'number', 'datetime']},
                        {type: 'not_equal', nb_inputs: 1, multiple: false, apply_to: ['string', 'number', 'datetime']},
                        {type: 'in', nb_inputs: 1, multiple: true, apply_to: ['string', 'number']},
                        {type: 'not_in', nb_inputs: 1, multiple: true, apply_to: ['string', 'number']},
                        {type: 'less', nb_inputs: 1, multiple: false, apply_to: ['string', 'number', 'datetime']},
                        {type: 'less_or_equal', nb_inputs: 1, multiple: false, apply_to: ['string', 'number', 'datetime']},
                        {type: 'greater', nb_inputs: 1, multiple: false, apply_to: ['string', 'number', 'datetime']},
                        {type: 'greater_or_equal', nb_inputs: 1, multiple: false, apply_to: ['string', 'number', 'datetime']},
                        {type: 'regex', nb_inputs: 1, multiple: false, apply_to: ['string', 'number']},
                        {type: 'not_regex', nb_inputs: 1, multiple: false, apply_to: ['string', 'number']}
                    ],
                    lang: { operators: { regexp: 'regex', not_regex: 'not regex' } },
                    sqlOperators: { regexp: {op: 'REGEXP'}, not_regexp: {op: 'NOT REGEXP'} },
                    sqlRuleOperator: { 'REGEXP': function (v) { return {val: v, op: 'regexp'}; }, 'NOT REGEXP': function (v) { return {val: v, op: 'not_regexp'}; } }
                });
            },

            initSelect2(el, url, callback) {
                $(el).select2({
                    width: '100%',
                    ajax: {
                        url: url,
                        delay: 150
                    }
                }).on('change', () => {
                    const val = $(el).val();
                    callback(val);
                    if (el === '#transports') {
                        const data = ($(el).data('select2') ? $(el).select2('data') : []) || [];
                        const labels = {};
                        data.forEach(i => { if (i && i.id != null) { labels[i.id] = i.text; } });
                        this.transportLabels = labels;
                    }
                });
            },


            loadRule(rule) {
                const r = rule || {};

                // prepare maps ids and inject option text if provided
                let mapsIds = [];
                if (Array.isArray(r.maps)) {
                    mapsIds = r.maps.map(m => (typeof m === 'object' ? m.id : m));
                    const $maps = $('#maps');
                    if ($maps.length) {
                        r.maps.forEach(m => {
                            if (m && typeof m === 'object' && m.text != null) {
                                if (!$maps.find('option[value="' + m.id + '"]').length) {
                                    const opt = new Option(m.text, m.id, false, false);
                                    $maps.append(opt);
                                }
                            }
                        });
                    }
                }

                // prepare transport ids and inject option text if provided
                let transportIds = [];
                if (Array.isArray(r.transports)) {
                    transportIds = r.transports.map(t => (typeof t === 'object' ? t.id : t));
                    const $trans = $('#transports');
                    if ($trans.length) {
                        r.transports.forEach(t => {
                            if (t && typeof t === 'object' && t.text != null) {
                                if (!$trans.find('option[value="' + t.id + '"]').length) {
                                    const opt = new Option(t.text, t.id, false, false);
                                    $trans.append(opt);
                                }
                            }
                        });
                    }
                }

                this.rule = {
                    ...this.rule,
                    ...r,
                    extra: { ...this.rule.extra, ...(r.extra || {}) },
                    maps: mapsIds,
                    transports: transportIds,
                };

                $('#builder').queryBuilder('setRules', this.rule.builder || {});
                $('#maps').val(this.rule.maps).trigger('change');
                $('#transports').val(this.rule.transports).trigger('change');
            },

            async save() {
                const rules = $('#builder').queryBuilder('getRules');
                if (!rules?.valid) {
                    toastr.error("Invalid rule");
                    return;
                }
                this.rule.builder = rules;

                try {
                    const res = await fetch(this.saveUrl, {
                        method: this.saveMethod,
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(this.rule)
                    });
                    const data = await res.json();
                    if (data.status === 'ok') {
                        toastr.success(data.message);
                        window.location.href = "{{ url('alert-rules') }}";
                    } else {
                        toastr.error(data.message);
                    }
                } catch {
                    toastr.error("Failed to process rule");
                }
            },

            openModal(id) {
                try { $('#'+id).modal('show'); } catch (e) {}
            },

            importSql() {
                const sql = window.prompt("Enter SQL query");
                if (!sql) return;
                try {
                    $("#builder").queryBuilder("setRulesFromSQL", sql);
                } catch {
                    alert("Could not parse SQL");
                }
            },

            importOld() {
                let input = window.prompt("Enter old rule format");
                if (!input) return;
                try {
                    input = input
                        .replace(/&&/g, 'AND')
                        .replace(/\|\|/g, 'OR')
                        .replace(/%/g, '')
                        .replace(/"/g, "'")
                        .replace(/~/g, 'REGEXP')
                        .replace(/@/g, '.*');
                    $("#builder").queryBuilder("setRulesFromSQL", input);
                } catch {
                    alert("Could not parse old rule");
                }
            }
        }
    }
</script>
<script>
    // helper to format seconds to s/m/h/d string
    function formatDuration(val) {
        if (val == null || isNaN(val)) { return ''; }
        if ((val / 86400) >= 1) { return (val / 86400) + 'd'; }
        if ((val / 3600) >= 1) { return (val / 3600) + 'h'; }
        if ((val / 60) >= 1) { return (val / 60) + 'm'; }
        return String(val);
    }

    // Bridge function so other scripts (like _modals) can load a rule into the Alpine component
    function loadRule(rule) {
        if (window.alertRule && typeof window.alertRule.loadRule === 'function') {
            window.alertRule.loadRule(rule);
            return;
        }
        try {
            console.warn('Alert Rule Alpine component not ready to load rule yet');
        } catch (e) { }
    }

</script>
@endpush
