@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device">
        <x-device.edit-tabs :device="$device" />

        @if($configuredMethods->isNotEmpty())
            <div
                x-data="pollingTabs(@js($tabsConfig))"
                class="tw:flex tw:flex-col tw:md:flex-row tw:gap-6 tw:mt-6"
            >
                <!-- Left Tabs -->
                <div class="tw:w-full tw:md:w-1/4 tw:shrink-0">
                    <ul class="tw:flex tw:flex-col tw:space-y-2">
                        @foreach($allMethods as $method)
                            <li x-show="activeMethods.includes('{{ $method['type'] }}')"
                                :class="activeTab === '{{ $method['type'] }}' ? 'tw:bg-blue-600 tw:text-white! tw:border-blue-600 tw:dark:bg-blue-700' : 'tw:text-gray-700 tw:border-gray-200 tw:hover:bg-gray-50 tw:dark:text-dark-white-200 tw:dark:border-dark-gray-400 tw:dark:hover:bg-dark-gray-400'"
                                class="tw:flex tw:items-center tw:border tw:rounded-lg tw:shadow-sm tw:transition-colors tw:overflow-hidden"
                                x-cloak>
                                <button type="button" @click="activeTab = '{{ $method["type"] }}'"
                                        :class="activeTab === '{{ $method["type"] }}' ? 'tw:text-white!' : 'tw:text-gray-700 tw:dark:text-dark-white-200'"
                                        class="tw:flex-1 tw:text-left tw:px-4 tw:py-3 tw:font-medium tw:transition-colors tw:flex tw:items-center tw:gap-2">
                                    <i class="fa fa-fw {{ $method['icon'] }}"></i>
                                    <span class="tw:grow">{{ $method['label'] }}</span>
                                    <span x-show="dirtyMethods['{{ $method['type'] }}']"
                                          x-cloak
                                          class="tw:inline-block tw:w-2 tw:h-2 tw:rounded-full tw:bg-amber-400 tw:animate-pulse tw:shrink-0"
                                          title="{{ __('Unsaved changes pending') }}">
                                    </span>
                                </button>
                                <div class="tw:px-3 tw:py-3 tw:shrink-0 tw:flex tw:items-center" x-show="methods['{{ $method['type'] }}']?.configured" x-cloak>
                                    <template x-if="!methods['{{ $method['type'] }}']?.enabled">
                                        <span class="fa-stack" style="font-size: 11px;" title="{{ __('Status: Disabled') }}">
                                            <i class="fa fa-circle fa-stack-2x tw:text-gray-400 tw:dark:text-dark-gray-300"></i>
                                            <i class="fa fa-minus fa-stack-1x tw:text-white"></i>
                                        </span>
                                    </template>
                                    <template x-if="methods['{{ $method['type'] }}']?.enabled && methods['{{ $method['type'] }}']?.lastCheckSuccessful === true">
                                        <span class="fa-stack" style="font-size: 11px;" title="{{ __('Status: Successful') }}">
                                            <i class="fa fa-circle fa-stack-2x tw:text-[#5cb85c]"></i>
                                            <i class="fa fa-check fa-stack-1x tw:text-white"></i>
                                        </span>
                                    </template>
                                    <template x-if="methods['{{ $method['type'] }}']?.enabled && methods['{{ $method['type'] }}']?.lastCheckSuccessful === false">
                                        <span class="fa-stack" style="font-size: 11px;" title="{{ __('Status: Failed') }}">
                                            <i class="fa fa-circle fa-stack-2x tw:text-red-500"></i>
                                            <i class="fa fa-times fa-stack-1x tw:text-white"></i>
                                        </span>
                                    </template>
                                    <template x-if="methods['{{ $method['type'] }}']?.enabled && (methods['{{ $method['type'] }}']?.lastCheckSuccessful === null || methods['{{ $method['type'] }}']?.lastCheckSuccessful === undefined)">
                                        <span class="fa-stack" style="font-size: 11px;" title="{{ __('Status: Unknown') }}">
                                            <i class="fa fa-circle fa-stack-2x tw:text-gray-400 tw:dark:text-dark-white-400"></i>
                                            <i class="fa fa-question fa-stack-1x tw:text-white"></i>
                                        </span>
                                    </template>
                                </div>
                                <button type="button"
                                        x-show="!methods['{{ $method['type'] }}']?.configured"
                                        x-cloak
                                        @click="removeMethod('{{ $method['type'] }}')"
                                        :class="activeTab === '{{ $method['type'] }}' ? 'tw:text-blue-200 tw:hover:text-white' : 'tw:text-gray-400 tw:hover:text-red-500'"
                                        class="tw:px-3 tw:py-3 tw:shrink-0 tw:transition-colors tw:text-base"
                                        title="{{ __('Remove') }}"
                                        aria-label="{{ __('Remove') }} {{ $method['label'] }}">
                                    <i class="fa fa-times"></i>
                                </button>
                            </li>
                        @endforeach

                        {{-- Add polling type dropdown --}}
                        <x-device.polling.add-type-select />
                    </ul>
                </div>

                <!-- Right Content -->
                <div class="tw:w-full tw:md:w-3/4 tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:rounded-lg tw:shadow-sm tw:p-6 tw:grow tw:bg-white tw:dark:bg-dark-gray-500">
                    <div x-show="noAvailabilitySources" x-cloak class="tw:mb-6 tw:bg-yellow-50 tw:dark:bg-transparent tw:border tw:border-yellow-200 tw:dark:border-yellow-800 tw:p-4 tw:rounded-lg" x-transition>
                        <div class="tw:flex tw:items-start">
                            <i class="tw:text-yellow-600 tw:dark:text-yellow-500 tw:mt-1 tw:mr-3 fa fa-exclamation-triangle fa-2x"></i>
                            <div>
                                <p class="tw:font-semibold tw:text-yellow-800 tw:dark:text-yellow-400">
                                    {{ __('device.no_availability') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    @php
                        $formLabels = [
                            'saveFailed' => __('Failed to save settings'),
                            'saved' => __('Settings saved'),
                            'saveError' => __('An error occurred while saving.'),
                            'confirmRemove' => __('Are you sure you want to remove this polling method?'),
                            'removeFailed' => __('Failed to remove polling method'),
                            'removed' => __('Polling method removed'),
                            'removeError' => __('An error occurred while removing.'),
                            'unknownSecret' => __('Unknown secret'),
                            'unreachable' => __('poller.reachability_check_failed'),
                            'sharedWith' => [
                                'one' => trans_choice('Shared — used by :count other device.|Shared — used by :count other devices.', 1, ['count' => ':count']),
                                'other' => trans_choice('Shared — used by :count other device.|Shared — used by :count other devices.', 2, ['count' => ':count']),
                            ],
                            'updateShared' => [
                                'one' => trans_choice('Update the shared secret (:count other device affected)|Update the shared secret (:count other devices affected)', 1, ['count' => ':count']),
                                'other' => trans_choice('Update the shared secret (:count other device affected)|Update the shared secret (:count other devices affected)', 2, ['count' => ':count']),
                            ],
                            'sharedGuard' => __(':secret is shared with other devices. Choose how to apply your changes:'),
                        ];
                    @endphp
                    @foreach($allMethods as $method)
                        <div x-data="pollingMethodForm(@js([
                                'type' => $method['type'],
                                'configured' => (bool) $method['configured'],
                                'enabled' => (bool) $method['enabled'],
                                'affectsAvailability' => (bool) $method['affects_availability'],
                                'currentSecretId' => (string) ($method['secret']['id'] ?? ''),
                                'secretDescription' => $method['secret']['description'] ?? '',
                                'newSecretDescription' => $method['default_secret_description'],
                                'secretFieldLabels' => (object) collect($method['schema_fields'] ?? [])->mapWithKeys(fn (array $f) => [$f['key'] => $f['label'] ?? $f['key']])->all(),
                                'formData' => (object) ($method['schema_defaults'] ?? []),
                                'settingsData' => (object) ($method['settings'] ?? []),
                                'updateUrl' => route('device.edit.polling.update', ['device' => $device, 'methodType' => $method['type']]),
                                'storeUrl' => route('device.edit.polling.store', ['device' => $device]),
                                'secretUrl' => route('secrets.show', ['secret' => '__ID__']),
                                'destroyUrl' => route('device.edit.polling.destroy', ['device' => $device, 'methodType' => $method['type']]),
                                'labels' => $formLabels,
                            ]))"
                             x-show="activeTab === '{{ $method["type"] }}' && activeMethods.includes('{{ $method["type"] }}')"
                             x-cloak>

                            <div class="tw:flex tw:items-center tw:justify-between tw:mb-6 tw:border-b tw:pb-3 tw:dark:border-dark-gray-400">
                                <div class="tw:flex tw:items-center tw:gap-3">
                                    <h3 class="tw:text-2xl tw:font-semibold tw:text-gray-800 tw:dark:text-dark-white-100 tw:m-0">{{ $method['label'] }} {{ __('Settings') }}</h3>
                                    <span x-show="isDirty" x-cloak class="tw:inline-flex tw:items-center tw:gap-1.5 tw:px-2.5 tw:py-0.5 tw:rounded-full tw:text-xs tw:font-medium tw:bg-amber-100 tw:text-amber-800 tw:dark:bg-amber-900/50 tw:dark:text-amber-300">
                                        <span class="tw:w-1.5 tw:h-1.5 tw:rounded-full tw:bg-amber-500 tw:animate-pulse"></span>
                                        {{ __('Unsaved changes pending') }}
                                    </span>
                                </div>
                            </div>

                            <form x-ref="form" method="POST" :action="configured ? updateUrl : storeUrl" @submit.prevent="saveForm()">
                                @csrf
                                <input type="hidden" name="_method" value="PUT" :disabled="!configured">
                                <input type="hidden" name="method_type" value="{{ $method['type'] }}" :disabled="configured">
                                <input type="hidden" name="tab" value="{{ $method['type'] }}">

                                {{-- Method Options --}}
                                <div class="tw:bg-gray-50 tw:dark:bg-dark-gray-300 tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:rounded-xl tw:p-5 tw:mb-6">
                                    <h4 class="tw:font-semibold tw:text-sm tw:uppercase tw:tracking-wider tw:mb-4 tw:text-gray-500 tw:dark:text-dark-white-300">{{ __('Method Options') }}</h4>
                                    <div class="tw:grid tw:grid-cols-1 tw:md:grid-cols-2 tw:gap-4 tw:max-w-2xl">
                                        <x-device.polling.toggle name="enabled" model="enabled" :label="__('Enabled')" />
                                        <x-device.polling.toggle name="affects_availability" model="affectsAvailability" :label="__('poller.affects_availability')" />
                                    </div>
                                </div>

                                {{-- Credentials section --}}
                                @if(!empty($method['schema_fields']))
                                    <div x-show="enabled" class="tw:bg-gray-50 tw:dark:bg-dark-gray-300 tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:rounded-xl tw:p-5 tw:mb-6">
                                        <h4 class="tw:font-semibold tw:text-sm tw:uppercase tw:tracking-wider tw:mb-4 tw:text-gray-500 tw:dark:text-dark-white-300">{{ __('Credentials') }}</h4>
                                        <input type="hidden" name="secret_mode" :value="secretMode">

                                        {{-- Configured credentials section --}}
                                        <div x-show="configured" class="tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:p-5 tw:rounded-lg tw:text-sm tw:bg-white tw:dark:bg-dark-gray-500">
                                            <input type="hidden" name="secret_id" :value="selectedSecretId" :disabled="!configured && credentialMode !== 'existing'">

                                            {{-- Secret picker --}}
                                            <div class="tw:mb-4 form-group" :class="(errors && errors['secret_id']) ? 'has-error' : ''">
                                                <label class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:dark:text-dark-white-200 tw:mb-1">{{ __('Secret') }}</label>
                                                <div class="tw:flex tw:items-center tw:gap-2 tw:max-w-xl">
                                                    <x-select2
                                                        :id="'secret-select-' . $method['type']"
                                                        type="secret"
                                                        :data="['secret_type' => $method['type']]"
                                                        :placeholder="$method['secret'] ? __('Select Secret') : __('No secret - default credentials')"
                                                        :allow-clear="false"
                                                        x-model="selectedSecretId"
                                                        x-ref="secretSelect"
                                                        @change="onSecretChange()"
                                                        class="tw:grow"
                                                    >
                                                        <option value="" {{ $method['secret'] ? '' : 'selected' }}></option>
                                                        @foreach(($availableSecrets[$method['type']] ?? collect()) as $secret)
                                                            <option value="{{ (string) $secret->id }}" {{ (string) ($method['secret']['id'] ?? '') === (string) $secret->id ? 'selected' : '' }}>
                                                                {{ $secret->description }}
                                                            </option>
                                                        @endforeach
                                                    </x-select2>
                                                    <button
                                                        type="button"
                                                        class="btn btn-default btn-sm tw:shrink-0"
                                                        :class="showSecretInfo ? 'tw:bg-gray-200 tw:dark:bg-dark-gray-400' : ''"
                                                        :disabled="!selectedSecretId"
                                                        @click="showSecretInfo = !showSecretInfo; if (showSecretInfo) { isEditingSecret = false; }"
                                                        title="{{ __('View secret details') }}"
                                                        aria-label="{{ __('View secret details') }}"
                                                    >
                                                        <i class="fa fa-info-circle"></i>
                                                    </button>
                                                    <button
                                                        type="button"
                                                        class="btn btn-default btn-sm tw:shrink-0"
                                                        :class="isEditingSecret ? 'tw:bg-gray-200 tw:dark:bg-dark-gray-400' : ''"
                                                        :disabled="!selectedSecretId"
                                                        @click="toggleEditSecret()"
                                                        title="{{ __('Edit secret') }}"
                                                        aria-label="{{ __('Edit secret') }}"
                                                    >
                                                        <i class="fa fa-edit"></i>
                                                    </button>
                                                </div>
                                                <template x-if="errors && errors['secret_id']">
                                                    <span class="help-block" x-text="errors['secret_id']?.[0]"></span>
                                                </template>

                                                <div x-show="showSecretInfo" x-cloak
                                                     class="tw:mt-3 tw:bg-gray-50 tw:dark:bg-dark-gray-400 tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:rounded-lg tw:p-3 tw:text-sm">
                                                    <div class="tw:font-semibold tw:text-gray-800 tw:dark:text-dark-white-100" x-text="selectedSecret?.description ?? labels.unknownSecret"></div>
                                                    <div class="tw:text-gray-500 tw:dark:text-dark-white-300 tw:mt-1">
                                                        <template x-if="isSharedSecret">
                                                            <span x-text="choice(labels.sharedWith, otherDevicesCount)"></span>
                                                        </template>
                                                        <template x-if="!isSharedSecret">
                                                            <span>{{ __('Only used by this device.') }}</span>
                                                        </template>
                                                    </div>
                                                    <div class="tw:text-gray-500 tw:dark:text-dark-white-300 tw:mt-1" x-show="fieldsSet.length">
                                                        {{ __('Fields set') }}: <span x-text="fieldsSet.map(k => secretFieldLabels[k] || k).join(', ')"></span>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Editing a secret's values --}}
                                            <div x-show="isEditingSecret" x-cloak>
                                                <fieldset :disabled="!configured || !isEditingSecret" class="tw:border-0 tw:p-0 tw:m-0">
                                                    <div class="form-group tw:max-w-md" :class="(errors && errors['description']) ? 'has-error' : ''">
                                                        <label class="control-label">{{ __('Secret Description') }}</label>
                                                        <input type="text" name="description" x-model="secretDescription" class="form-control" :disabled="!configured || !isEditingSecret">
                                                        <p class="tw:text-amber-600 tw:dark:text-amber-400 tw:text-xs tw:mt-1.5" x-show="showSharedGuard && updateMode === 'create' && secretDescription === (selectedSecret?.description ?? '')">
                                                            <i class="fa fa-info-circle tw:mr-1"></i> {{ __('Please update the description so it does not match the existing secret.') }}
                                                        </p>
                                                        <template x-if="errors && errors['description']">
                                                            <span class="help-block" x-text="errors['description']?.[0]"></span>
                                                        </template>
                                                    </div>

                                                    <x-field-schema-fields
                                                        :fields="$method['schema_fields']"
                                                        :method-type="$method['type']"
                                                        name-prefix="secret_data"
                                                        model-prefix="formData"
                                                        :check-can-unmask="true"
                                                        :grid="true" />

                                                    <div x-show="showSharedGuard" x-cloak class="tw:mb-5 tw:bg-red-50 tw:dark:bg-transparent tw:border tw:border-red-200 tw:dark:border-red-800 tw:p-4 tw:rounded-lg">
                                                        <div class="tw:flex tw:items-start">
                                                            <i class="fa fa-exclamation-triangle tw:text-red-600 tw:dark:text-red-500 tw:mt-1 tw:mr-3"></i>
                                                            <div>
                                                                <p class="tw:text-sm tw:font-medium tw:text-red-800 tw:dark:text-red-400 tw:mb-2"
                                                                   x-text="labels.sharedGuard.replace(':secret', selectedSecret?.description ?? '')"></p>
                                                                <div class="tw:flex tw:flex-col tw:gap-2">
                                                                    <label class="tw:flex tw:items-center tw:cursor-pointer">
                                                                        <input type="radio" value="create" x-model="updateMode" :disabled="!showSharedGuard" class="tw:w-4 tw:h-4 tw:text-[#337ab7] tw:border-gray-300 tw:focus:ring-[#337ab7] tw:mr-2">
                                                                        <span class="tw:text-gray-700 tw:dark:text-dark-white-200">{{ __('Create a new secret for this device only (recommended)') }}</span>
                                                                    </label>
                                                                    <label class="tw:flex tw:items-center tw:cursor-pointer">
                                                                        <input type="radio" value="update" x-model="updateMode" :disabled="!showSharedGuard" class="tw:w-4 tw:h-4 tw:text-[#337ab7] tw:border-gray-300 tw:focus:ring-[#337ab7] tw:mr-2">
                                                                        <span class="tw:text-gray-700 tw:dark:text-dark-white-200" x-text="choice(labels.updateShared, otherDevicesCount)"></span>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </fieldset>
                                            </div>
                                        </div>

                                        {{-- Unconfigured credentials section --}}
                                        <div x-show="!configured" class="tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:p-5 tw:rounded-lg tw:text-sm tw:bg-white tw:dark:bg-dark-gray-500">
                                            <div class="tw:flex tw:flex-wrap tw:gap-6 tw:mb-4">
                                                <label class="radio-inline">
                                                    <input type="radio" value="existing" x-model="credentialMode">
                                                    {{ __('Use Existing Secret') }}
                                                </label>
                                                <label class="radio-inline">
                                                    <input type="radio" value="new" x-model="credentialMode">
                                                    {{ __('Create New Secret') }}
                                                </label>
                                            </div>

                                            {{-- Existing secret picker --}}
                                            <div x-show="credentialMode === 'existing'" x-cloak class="tw:max-w-md tw:mb-0 form-group" :class="(errors && errors['secret_id']) ? 'has-error' : ''">
                                                <x-select2
                                                    :id="'secret-select-unconf-' . $method['type']"
                                                    :label="__('Select Secret')"
                                                    type="secret"
                                                    :data="['secret_type' => $method['type']]"
                                                    :placeholder="__('Select an existing secret...')"
                                                    :allow-clear="false"
                                                    x-model="selectedSecretId"
                                                    x-ref="unconfSecretSelect"
                                                    class="tw:max-w-md"
                                                >
                                                    @foreach($availableSecrets[$method['type']] ?? [] as $secret)
                                                        <option value="{{ (string) $secret->id }}">
                                                            {{ $secret->description }}
                                                        </option>
                                                    @endforeach
                                                </x-select2>
                                                <template x-if="errors && errors['secret_id']">
                                                    <span class="help-block" x-text="errors['secret_id']?.[0]"></span>
                                                </template>
                                            </div>

                                            {{-- New secret form --}}
                                            <div x-show="credentialMode === 'new'" x-cloak>
                                                <fieldset :disabled="configured || credentialMode !== 'new'" class="tw:border-0 tw:p-0 tw:m-0">
                                                    <x-device.polling.new-secret-fields
                                                        :method="$method"
                                                        name-prefix="secret_data"
                                                        model-prefix="formData"
                                                        description-name="description"
                                                        description-model="newSecretDescription"
                                                        :error-key="'description'"
                                                    />
                                                </fieldset>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- Settings Configuration --}}
                                <div x-show="enabled">
                                    <x-device.polling.settings
                                        :method="$method"
                                        name-prefix="settings"
                                        model-prefix="settingsData"
                                    />
                                </div>


                                <div class="tw:flex tw:items-center tw:justify-between tw:gap-4 tw:mt-6 tw:pt-6 tw:border-t tw:border-gray-200 tw:dark:border-dark-gray-400">
                                    <div class="tw:flex tw:items-center tw:gap-2">
                                        <template x-if="configured">
                                            <button type="submit" :disabled="!isDirty || loading" class="btn btn-primary tw:bg-blue-600 tw:border-blue-600 tw:hover:bg-blue-700" :class="(!isDirty) ? 'tw:opacity-50 tw:cursor-not-allowed' : ''">
                                                <template x-if="loading"><i class="fa fa-spinner fa-spin tw:mr-1"></i></template>
                                                <template x-if="!loading"><i class="fa fa-save tw:mr-1"></i></template>
                                                {{ __('Save Settings') }}
                                            </button>
                                        </template>
                                        <template x-if="!configured">
                                            <button type="submit" :disabled="loading" class="btn btn-success tw:bg-emerald-600 tw:border-emerald-600 tw:hover:bg-emerald-700">
                                                <template x-if="loading"><i class="fa fa-spinner fa-spin tw:mr-1"></i></template>
                                                <template x-if="!loading"><i class="fa fa-plus tw:mr-1"></i></template>
                                                {{ __('Add Polling Type') }}
                                            </button>
                                        </template>
                                    </div>

                                    <button type="button" class="btn btn-danger" x-show="configured" x-cloak @click="deleteMethod()">
                                        <i class="fa fa-trash tw:mr-1"></i> {{ __('Remove') }} {{ $method['label'] }}
                                    </button>
                                </div>
                            </form>

                            {{-- Reachability Failure Dialog --}}
                            <x-device.polling.validation-failed-modal
                                action-click="saveAnyway()"
                                :action-label="__('Save Anyway')"
                                action-icon="fa-save"
                            />
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <!-- No configured methods, just show the Add form -->
            <div class="tw:mt-6 tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:rounded-lg tw:shadow-sm tw:p-6 tw:max-w-4xl tw:mx-auto">
                @include('device.edit.includes.add-polling-type')
            </div>
        @endif
    </x-device.page>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('pollingTabs', (config) => ({
                activeTab: config.initialTab,
                activeMethods: config.activeMethods,
                methods: config.methods,
                allTypes: config.allTypes,
                dirtyMethods: {},
                setDirty(type, isDirty) {
                    this.dirtyMethods[type] = isDirty;
                },
                get noAvailabilitySources() {
                    return !Object.values(this.methods).some(m => m.configured && m.enabled && m.affectsAvailability);
                },
                get addableRemaining() {
                    return this.allTypes.filter(m => !this.activeMethods.includes(m.type));
                },
                addMethod(type) {
                    if (!this.activeMethods.includes(type)) {
                        this.activeMethods = [...this.activeMethods, type];
                        this.activeTab = type;
                    }
                },
                removeMethod(type) {
                    delete this.dirtyMethods[type];
                    this.activeMethods = this.activeMethods.filter(t => t !== type);
                    if (this.activeTab === type) {
                        this.activeTab = this.activeMethods[0] ?? '';
                    }
                },
                init() {
                    this.$watch('activeTab', (tab) => {
                        const url = new URL(window.location.href);
                        if (tab) {
                            url.searchParams.set('tab', tab);
                        } else {
                            url.searchParams.delete('tab');
                        }
                        window.history.replaceState({}, '', url.toString());
                    });
                },
            }));

            Alpine.data('pollingMethodForm', (config) => ({
                type: config.type,
                configured: config.configured,
                enabled: config.enabled,
                initialEnabled: config.enabled,
                affectsAvailability: config.affectsAvailability,
                initialAffectsAvailability: config.affectsAvailability,
                updateMode: 'update',
                credentialMode: 'existing',
                loading: false,
                currentSecretId: config.currentSecretId,
                selectedSecretId: config.currentSecretId,
                showSecretInfo: false,
                isEditingSecret: false,
                secretDescription: config.secretDescription,
                newSecretDescription: config.newSecretDescription,
                secrets: {}, // id => {description, usage_count, data}, loaded when selected
                secretUrl: config.secretUrl,
                secretFieldLabels: config.secretFieldLabels,
                formData: config.formData,
                settingsData: config.settingsData,
                initialSettingsData: JSON.parse(JSON.stringify(config.settingsData)),
                updateUrl: config.updateUrl,
                storeUrl: config.storeUrl,
                destroyUrl: config.destroyUrl,
                labels: config.labels,

                errors: {},
                unreachableDialog: false,
                unreachableMessage: '',
                unreachableDetails: '',

                get selectedSecret() {
                    return this.secrets[this.selectedSecretId] || null;
                },
                get fieldsSet() {
                    const data = this.selectedSecret?.data || {};
                    return Object.keys(data).filter(k => data[k] !== null && data[k] !== '');
                },
                get otherDevicesCount() {
                    const total = this.selectedSecret?.usage_count ?? 0;
                    const isCurrent = this.configured && (String(this.selectedSecretId) === String(this.currentSecretId));
                    return isCurrent ? Math.max(0, total - 1) : total;
                },
                get isSharedSecret() {
                    return this.otherDevicesCount > 0;
                },
                get secretDataChanged() {
                    if (!this.selectedSecret) { return false; }
                    return JSON.stringify(this.formData) !== JSON.stringify(this.secretFormData(this.selectedSecret));
                },
                get secretValuesChanged() {
                    if (!this.selectedSecret) { return false; }
                    return this.secretDataChanged
                        || this.secretDescription !== this.selectedSecret.description;
                },
                get secretMode() {
                    if (!this.configured) { return this.credentialMode; }
                    if (!this.isEditingSecret) { return this.selectedSecretId ? 'existing' : ''; }
                    return this.showSharedGuard && this.updateMode === 'create' ? 'new' : 'edit';
                },
                get showSharedGuard() {
                    return this.configured && this.isEditingSecret && this.isSharedSecret && this.secretDataChanged;
                },
                settingsChanged() {
                    const norm = (obj) => {
                        const out = {};
                        for (const k in obj || {}) {
                            const v = obj[k];
                            if (v !== '' && v !== null && v !== undefined) {
                                out[k] = String(v);
                            }
                        }
                        return out;
                    };
                    return JSON.stringify(norm(this.settingsData)) !== JSON.stringify(norm(this.initialSettingsData));
                },
                get isDirty() {
                    if (!this.configured) { return true; }
                    return this.enabled !== this.initialEnabled
                        || this.affectsAvailability !== this.initialAffectsAvailability
                        || this.selectedSecretId !== this.currentSecretId
                        || this.secretValuesChanged
                        || this.settingsChanged();
                },
                choice(forms, count) {
                    return (count === 1 ? forms.one : forms.other).replace(':count', count);
                },
                async loadSecret(id) {
                    if (!id) {
                        return null;
                    }
                    if (!this.secrets[id]) {
                        try {
                            const { data } = await axios.get(this.secretUrl.replace('__ID__', id));
                            this.secrets = { ...this.secrets, [id]: data };
                        } catch (error) {
                            return null; // not cached, retried on next selection
                        }
                    }
                    return this.secrets[id];
                },
                secretFormData(secret) {
                    const data = secret?.data || {};
                    return Object.fromEntries(Object.keys(this.secretFieldLabels).map(k => [k, data[k] === null || data[k] === undefined ? '' : String(data[k])]));
                },
                async onSecretChange() {
                    const id = this.selectedSecretId;
                    const secret = await this.loadSecret(id);
                    if (id !== this.selectedSecretId) {
                        return; // superseded by a newer selection
                    }
                    this.formData = this.secretFormData(secret);
                    this.secretDescription = secret?.description ?? '';
                    this.showSecretInfo = false;
                    this.updateMode = this.showSharedGuard ? 'create' : 'update';
                },
                toggleEditSecret() {
                    this.isEditingSecret = !this.isEditingSecret;
                    if (this.isEditingSecret) { this.showSecretInfo = false; }
                },
                saveAnyway() {
                    this.unreachableDialog = false;
                    this.saveForm(true);
                },
                async saveForm(force = false) {
                    if (this.loading) { return; }
                    this.loading = true;
                    this.errors = {};
                    const formData = new FormData(this.$refs.form);
                    if (force) {
                        formData.set('force_save', '1');
                    }
                    try {
                        const { data } = await axios.post(this.configured ? this.updateUrl : this.storeUrl, formData);
                        toastr.success(data.message || this.labels.saved);
                        if (data.method) {
                            await this.applySavedMethod(data.method);
                        }
                        this.setDirty(this.type, false);
                    } catch (error) {
                        const response = error.response?.data;
                        if (response?.status === 'unreachable') {
                            this.unreachableMessage = response.message || this.labels.unreachable;
                            this.unreachableDetails = response.error_details || '';
                            this.unreachableDialog = true;
                        } else if (response?.errors) {
                            this.errors = response.errors;
                        } else {
                            toastr.error(response?.message || (error.response ? this.labels.saveFailed : this.labels.saveError));
                        }
                    } finally {
                        this.loading = false;
                    }
                },
                async applySavedMethod(method) {
                    this.configured = method.configured;
                    this.isEditingSecret = false;
                    this.showSecretInfo = false;
                    this.initialEnabled = method.enabled;
                    this.enabled = method.enabled;
                    this.initialAffectsAvailability = method.affects_availability;
                    this.affectsAvailability = method.affects_availability;
                    this.currentSecretId = String(method.secret?.id ?? '');
                    this.selectedSecretId = this.currentSecretId;
                    this.newSecretDescription = method.default_secret_description ?? this.newSecretDescription;
                    this.secrets = {}; // saved secrets may have changed
                    await this.onSecretChange();
                    this.settingsData = { ...(method.settings ?? {}) };
                    this.initialSettingsData = { ...(method.settings ?? {}) };

                    Object.assign(this.methods[this.type], {
                        configured: true,
                        enabled: method.enabled,
                        affectsAvailability: method.affects_availability,
                        lastCheckSuccessful: method.last_check_successful,
                    });

                    // a newly created secret is not in the select yet
                    if (method.secret && this.$refs.secretSelect) {
                        const secretId = String(method.secret.id);
                        const $select = $(this.$refs.secretSelect);
                        $select.find(`option[value="${secretId}"]`).remove();
                        $select.append(new Option(method.secret.description, secretId, true, true)).trigger('change');
                    }
                },
                async deleteMethod() {
                    if (!confirm(this.labels.confirmRemove)) {
                        return;
                    }
                    let data;
                    try {
                        ({ data } = await axios.delete(this.destroyUrl));
                    } catch (error) {
                        toastr.error(error.response?.data?.message || (error.response ? this.labels.removeFailed : this.labels.removeError));
                        return;
                    }
                    toastr.success(data.message || this.labels.removed);
                    this.configured = false;
                    this.newSecretDescription = data.default_secret_description ?? this.newSecretDescription;
                    Object.assign(this.methods[this.type], { configured: false, lastCheckSuccessful: null });
                    this.removeMethod(this.type);
                },
                init() {
                    if (this.currentSecretId) {
                        this.onSecretChange();
                    }
                    this.setDirty(this.type, this.isDirty);
                    this.$watch('isDirty', (val) => this.setDirty(this.type, val));
                    this.$watch('showSharedGuard', (val) => {
                        this.updateMode = val ? 'create' : 'update';
                    });
                },
            }));
        });
    </script>
@endpush
