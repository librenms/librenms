@extends('layouts.librenmsv1')

@section('title', __('Add Device'))

@section('content')
    <div class="container">
        <x-panel>
            <x-slot name="title">
                <i class="fa-solid fa-plus fa-fw fa-lg" aria-hidden="true"></i> {{ __('Add Device') }}
            </x-slot>

            <form method="POST" action="{{ route('device.add.store') }}"
                  @submit.prevent="submitForm()"
                  x-data="addDeviceForm(@js($add_device_config))">
                @csrf
                <template x-if="topErrors.length > 0">
                    <div class="alert alert-danger tw:mb-6">
                        <ul class="tw:list-disc tw:list-inside tw:space-y-1">
                            <template x-for="(error, index) in topErrors" :key="index">
                                <li x-text="error"></li>
                            </template>
                        </ul>
                    </div>
                </template>

                {{-- General Properties Section --}}
                <div class="tw:bg-gray-50 tw:dark:bg-dark-gray-300 tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:rounded-xl tw:p-6 tw:mb-6">
                    <div class="tw:text-lg tw:font-semibold tw:mb-4 tw:text-gray-800 tw:dark:text-dark-white-100 tw:flex tw:items-center tw:gap-2">
                        <i class="fa-solid fa-circle-info tw:text-[#337ab7]"></i>
                        {{ __('General Properties') }}
                    </div>
                    <div class="tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:p-5 tw:rounded-lg tw:bg-white tw:dark:bg-dark-gray-500 tw:space-y-5">
                        {{-- Hostname & Poller Group --}}
                        <div class="tw:grid tw:grid-cols-1 @config('distributed_poller') tw:md:grid-cols-2 @endconfig tw:gap-5">
                            <div class="form-group tw:mb-0"
                                 :class="(errors && errors['hostname']) ? 'has-error' : ''">
                                <label for="hostname" class="control-label tw:font-medium tw:text-gray-700 tw:dark:text-dark-white-200">
                                    {{ __('Hostname or IP') }} <span class="tw:text-red-500">*</span>
                                </label>
                                <input type="text" id="hostname" name="hostname" class="form-control"
                                       x-model="hostname" placeholder="device.example.com or 192.168.1.1" required autofocus>
                                <template x-if="errors && errors['hostname']">
                                    <span class="help-block" x-text="errors['hostname']?.[0]"></span>
                                </template>
                            </div>

                            @config('distributed_poller')
                            <div class="form-group tw:mb-0"
                                 :class="(errors && errors['poller_group']) ? 'has-error' : ''">
                                <label for="poller_group" class="control-label tw:font-medium tw:text-gray-700 tw:dark:text-dark-white-200">
                                    {{ __('Poller Group') }}
                                </label>
                                <select id="poller_group" name="poller_group" x-model="poller_group" class="form-control">
                                    <option value="0">{{ __('Default poller group') }}</option>
                                    @foreach($poller_groups as $group)
                                        <option value="{{ $group->id }}">{{ $group->group_name }}</option>
                                    @endforeach
                                </select>
                                <template x-if="errors && errors['poller_group']">
                                    <span class="help-block" x-text="errors['poller_group']?.[0]"></span>
                                </template>
                            </div>
                            @endconfig
                        </div>

                        {{-- Display Name Section --}}
                        <div class="tw:rounded-lg tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:bg-gray-50/70 tw:dark:bg-dark-gray-300 tw:p-4 tw:space-y-2.5">
                            {{-- Header with title and status badges --}}
                            <div class="tw:flex tw:flex-wrap tw:items-center tw:justify-between tw:gap-2 tw:min-h-[28px]">
                                <div class="tw:flex tw:items-center tw:gap-2">
                                    <span class="tw:text-sm tw:font-semibold tw:uppercase tw:tracking-wider tw:whitespace-nowrap tw:text-gray-600 tw:dark:text-dark-white-200">
                                        {{ __('Display Name') }}
                                    </span>
                                </div>
                                <div class="tw:flex tw:flex-wrap tw:items-center tw:gap-2">
                                    <template x-if="display_template && display_template.trim() !== ''">
                                        <span class="tw:inline-flex tw:items-center tw:gap-1 tw:px-2 tw:py-0.5 tw:rounded tw:text-sm tw:font-medium tw:whitespace-nowrap tw:bg-purple-100 tw:text-purple-800 tw:dark:bg-purple-900/60 tw:dark:text-purple-300"
                                              title="{{ __('Custom template override') }}">
                                            <i class="fa-solid fa-circle-info"></i> {{ __('Custom Template') }}
                                        </span>
                                    </template>
                                    <template x-if="hasPlaceholders">
                                        <span class="tw:inline-flex tw:items-center tw:gap-1 tw:px-2 tw:py-0.5 tw:rounded tw:text-sm tw:font-medium tw:whitespace-nowrap tw:bg-amber-100 tw:text-amber-800 tw:dark:bg-amber-900/60 tw:dark:text-amber-300"
                                              title="{{ __('Highlighted values are placeholders that will be replaced during discovery or polling') }}">
                                            <i class="fa-solid fa-circle-info"></i> {{ __('Placeholder data') }}
                                        </span>
                                    </template>
                                    <template x-if="!showTemplateInput">
                                        <button type="button" @click="showTemplateInput = true"
                                                class="tw:text-sm tw:font-medium tw:text-blue-600 tw:hover:text-blue-800 tw:dark:text-blue-400 tw:dark:hover:text-blue-300 tw:inline-flex tw:items-center tw:gap-1.5 tw:cursor-pointer tw:ml-1">
                                            <i class="fa-solid fa-pencil"></i> {{ __('Edit Template') }}
                                        </button>
                                    </template>
                                </div>
                            </div>

                            {{-- Computed Display Name Output (Preview) - Natural typography, NOT an input box --}}
                            <div class="tw:flex tw:items-center tw:gap-2 tw:min-h-[36px]">
                                <i class="fa-solid fa-tag tw:text-gray-400 tw:dark:text-dark-white-400 tw:shrink-0"></i>
                                <span class="tw:text-base tw:font-medium tw:text-gray-900 tw:dark:text-white tw:tracking-tight tw:break-all tw:leading-normal" x-ref="previewEl"></span>
                            </div>

                            {{-- Template Input (Hidden by default until user indicates they want to modify it) --}}
                            <div x-show="showTemplateInput" x-cloak class="tw:pt-2.5 tw:border-t tw:border-gray-200/80 tw:dark:border-dark-gray-400/80">
                                <div class="form-group tw:mb-0"
                                     :class="(errors && errors['display_template']) ? 'has-error' : ''">
                                    <div class="tw:flex tw:items-center tw:justify-between tw:mb-1.5">
                                        <div class="tw:flex tw:items-center tw:gap-1.5">
                                            <label for="display_template" class="control-label tw:font-medium tw:text-gray-700 tw:dark:text-dark-white-200 tw:mb-0">
                                                {{ __('Template') }}
                                            </label>
                                            <i class="fa-solid fa-circle-question tw:text-gray-400 tw:hover:text-gray-600 tw:dark:hover:text-dark-white-200 tw:cursor-help tw:text-sm"
                                               data-toggle="tooltip"
                                               data-placement="top"
                                               title="{{ __('Leave blank to use system default. Available variables:') }} &#123;&#123; $hostname &#125;&#125;, &#123;&#123; $sysName &#125;&#125;, &#123;&#123; $sysName_fallback &#125;&#125;, &#123;&#123; $ip &#125;&#125;"></i>
                                        </div>
                                        <div class="tw:flex tw:items-center tw:gap-2">
                                            <template x-if="display_template && display_template.trim() !== ''">
                                                <button type="button" @click="display_template = ''"
                                                        class="tw:text-blue-600 tw:hover:text-blue-800 tw:dark:text-blue-400 tw:dark:hover:text-blue-300 tw:cursor-pointer">
                                                    {{ __('Reset to default') }}
                                                </button>
                                            </template>
                                            <button type="button" @click="showTemplateInput = false"
                                                    class="tw:text-gray-500 tw:hover:text-gray-700 tw:dark:text-dark-white-300 tw:dark:hover:text-white tw:cursor-pointer">
                                                <i class="fa-solid fa-xmark"></i> {{ __('Close') }}
                                            </button>
                                        </div>
                                    </div>
                                    <input type="text" id="display_template" name="display_template" class="form-control"
                                           x-model="display_template" :placeholder="defaultDisplayTemplate">
                                    <template x-if="errors && errors['display_template']">
                                        <span class="help-block" x-text="errors['display_template']?.[0]"></span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Polling Methods Section --}}
                <div class="tw:bg-gray-50 tw:dark:bg-dark-gray-300 tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:rounded-xl tw:p-6">
                    <div class="tw:text-lg tw:font-semibold tw:mb-4 tw:text-gray-800 tw:dark:text-dark-white-100 tw:flex tw:items-center tw:gap-2">
                        <i class="fa-solid fa-sliders tw:text-[#337ab7]"></i>
                        {{ __('Polling Methods') }}
                    </div>

                    <div class="tw:flex tw:flex-col tw:md:flex-row tw:gap-6">

                        {{-- Left: tab list --}}
                        <div class="tw:w-full tw:md:w-1/4 tw:shrink-0">
                            <ul class="tw:flex tw:flex-col tw:space-y-2">
                                @foreach($availableMethods as $method)
                                    <li x-show="activeMethods.includes('{{ $method['type'] }}')"
                                        x-cloak
                                        :class="[
                                            activeTab === '{{ $method['type'] }}'
                                                ? 'tw:bg-blue-600 tw:border-blue-600 tw:dark:bg-blue-700 tw:dark:border-blue-700'
                                                : 'tw:border-gray-200 tw:hover:bg-gray-50 tw:dark:border-dark-gray-400 tw:dark:hover:bg-dark-gray-400',
                                            hasMethodErrors('{{ $method['type'] }}') ? (activeTab === '{{ $method['type'] }}' ? 'tw:ring-2 tw:ring-red-400' : 'tw:border-red-500 tw:dark:border-red-500') : ''
                                        ]"
                                        class="tw:flex tw:items-center tw:border tw:rounded-lg tw:shadow-sm tw:transition-colors tw:overflow-hidden">
                                        <button type="button"
                                                @click="activeTab = '{{ $method['type'] }}'"
                                                :class="activeTab === '{{ $method['type'] }}' ? 'tw:text-white!' : 'tw:text-gray-700 tw:dark:text-dark-white-200'"
                                                class="tw:flex-1 tw:text-left tw:px-4 tw:py-3 tw:font-medium tw:transition-colors tw:flex tw:items-center tw:justify-between">
                                            <span class="tw:flex tw:items-center">
                                                <i class="fa-solid fa-fw {{ $method['icon'] }} tw:mr-2"></i>
                                                {{ $method['label'] }}
                                            </span>
                                            <template x-if="hasMethodErrors('{{ $method['type'] }}')">
                                                <i class="fa-solid fa-circle-exclamation" :class="activeTab === '{{ $method['type'] }}' ? 'tw:text-red-200' : 'tw:text-red-500'"></i>
                                            </template>
                                        </button>
                                        <button type="button"
                                                @click="removeMethod('{{ $method['type'] }}')"
                                                :class="activeTab === '{{ $method['type'] }}' ? 'tw:text-blue-200 tw:hover:text-white' : 'tw:text-gray-400 tw:hover:text-red-500'"
                                                class="tw:px-3 tw:py-3 tw:shrink-0 tw:transition-colors"
                                                title="{{ __('Remove') }}"
                                                aria-label="{{ __('Remove') }} {{ $method['label'] }}">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </li>
                                @endforeach

                                 {{-- Add polling type --}}
                                 <x-device.polling.add-type-select />
                            </ul>
                        </div>

                        {{-- Right: tab panels --}}
                        <div class="tw:w-full tw:md:w-3/4 tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:rounded-lg tw:shadow-sm tw:p-6 tw:grow tw:bg-white tw:dark:bg-dark-gray-500" x-cloak>
                            <template x-if="activeMethods.length === 0">
                                <div class="tw:flex tw:flex-col tw:items-center tw:justify-center tw:text-center tw:py-8 tw:px-2 tw:text-gray-500 tw:dark:text-dark-white-300">
                                    <h4 class="tw:text-lg tw:font-semibold tw:text-gray-700 tw:dark:text-dark-white-200 tw:mb-1">
                                        {{ __('No Polling Methods Selected') }}
                                    </h4>
                                    <p class="tw:text-sm tw:max-w-md">
                                        {{ __('At least one polling method is required. Select a method from the list on the left to configure it.') }}
                                    </p>
                                </div>
                            </template>

                            @foreach($availableMethods as $method)
                                <template x-if="activeTab === '{{ $method['type'] }}' && activeMethods.includes('{{ $method['type'] }}')">
                                    <div>
                                        <div class="tw:text-2xl tw:font-semibold tw:mb-6 tw:pb-3 tw:border-b tw:border-gray-200 tw:dark:border-dark-gray-400 tw:text-gray-800 tw:dark:text-dark-white-100">
                                            {{ $method['label'] }} {{ __('Settings') }}
                                        </div>

                                        {{-- Active flag (submitted for all methods, controller ignores inactive ones) --}}
                                        <input type="hidden" name="polling_methods[{{ $method['type'] }}][active]" value="1">

                                        {{-- Method Options --}}
                                        <div class="tw:bg-gray-50 tw:dark:bg-dark-gray-300 tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:rounded-xl tw:p-5 tw:mb-6">
                                            <h4 class="tw:font-semibold tw:text-sm tw:uppercase tw:tracking-wider tw:mb-4 tw:text-gray-500 tw:dark:text-dark-white-300">{{ __('Method Options') }}</h4>
                                            <div class="tw:grid tw:grid-cols-1 tw:md:grid-cols-2 tw:gap-4 tw:max-w-2xl">
                                                <x-toggle bordered
                                                    :name="'polling_methods[' . $method['type'] . '][validate]'"
                                                    :model="'methods[\'' . $method['type'] . '\'].validate'"
                                                    :label="__('Validate on add')" />
                                                <x-toggle bordered
                                                    :name="'polling_methods[' . $method['type'] . '][affects_availability]'"
                                                    :model="'methods[\'' . $method['type'] . '\'].affects_availability'"
                                                    :label="__('poller.affects_availability')" />
                                            </div>
                                        </div>

                                        {{-- Credentials --}}
                                        @if(!empty($method['schema_fields']))
                                            <div class="tw:bg-gray-50 tw:dark:bg-dark-gray-300 tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:rounded-xl tw:p-5 tw:mb-6">
                                                <h4 class="tw:font-semibold tw:text-sm tw:uppercase tw:tracking-wider tw:mb-4 tw:text-gray-500 tw:dark:text-dark-white-300">{{ __('Credentials') }}</h4>

                                                <div class="tw:font-medium tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:p-5 tw:rounded-lg tw:bg-white tw:dark:bg-dark-gray-500">
                                                    <div class="tw:flex tw:flex-wrap tw:gap-4 tw:mb-4">
                                                        <label class="radio-inline">
                                                            <input type="radio"
                                                                   name="polling_methods[{{ $method['type'] }}][secret_mode]"
                                                                   value="default"
                                                                   x-model="methods['{{ $method['type'] }}'].secret_mode">
                                                            {{ __('Attempt Defaults') }}
                                                        </label>
                                                        <label class="radio-inline">
                                                            <input type="radio"
                                                                   name="polling_methods[{{ $method['type'] }}][secret_mode]"
                                                                   value="existing"
                                                                   x-model="methods['{{ $method['type'] }}'].secret_mode">
                                                            {{ __('Use Existing Secret') }}
                                                        </label>
                                                        <label class="radio-inline">
                                                            <input type="radio"
                                                                   name="polling_methods[{{ $method['type'] }}][secret_mode]"
                                                                   value="new"
                                                                   x-model="methods['{{ $method['type'] }}'].secret_mode">
                                                            {{ __('Create New Secret') }}
                                                        </label>
                                                    </div>

                                                    {{-- Existing secret picker --}}
                                                    <x-select2
                                                        :id="'secret-select-' . $method['type']"
                                                        :name="'polling_methods[' . $method['type'] . '][secret_id]'"
                                                        :label="__('Select Secret')"
                                                        type="secret"
                                                        :data="['secret_type' => $method['type']]"
                                                        :placeholder="__('Select an existing secret...')"
                                                        x-show="methods['{{ $method['type'] }}'].secret_mode === 'existing'"
                                                        x-cloak
                                                        x-model="methods['{{ $method['type'] }}'].secret_id"
                                                        class="tw:max-w-md"
                                                    />

                                                    {{-- New secret form --}}
                                                    <template x-if="methods['{{ $method['type'] }}'].secret_mode === 'new'">
                                                        <x-device.polling.new-secret-fields
                                                            :method="$method"
                                                            :name-prefix="'polling_methods[' . $method['type'] . '][secret_data]'"
                                                            :model-prefix="'methods[\'' . $method['type'] . '\'].formData'"
                                                            :description-name="'polling_methods[' . $method['type'] . '][description]'"
                                                            :description-model="'methods[\'' . $method['type'] . '\'].description'"
                                                            :description-placeholder="__('Optional')"
                                                            :error-key="'polling_methods.' . $method['type'] . '.description'"
                                                        />
                                                    </template>
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Settings fields --}}
                                        <x-device.polling.settings
                                            :method="$method"
                                            :name-prefix="'polling_methods[' . $method['type'] . '][settings]'"
                                            :model-prefix="'methods[\'' . $method['type'] . '\'].settingsData'"
                                        />

                                    </div>
                                </template>
                            @endforeach
                        </div>{{-- end right --}}

                    </div>{{-- end flex row --}}
                </div>

                {{-- SNMP manual overrides (only shown when SNMP polling method doesn't exist) --}}
                <div x-show="!activeMethods.includes('snmp')"
                     x-cloak
                     class="tw:bg-gray-50 tw:dark:bg-dark-gray-300 tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:rounded-xl tw:p-6 tw:mt-6"
                     x-transition>
                    <div class="tw:text-lg tw:font-semibold tw:mb-4 tw:text-gray-800 tw:dark:text-dark-white-100 tw:flex tw:items-center tw:gap-2">
                        <i class="fa-solid fa-wrench tw:text-[#337ab7]"></i>
                        {{ __('Manual Overrides') }}
                    </div>
                    <div class="tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:p-5 tw:rounded-lg tw:bg-white tw:dark:bg-dark-gray-500">
                        <div class="tw:grid tw:grid-cols-1 tw:md:grid-cols-3 tw:gap-4 tw:max-w-2xl">
                            <div class="form-group tw:mb-0" :class="(errors && errors.sysName) ? 'has-error' : ''">
                                <label for="sysName" class="control-label">{{ __('sysName') }} <span class="text-muted">({{ __('optional') }})</span></label>
                                <input type="text" id="sysName" name="sysName" class="form-control" x-model="sysName">
                                <template x-if="errors && errors.sysName">
                                    <span class="help-block" x-text="errors.sysName?.[0]"></span>
                                </template>
                            </div>
                            <div class="form-group tw:mb-0" :class="(errors && errors.hardware) ? 'has-error' : ''">
                                <label for="hardware" class="control-label">{{ __('Hardware') }} <span class="text-muted">({{ __('optional') }})</span></label>
                                <input type="text" id="hardware" name="hardware" class="form-control" x-model="hardware">
                                <template x-if="errors && errors.hardware">
                                    <span class="help-block" x-text="errors.hardware?.[0]"></span>
                                </template>
                            </div>
                            <x-select2
                                id="os-select"
                                name="os"
                                type="os"
                                :label="__('OS')"
                                :help="__('optional')"
                                :placeholder="__('OS (optional)')"
                                x-model="os"
                            />
                        </div>
                    </div>
                </div>

                <div class="tw:mt-6 tw:pt-6 tw:border-t tw:border-gray-200 tw:dark:border-dark-gray-400">
                    <button type="submit" :disabled="loading" class="btn btn-primary tw:bg-blue-600 tw:border-blue-600 tw:hover:bg-blue-700">
                        <template x-if="loading"><i class="fa-solid fa-spinner fa-spin tw:mr-1"></i></template>
                        <template x-if="!loading"><i class="fa-solid fa-plus tw:mr-1"></i></template>
                        {{ __('Add Device') }}
                    </button>
                </div>

                {{-- Reachability Failure Dialog --}}
                <x-device.polling.validation-failed-modal
                    action-click="addAnyway()"
                    :action-label="__('Add Anyway')"
                    action-icon="fa-plus"
                />
            </form>
        </x-panel>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('addDeviceForm', (config) => ({
                hostname: config.hostname || '',
                display_template: config.display_template || '',
                defaultDisplayTemplate: config.default_display_template || '',
                showTemplateInput: !!(config.display_template && config.display_template.trim() !== ''),
                poller_group: config.poller_group || 0,
                sysName: config.sysName || '',
                hardware: config.hardware || '',
                os: config.os || '',
                activeTab: config.active_tab || 'snmp',
                activeMethods: config.active_methods || [],
                methods: config.methods || {},
                allTypes: config.all_types || [],
                loading: false,
                errors: {},
                generalError: '',
                unreachableDialog: false,
                unreachableMessage: '',
                unreachableDetails: '',

                get topErrors() {
                    if (this.generalError) {
                        return [this.generalError];
                    }
                    const flat = [];
                    for (const key in this.errors) {
                        const val = this.errors[key];
                        if (Array.isArray(val)) {
                            flat.push(...val);
                        } else if (typeof val === 'string') {
                            flat.push(val);
                        }
                    }
                    return flat;
                },

                hasMethodErrors(type) {
                    return Object.keys(this.errors || {}).some(k => k.startsWith(`polling_methods.${type}.`));
                },

                addAnyway() {
                    this.unreachableDialog = false;
                    this.submitForm(true);
                },

                computedDisplayName: '',
                hasPlaceholders: false,
                previewDebounceTimer: null,

                init() {
                    this.$watch('hostname', () => this.queuePreview());
                    this.$watch('sysName', () => this.queuePreview());
                    this.$watch('display_template', () => this.queuePreview());
                    this.fetchPreview();
                },

                queuePreview() {
                    clearTimeout(this.previewDebounceTimer);
                    this.previewDebounceTimer = setTimeout(() => this.fetchPreview(), 150);
                },

                fetchPreview() {
                    const isIp = (str) => {
                        if (!str) return false;
                        return /^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/.test(str) || str.includes(':');
                    };

                    const host = this.hostname?.trim() ?? '';
                    const sys = this.sysName?.trim() ?? '';
                    const isHostIp = isIp(host);

                    const values = {
                        hostname: host || '___PLACEHOLDER_HOSTNAME___',
                        sysName: sys || '___PLACEHOLDER_SYSNAME___',
                        sysName_fallback: sys || (host && !isHostIp ? host : '___PLACEHOLDER_SYSNAME_FALLBACK___'),
                        ip: isHostIp ? host : '___PLACEHOLDER_IP___',
                    };

                    axios.post(@js(route('ajax.template.preview')), {
                        template: this.activeDisplayTemplate,
                        variables: values,
                    }).then(({ data }) => {
                        this.renderPreview(data?.preview ?? '');
                    }).catch(() => {
                        // ignore error
                    });
                },

                renderPreview(preview) {
                    const target = this.$refs.previewEl;
                    if (!target) return;

                    target.replaceChildren();

                    const placeholderPattern = /___PLACEHOLDER_(HOSTNAME|SYSNAME_FALLBACK|SYSNAME|IP)___/gi;
                    let hasPlaceholders = false;
                    let lastIndex = 0;
                    let match;

                    while ((match = placeholderPattern.exec(preview)) !== null) {
                        if (match.index > lastIndex) {
                            target.append(document.createTextNode(preview.slice(lastIndex, match.index)));
                        }

                        hasPlaceholders = true;
                        const span = document.createElement('span');
                        span.className = 'tw:px-1 tw:py-0.5 tw:rounded tw:bg-amber-100/90 tw:dark:bg-amber-900/50 tw:text-amber-800 tw:dark:text-amber-300 tw:border tw:border-dashed tw:border-amber-400 tw:dark:border-amber-600';
                        span.title = @js(__('Placeholder for unpopulated field'));
                        span.textContent = `<${match[1].toLowerCase()}>`;
                        target.append(span);

                        lastIndex = placeholderPattern.lastIndex;
                    }

                    if (lastIndex < preview.length) {
                        target.append(document.createTextNode(preview.slice(lastIndex)));
                    }

                    this.hasPlaceholders = hasPlaceholders;
                },

                get activeDisplayTemplate() {
                    return (this.display_template && this.display_template.trim() !== '')
                        ? this.display_template.trim()
                        : this.defaultDisplayTemplate;
                },

                get isCustomDisplayTemplate() {
                    return !!(this.display_template && this.display_template.trim() !== '');
                },

                get addableRemaining() {
                    return this.allTypes.filter(m => !this.activeMethods.includes(m.type));
                },

                addMethod(type) {
                    if (!this.activeMethods.includes(type)) {
                        this.activeMethods.push(type);
                        this.activeTab = type;
                    }
                },

                removeMethod(type) {
                    this.activeMethods = this.activeMethods.filter(t => t !== type);
                    if (this.activeTab === type) {
                        this.activeTab = this.activeMethods[0] ?? '';
                    }
                },

                async submitForm(force = false) {
                    if (this.loading) return;
                    this.loading = true;
                    this.errors = {};
                    this.generalError = '';

                    const pollingMethods = {};
                    for (const type of this.activeMethods) {
                        const m = this.methods[type] || {};
                        const methodPayload = {
                            active: 1,
                            validate: m.validate ? 1 : 0,
                            affects_availability: m.affects_availability ? 1 : 0,
                            secret_mode: m.secret_mode,
                            settings: m.settingsData || {},
                        };

                        if (m.secret_mode === 'existing') {
                            methodPayload.secret_id = m.secret_id;
                        } else if (m.secret_mode === 'new') {
                            methodPayload.description = m.description;
                            methodPayload.secret_data = m.formData || {};
                        }

                        pollingMethods[type] = methodPayload;
                    }

                    const payload = {
                        hostname: this.hostname,
                        display_template: this.display_template,
                        poller_group: this.poller_group,
                        active_tab: this.activeTab,
                        active_methods: this.activeMethods,
                        polling_methods: pollingMethods,
                        sysName: this.sysName,
                        hardware: this.hardware,
                        os: this.os,
                        force_add: force ? 1 : 0,
                    };

                    try {
                        const { data } = await axios.post(config.store_url, payload);
                        if (data.redirect) {
                            window.location.href = data.redirect;
                            return;
                        }
                        this.generalError = @js(__('Failed to save device.'));
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    } catch (error) {
                        const data = error.response?.data ?? {};
                        if (data.status === 'unreachable' || data.status === 'duplicate') {
                            this.unreachableMessage = data.message || @js(__('poller.reachability_check_failed'));
                            this.unreachableDetails = data.error_details || '';
                            this.unreachableDialog = true;
                            return;
                        }
                        if (data.errors) {
                            this.errors = data.errors;
                            this.generalError = (Object.keys(data.errors).length === 0 && data.message) ? data.message : '';
                        } else {
                            this.generalError = data.message || (error.response ? @js(__('Failed to save device.')) : @js(__('An unexpected error occurred.')));
                        }
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    } finally {
                        this.loading = false;
                    }
                },
            }));
        });

    </script>
@endpush
