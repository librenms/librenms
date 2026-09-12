@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device">
        <x-device.edit-tabs :device="$device" />

        @if($configuredMethods->isNotEmpty())
            <div
                x-data="pollingTabs('{{ request('tab') }}', @js($configuredMethods->pluck('type')->values()), '{{ $defaultTab }}', @js($allMethods->mapWithKeys(fn($m) => [$m['type'] => ['enabled' => (bool)$m['enabled'], 'affectsAvailability' => (bool)$m['affects_availability'], 'credential_mode' => 'existing', 'formData' => $m['schema_defaults'] ?? [], 'settingsData' => $m['settings'] ?? []]])), {{ $errors->any() ? 'true' : 'false' }}, @js($unconfiguredMethods->map(fn($m) => ['type' => $m['type'], 'label' => $m['label']])->values()))"
                class="tw:flex tw:flex-col tw:md:flex-row tw:gap-6 tw:mt-6"
            >
                <!-- Left Tabs -->
                <div class="tw:w-full tw:md:w-1/4 tw:shrink-0">
                    <ul class="tw:flex tw:flex-col tw:space-y-2">
                        @foreach($allMethods as $method)
                            <li x-show="activeMethods.includes('{{ $method['type'] }}')"
                                :class="activeTab === '{{ $method['type'] }}' ? 'tw:bg-blue-600 tw:text-white! tw:border-blue-600 tw:dark:bg-blue-700' : 'tw:text-gray-700 tw:border-gray-200 tw:hover:bg-gray-50 tw:dark:text-dark-white-200 tw:dark:border-dark-gray-400 tw:dark:hover:bg-dark-gray-400'"
                                class="tw:flex tw:items-center tw:border tw:rounded-lg tw:shadow-sm tw:transition-colors tw:overflow-hidden"
                                style="display: none;">
                                <button type="button" @click="activeTab = '{{ $method["type"] }}'"
                                        :class="activeTab === '{{ $method["type"] }}' ? 'tw:text-white!' : 'tw:text-gray-700 tw:dark:text-dark-white-200'"
                                        class="tw:flex-1 tw:text-left tw:px-4 tw:py-3 tw:font-medium tw:transition-colors tw:flex tw:items-center tw:gap-2">
                                    <i class="fa fa-fw {{ $method['icon'] }}"></i>
                                    <span class="tw:grow">{{ $method['label'] }}</span>
                                    <span x-show="dirtyMethods['{{ $method['type'] }}']"
                                          style="display: none;"
                                          class="tw:inline-block tw:w-2 tw:h-2 tw:rounded-full tw:bg-amber-400 tw:animate-pulse tw:shrink-0"
                                          title="{{ __('Unsaved changes pending') }}">
                                    </span>
                                </button>
                                @if($method['configured'])
                                    <div class="tw:px-3 tw:py-3 tw:shrink-0 tw:flex tw:items-center">
                                        @if($method['last_check_successful'] === true)
                                            <span class="fa-stack" style="width: 1.28571429em; height: 1.28571429em; line-height: 1.28571429em;" title="{{ __('Status: Successful') }}">
                                                <i class="fa fa-circle fa-stack-1x tw:text-white"></i>
                                                <i class="fa fa-check-circle fa-stack-1x tw:text-[#5cb85c]"></i>
                                            </span>
                                        @elseif($method['last_check_successful'] === false)
                                            <span class="fa-stack" style="width: 1.28571429em; height: 1.28571429em; line-height: 1.28571429em;" title="{{ __('Status: Failed') }}">
                                                <i class="fa fa-circle fa-stack-1x tw:text-white"></i>
                                                <i class="fa fa-times-circle fa-stack-1x tw:text-red-500"></i>
                                            </span>
                                        @else
                                            <i class="fa fa-fw fa-circle-o tw:text-gray-400 tw:dark:text-dark-white-400" title="{{ __('Status: Unknown') }}"></i>
                                        @endif
                                    </div>
                                @else
                                    <button type="button"
                                            @click="removeMethod('{{ $method['type'] }}')"
                                            :class="activeTab === '{{ $method['type'] }}' ? 'tw:text-blue-200 tw:hover:text-white' : 'tw:text-gray-400 tw:hover:text-red-500'"
                                            class="tw:px-3 tw:py-3 tw:shrink-0 tw:transition-colors"
                                            title="{{ __('Remove') }}">
                                        <i class="fa fa-times"></i>
                                    </button>
                                @endif
                            </li>
                        @endforeach

                        {{-- Add polling type dropdown --}}
                        <x-device.polling.add-type-select />
                    </ul>
                </div>

                <!-- Right Content -->
                <div class="tw:w-full tw:md:w-3/4 tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:rounded-lg tw:shadow-sm tw:p-6 tw:grow tw:bg-white tw:dark:bg-dark-gray-500">
                    <div x-show="noAvailabilitySources" style="display: none;" class="tw:mb-6 tw:bg-yellow-50 tw:dark:bg-transparent tw:border tw:border-yellow-200 tw:dark:border-yellow-800 tw:p-4 tw:rounded-lg" x-transition>
                        <div class="tw:flex tw:items-start">
                            <i class="tw:text-yellow-600 tw:dark:text-yellow-500 tw:mt-1 tw:mr-3 fa fa-exclamation-triangle fa-2x"></i>
                            <div>
                                <p class="tw:font-semibold tw:text-yellow-800 tw:dark:text-yellow-400">
                                    {{ __('device.no_availability') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    @foreach($allMethods as $method)
                        <div x-data="pollingMethodForm({
                                type: '{{ $method['type'] }}',
                                configured: {{ $method['configured'] ? 'true' : 'false' }},
                                enabled: {{ $method['enabled'] ? 'true' : 'false' }},
                                affectsAvailability: {{ $method['affects_availability'] ? 'true' : 'false' }},
                                currentSecretId: '{{ (string) ($method['secret']?->id ?? '') }}',
                                selectedSecretId: '{{ (string) ($method['secret']?->id ?? '') }}',
                                secretDescription: @js($method['secret']?->description ?? ''),
                                secretMeta: @js((object) array_replace(
                                    $method['secret_meta'] ?? [],
                                    $method['secret']
                                        ? [(string) $method['secret']->id => [
                                            'description' => $method['secret']->description,
                                            'usage_count' => $method['usage_count'] ?? 0,
                                        ]]
                                        : []
                                )),
                                secretFormDataById: @js((object) array_replace(
                                    $method['secret_form_data_by_id'] ?? [],
                                    $method['secret']
                                        ? [(string) $method['secret']->id => (object) ($method['secret_form_data'] ?? $method['schema_defaults'] ?? [])]
                                        : []
                                )),
                                secretFieldLabels: @js((object) collect($method['schema_fields'] ?? [])->mapWithKeys(fn (array $f) => [$f['key'] => $f['label'] ?? $f['key']])),
                                formData: @js((object) ($method['secret_form_data'] ?? $method['schema_defaults'] ?? [])),
                                settingsData: @js((object) ($method['settings'] ?? [])),
                                updateUrl: '{{ route('device.edit.polling.update', ['device' => $device, 'methodType' => $method['type']]) }}',
                                storeUrl: '{{ route('device.edit.polling.store', ['device' => $device]) }}',
                                destroyUrl: '{{ route('device.edit.polling.destroy', ['device' => $device, 'methodType' => $method['type']]) }}',
                                labels: {
                                    saveFailed: @js(__('Failed to save settings')),
                                    saved: @js(__('Settings saved')),
                                    saveError: @js(__('An error occurred while saving.')),
                                    confirmRemove: @js(__('Are you sure you want to remove this polling method?')),
                                    removeFailed: @js(__('Failed to remove polling method')),
                                    removed: @js(__('Polling method removed')),
                                    removeError: @js(__('An error occurred while removing.'))
                                }
                            })"
                             x-show="activeTab === '{{ $method["type"] }}' && activeMethods.includes('{{ $method["type"] }}')"
                             style="display: none;"
                             x-transition>

                            <div class="tw:flex tw:items-center tw:justify-between tw:mb-6 tw:border-b tw:pb-3 tw:dark:border-dark-gray-400">
                                <div class="tw:flex tw:items-center tw:gap-3">
                                    <h3 class="tw:text-2xl tw:font-semibold tw:text-gray-800 tw:dark:text-dark-white-100 tw:m-0">{{ $method['label'] }} {{ __('Settings') }}</h3>
                                    <span x-show="isDirty" style="display: none;" class="tw:inline-flex tw:items-center tw:gap-1.5 tw:px-2.5 tw:py-0.5 tw:rounded-full tw:text-xs tw:font-medium tw:bg-amber-100 tw:text-amber-800 tw:dark:bg-amber-900/50 tw:dark:text-amber-300">
                                        <span class="tw:w-1.5 tw:h-1.5 tw:rounded-full tw:bg-amber-500 tw:animate-pulse"></span>
                                        {{ __('Unsaved changes pending') }}
                                    </span>
                                </div>
                            </div>

                            <form x-ref="form" method="POST" :action="configured ? updateUrl : storeUrl" @submit.prevent="saveForm($event)">
                                @csrf
                                <input type="hidden" name="_method" value="PUT" :disabled="!configured">
                                <input type="hidden" name="method_type" value="{{ $method['type'] }}" :disabled="configured">
                                <input type="hidden" name="tab" value="{{ $method['type'] }}">

                                {{-- Method Options --}}
                                <div class="tw:bg-gray-50 tw:dark:bg-dark-gray-300 tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:rounded-xl tw:p-5 tw:mb-6">
                                    <h4 class="tw:font-semibold tw:text-sm tw:uppercase tw:tracking-wider tw:mb-4 tw:text-gray-500 tw:dark:text-dark-white-300">{{ __('Method Options') }}</h4>
                                    <div class="tw:grid tw:grid-cols-1 tw:md:grid-cols-2 tw:gap-4 tw:max-w-2xl">
                                        <label class="tw:flex tw:items-center tw:cursor-pointer tw:group tw:px-4 tw:py-3 tw:rounded-lg tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:bg-white tw:dark:bg-dark-gray-500 tw:w-full">
                                            <div class="tw:relative tw:shrink-0">
                                                <input type="hidden" name="enabled" value="0">
                                                <input type="checkbox" name="enabled" value="1" class="tw:sr-only" x-model="enabled">
                                                <div class="tw:block tw:w-12 tw:h-7 tw:rounded-full tw:transition-colors tw:duration-200" :class="enabled ? 'tw:bg-blue-600' : 'tw:bg-gray-300 tw:dark:bg-dark-gray-400'"></div>
                                                <div class="tw:absolute tw:left-0.5 tw:top-0.5 tw:w-6 tw:h-6 tw:rounded-full tw:transition-transform tw:duration-200 tw:bg-white tw:shadow-sm" :class="enabled ? 'tw:translate-x-5' : 'tw:translate-x-0'"></div>
                                            </div>
                                            <span class="tw:ml-3 tw:font-medium tw:text-gray-700 tw:dark:text-dark-white-200">{{ __('Enabled') }}</span>
                                        </label>

                                        <label class="tw:flex tw:items-center tw:cursor-pointer tw:group tw:px-4 tw:py-3 tw:rounded-lg tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:bg-white tw:dark:bg-dark-gray-500 tw:w-full">
                                            <div class="tw:relative tw:shrink-0">
                                                <input type="hidden" name="affects_availability" value="0">
                                                <input type="checkbox" name="affects_availability" value="1" class="tw:sr-only" x-model="affectsAvailability">
                                                <div class="tw:block tw:w-12 tw:h-7 tw:rounded-full tw:transition-colors tw:duration-200" :class="affectsAvailability ? 'tw:bg-blue-600' : 'tw:bg-gray-300 tw:dark:bg-dark-gray-400'"></div>
                                                <div class="tw:absolute tw:left-0.5 tw:top-0.5 tw:w-6 tw:h-6 tw:rounded-full tw:transition-transform tw:duration-200 tw:bg-white tw:shadow-sm" :class="affectsAvailability ? 'tw:translate-x-5' : 'tw:translate-x-0'"></div>
                                            </div>
                                            <span class="tw:ml-3 tw:font-medium tw:text-gray-700 tw:dark:text-dark-white-200">{{ __('poller.affects_availability') }}</span>
                                        </label>
                                    </div>
                                </div>

                                {{-- Credentials section --}}
                                @if(!empty($method['schema_fields']))
                                    @if($method['configured'])
                                        <div x-show="enabled" class="tw:bg-gray-50 tw:dark:bg-dark-gray-300 tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:rounded-xl tw:p-5 tw:mb-6">
                                            <h4 class="tw:font-semibold tw:text-sm tw:uppercase tw:tracking-wider tw:mb-4 tw:text-gray-500 tw:dark:text-dark-white-300">{{ __('Credentials') }}</h4>

                                            <div class="tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:p-5 tw:rounded-lg tw:text-sm tw:bg-white tw:dark:bg-dark-gray-500">
                                                <input type="hidden" name="secret_id" :value="selectedSecretId">
                                                <input type="hidden" name="is_editing_secret" :value="isEditingSecret ? '1' : '0'">

                                                {{-- Secret picker --}}
                                                <div class="tw:mb-4 form-group" :class="(errors && errors['secret_id']) ? 'has-error' : ''">
                                                    <label class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:dark:text-dark-white-200 tw:mb-1">{{ __('Secret') }}</label>
                                                    <div class="tw:flex tw:items-center tw:gap-2 tw:max-w-xl">
                                                        <x-select2
                                                            :id="'secret-select-' . $method['type']"
                                                            type="secret"
                                                            :data="['secret_type' => $method['type']]"
                                                            :selected="$method['secret'] ? ['id' => (string) $method['secret']->id, 'text' => $method['secret']->description] : null"
                                                            :placeholder="__('Select Secret')"
                                                            :allow-clear="false"
                                                            x-model="selectedSecretId"
                                                            x-ref="secretSelect"
                                                            @change="onSecretChange()"
                                                            class="tw:grow"
                                                        >
                                                            @foreach(($availableSecrets[$method['type']] ?? collect()) as $secret)
                                                                <option value="{{ (string) $secret->id }}" {{ (string) ($method['secret']?->id ?? '') === (string) $secret->id ? 'selected' : '' }}>
                                                                    {{ $secret->description }}
                                                                </option>
                                                            @endforeach
                                                        </x-select2>
                                                        <button
                                                            type="button"
                                                            class="btn btn-default btn-sm tw:shrink-0"
                                                            :class="showSecretInfo ? 'tw:bg-gray-200 tw:dark:bg-dark-gray-400' : ''"
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

                                                    <div x-show="showSecretInfo" x-cloak style="display: none;"
                                                         class="tw:mt-3 tw:bg-gray-50 tw:dark:bg-dark-gray-400 tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:rounded-lg tw:p-3 tw:text-sm">
                                                        <div class="tw:font-semibold tw:text-gray-800 tw:dark:text-dark-white-100" x-text="selectedSecretMeta?.description ?? '{{ __('Unknown secret') }}'"></div>
                                                        <div class="tw:text-gray-500 tw:dark:text-dark-white-300 tw:mt-1">
                                                            <template x-if="isSharedSecret">
                                                                <span>{{ __('Shared — used by') }} <span x-text="otherDevicesCount"></span> {{ __('other device(s).') }}</span>
                                                            </template>
                                                            <template x-if="!isSharedSecret">
                                                                <span>{{ __('Only used by this device.') }}</span>
                                                            </template>
                                                        </div>
                                                        <div class="tw:text-gray-500 tw:dark:text-dark-white-300 tw:mt-1" x-show="Object.keys(secretFormDataById[selectedSecretId] || {}).length">
                                                            {{ __('Fields set') }}: <span x-text="Object.keys(secretFormDataById[selectedSecretId] || {}).map(k => secretFieldLabels[k] || k).join(', ')"></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Editing a secret's values --}}
                                                <div x-show="isEditingSecret" x-cloak style="display: none;">
                                                    <div class="form-group tw:max-w-md" :class="(errors && errors['description']) ? 'has-error' : ''">
                                                        <label class="control-label">{{ __('Secret Description') }}</label>
                                                        <input type="text" name="description" x-model="secretDescription" class="form-control">
                                                        <p class="tw:text-amber-600 tw:dark:text-amber-400 tw:text-xs tw:mt-1.5" x-show="showSharedGuard && updateMode === 'create' && secretDescription === (selectedSecretMeta?.description ?? '')">
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

                                                    <div x-show="showSharedGuard" x-cloak style="display: none;" class="tw:mb-5 tw:bg-red-50 tw:dark:bg-transparent tw:border tw:border-red-200 tw:dark:border-red-800 tw:p-4 tw:rounded-lg">
                                                        <div class="tw:flex tw:items-start">
                                                            <i class="fa fa-exclamation-triangle tw:text-red-600 tw:dark:text-red-500 tw:mt-1 tw:mr-3"></i>
                                                            <div>
                                                                <p class="tw:text-sm tw:font-medium tw:text-red-800 tw:dark:text-red-400 tw:mb-2">
                                                                    <span x-text="selectedSecretMeta?.description"></span>
                                                                    {{ __('is shared with other devices. Choose how to apply your changes:') }}
                                                                </p>
                                                                <div class="tw:flex tw:flex-col tw:gap-2">
                                                                    <label class="tw:flex tw:items-center tw:cursor-pointer">
                                                                        <input type="radio" name="secret_update_mode" value="create" x-model="updateMode" :disabled="!showSharedGuard" class="tw:w-4 tw:h-4 tw:text-[#337ab7] tw:border-gray-300 tw:focus:ring-[#337ab7] tw:mr-2">
                                                                        <span class="tw:text-gray-700 tw:dark:text-dark-white-200">{{ __('Create a new secret for this device only (recommended)') }}</span>
                                                                    </label>
                                                                    <label class="tw:flex tw:items-center tw:cursor-pointer">
                                                                        <input type="radio" name="secret_update_mode" value="update" x-model="updateMode" :disabled="!showSharedGuard" class="tw:w-4 tw:h-4 tw:text-[#337ab7] tw:border-gray-300 tw:focus:ring-[#337ab7] tw:mr-2">
                                                                        <span class="tw:text-gray-700 tw:dark:text-dark-white-200">
                                                                            {{ __('Update the shared secret') }}
                                                                            (<span x-text="otherDevicesCount"></span> {{ __('other device(s) affected') }})
                                                                        </span>
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        {{-- Unconfigured credentials section --}}
                                        <input type="hidden" name="credential_mode" :value="credentialMode">
                                        <div class="tw:bg-gray-50 tw:dark:bg-dark-gray-300 tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:rounded-xl tw:p-5 tw:mb-6">
                                            <h4 class="tw:font-semibold tw:text-sm tw:uppercase tw:tracking-wider tw:mb-4 tw:text-gray-500 tw:dark:text-dark-white-300">{{ __('Credentials') }}</h4>

                                            <div class="tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:p-5 tw:rounded-lg tw:text-sm tw:bg-white tw:dark:bg-dark-gray-500">
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
                                                <div x-show="credentialMode === 'existing'" style="display: none;" class="tw:max-w-md tw:mb-0">
                                                    <x-select2
                                                        :id="'secret-select-unconf-' . $method['type']"
                                                        name="secret_id"
                                                        :label="__('Select Secret')"
                                                        type="secret"
                                                        :data="['secret_type' => $method['type']]"
                                                        :placeholder="__('Select an existing secret...')"
                                                        :selected="old('secret_id')"
                                                        :allow-clear="false"
                                                        class="tw:max-w-md"
                                                    >
                                                        @foreach($availableSecrets[$method['type']] ?? [] as $secret)
                                                            <option value="{{ $secret->id }}" {{ old('secret_id') == $secret->id ? 'selected' : '' }}>
                                                                {{ $secret->description }}
                                                            </option>
                                                        @endforeach
                                                    </x-select2>
                                                </div>

                                                {{-- New secret form --}}
                                                <div x-show="credentialMode === 'new'" style="display: none;"
                                                     x-data="{ description: @js(old('description', strtoupper($method['type']) . ' ' . $device->hostname)) }">
                                                    <x-device.polling.new-secret-fields
                                                        :method="$method"
                                                        name-prefix="secret_data"
                                                        model-prefix="formData"
                                                        description-name="description"
                                                        description-model="description"
                                                        default-name="default"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endif

                                {{-- Settings Configuration --}}
                                <div x-show="enabled">
                                    <x-device.polling.settings
                                        :method="$method"
                                        name-prefix="settings"
                                        model-prefix="settingsData"
                                    />
                                </div>

                                @if($method['type'] === 'snmp')
                                    <!-- SNMP Disabled Overrides -->
                                    <div x-show="!enabled" class="tw:bg-gray-50 tw:dark:bg-dark-gray-300 tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:rounded-xl tw:p-5 tw:mb-6" style="display: none;">
                                        <h4 class="tw:font-semibold tw:text-sm tw:uppercase tw:tracking-wider tw:mb-4 tw:text-gray-500 tw:dark:text-dark-white-300">{{ __('Manual Overrides') }}</h4>
                                        <div class="tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:p-5 tw:rounded-lg tw:text-sm tw:bg-white tw:dark:bg-dark-gray-500">
                                            <div class="tw:grid tw:grid-cols-1 tw:md:grid-cols-3 tw:gap-4 tw:max-w-2xl">
                                                <div>
                                                    <label class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:dark:text-dark-white-200 tw:mb-1">{{ __('sysName') }} <span class="tw:text-gray-400 tw:dark:text-dark-white-400 tw:font-normal">({{ __('optional') }})</span></label>
                                                    <input type="text" name="sysName" class="form-control" value="{{ $device->sysName }}">
                                                </div>
                                                <div>
                                                    <label class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:dark:text-dark-white-200 tw:mb-1">{{ __('Hardware') }} <span class="tw:text-gray-400 tw:dark:text-dark-white-400 tw:font-normal">({{ __('optional') }})</span></label>
                                                    <input type="text" name="hardware" class="form-control" value="{{ $device->hardware }}">
                                                </div>
                                                <div x-data="{ currentOs: {{ json_encode(['id' => $device->os, 'text' => \App\Facades\LibrenmsConfig::get('os.'.$device->os.'.text')]) }} }" x-init="setTimeout(() => init_select2('#os-select-{{ $device->device_id }}', 'os', {}, currentOs, '{{ __('OS (optional)') }}'), 100)">
                                                    <label class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:dark:text-dark-white-200 tw:mb-1">{{ __('OS') }} <span class="tw:text-gray-400 tw:dark:text-dark-white-400 tw:font-normal">({{ __('optional') }})</span></label>
                                                    <select name="os" id="os-select-{{ $device->device_id }}" class="form-control"></select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="tw:flex tw:items-center tw:justify-between tw:gap-4 tw:mt-6 tw:pt-6 tw:border-t tw:border-gray-200 tw:dark:border-dark-gray-400">
                                    <div class="tw:flex tw:items-center tw:gap-2">
                                        @if($method['configured'])
                                            <button type="submit" :disabled="!isDirty || loading" class="btn btn-primary tw:bg-blue-600 tw:border-blue-600 tw:hover:bg-blue-700" :class="(!isDirty) ? 'tw:opacity-50 tw:cursor-not-allowed' : ''">
                                                <template x-if="loading"><i class="fa fa-spinner fa-spin tw:mr-1"></i></template>
                                                <template x-if="!loading"><i class="fa fa-save tw:mr-1"></i></template>
                                                {{ __('Save Settings') }}
                                            </button>
                                        @else
                                            <button type="submit" :disabled="loading" class="btn btn-success tw:bg-emerald-600 tw:border-emerald-600 tw:hover:bg-emerald-700">
                                                <template x-if="loading"><i class="fa fa-spinner fa-spin tw:mr-1"></i></template>
                                                <template x-if="!loading"><i class="fa fa-plus tw:mr-1"></i></template>
                                                {{ __('Add Polling Type') }}
                                            </button>
                                        @endif
                                    </div>

                                    @if($method['configured'] && $method['type'] !== 'icmp')
                                        <button type="button" class="btn btn-danger" @click="deleteMethod()">
                                            <i class="fa fa-trash tw:mr-1"></i> {{ __('Remove') }} {{ $method['label'] }}
                                        </button>
                                    @endif
                                </div>
                            </form>

                            {{-- Reachability Failure Dialog --}}
                            <x-device.polling.unreachable-modal
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
        function pollingTabs(requestedTab, configuredTabs, fallbackTab, initialMethods, hasErrors, unconfiguredTabs) {
            var urlTab = new URLSearchParams(window.location.search).get('tab');
            var initialTab = requestedTab || urlTab || fallbackTab;

            // Compute activeMethods array initially
            var activeMethods = configuredTabs;
            if (initialTab && !activeMethods.includes(initialTab)) {
                activeMethods = activeMethods.concat([initialTab]);
            }

            return {
                activeTab: initialTab,
                activeMethods: activeMethods,
                methods: initialMethods || {},
                allTypes: unconfiguredTabs || [],
                dirtyMethods: {},
                setDirty(type, isDirty) {
                    this.dirtyMethods[type] = isDirty;
                },
                get hasUnsavedChanges() {
                    return Object.values(this.dirtyMethods).some(Boolean);
                },
                get noAvailabilitySources() {
                    return !Object.values(this.methods).some(m => m.enabled && m.affectsAvailability);
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
                    delete this.dirtyMethods[type];
                    this.activeMethods = this.activeMethods.filter(t => t !== type);
                    if (this.activeTab === type) {
                        this.activeTab = this.activeMethods[0] ?? '';
                    }
                },
                init() {
                    this.$watch('activeTab', function (tab) {
                        var url = new URL(window.location.href);
                        url.searchParams.set('tab', tab);
                        window.history.replaceState({}, '', url.toString());
                    });
                },
            };
        }

        function pollingMethodForm(config) {
            return {
                type: config.type,
                configured: config.configured,
                enabled: config.enabled,
                initialEnabled: config.enabled,
                affectsAvailability: config.affectsAvailability,
                initialAffectsAvailability: config.affectsAvailability,
                updateMode: 'update',
                credentialMode: 'existing',
                loading: false,
                currentSecretId: config.currentSecretId || '',
                selectedSecretId: config.selectedSecretId || '',
                showSecretInfo: false,
                isEditingSecret: false,
                secretDescription: config.secretDescription || '',
                secretMeta: config.secretMeta || {},
                secretFormDataById: config.secretFormDataById || {},
                secretFieldLabels: config.secretFieldLabels || {},
                formData: config.formData || {},
                settingsData: config.settingsData || {},
                initialSettingsData: JSON.parse(JSON.stringify(config.settingsData || {})),
                updateUrl: config.updateUrl,
                storeUrl: config.storeUrl,
                destroyUrl: config.destroyUrl,
                labels: config.labels || {},

                errors: {},
                unreachableDialog: false,
                unreachableMessage: '',
                unreachableDetails: '',

                get selectedSecretMeta() {
                    return this.secretMeta[this.selectedSecretId] || null;
                },
                get otherDevicesCount() {
                    const total = this.selectedSecretMeta?.usage_count ?? 0;
                    const isCurrent = this.configured && (String(this.selectedSecretId) === String(this.currentSecretId));
                    return isCurrent ? Math.max(0, total - 1) : total;
                },
                get isSharedSecret() {
                    return this.otherDevicesCount > 0;
                },
                get secretDataChanged() {
                    return JSON.stringify(this.formData) !== JSON.stringify(this.secretFormDataById[this.selectedSecretId] || {});
                },
                get secretValuesChanged() {
                    return this.secretDataChanged
                        || this.secretDescription !== (this.selectedSecretMeta?.description ?? '');
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
                onSecretChange() {
                    this.formData = { ...(this.secretFormDataById[this.selectedSecretId] || {}) };
                    this.secretDescription = this.selectedSecretMeta?.description ?? '';
                    this.showSecretInfo = false;
                    this.updateMode = this.showSharedGuard ? 'create' : 'update';
                },
                toggleEditSecret() {
                    this.isEditingSecret = !this.isEditingSecret;
                    if (this.isEditingSecret) { this.showSecretInfo = false; }
                },
                saveAnyway() {
                    this.unreachableDialog = false;
                    this.saveForm(null, true);
                },
                async saveForm(e, force = false) {
                    this.loading = true;
                    this.errors = {};
                    const form = this.$refs.form;
                    const formData = new FormData(form);
                    if (e && e.submitter && e.submitter.name && !formData.has(e.submitter.name)) {
                        formData.append(e.submitter.name, e.submitter.value);
                    }
                    if (force) {
                        formData.set('force_save', '1');
                    }
                    const actionUrl = this.configured ? this.updateUrl : this.storeUrl;
                    try {
                        const response = await fetch(actionUrl, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || form.querySelector('input[name="_token"]')?.value || ''
                            },
                            body: formData
                        });
                        const data = await response.json();
                        if (!response.ok) {
                            if (data.status === 'unreachable') {
                                this.unreachableMessage = data.message || @js(__('poller.reachability_check_failed'));
                                this.unreachableDetails = data.error_details || '';
                                this.unreachableDialog = true;
                            } else if (data.errors) {
                                this.errors = data.errors;
                            } else {
                                toastr.error(data.message || this.labels.saveFailed || 'Failed to save settings');
                            }
                            return;
                        }
                        this.errors = {};
                        toastr.success(data.message || this.labels.saved || 'Settings saved');
                        if (data.method) {
                            this.configured = data.method.configured;
                            this.initialEnabled = data.method.enabled;
                            this.enabled = data.method.enabled;
                            this.initialAffectsAvailability = data.method.affects_availability;
                            this.affectsAvailability = data.method.affects_availability;
                            this.currentSecretId = String(data.method.secret?.id ?? '');
                            this.selectedSecretId = String(data.method.secret?.id ?? '');
                            this.secretDescription = data.method.secret?.description ?? '';
                            this.secretMeta = data.method.secret_meta ?? {};
                            this.secretFormDataById = data.method.secret_form_data_by_id ?? {};
                            this.formData = { ...(data.method.secret_form_data ?? {}) };
                            this.settingsData = { ...(data.method.settings ?? {}) };
                            this.initialSettingsData = { ...(data.method.settings ?? {}) };

                            if (data.method.secret && this.$refs.secretSelect) {
                                const selectEl = this.$refs.secretSelect.tagName === 'SELECT'
                                    ? this.$refs.secretSelect
                                    : (this.$refs.secretSelect.querySelector('select') || this.$refs.secretSelect);
                                const $select = $(selectEl);
                                const secretId = String(data.method.secret.id);
                                const secretDesc = data.method.secret.description;
                                $select.find(`option[value="${secretId}"]`).remove();
                                const newOption = new Option(secretDesc, secretId, true, true);
                                $select.append(newOption);
                                $select.val(secretId).trigger('change');
                            }
                        }
                        this.setDirty(this.type, false);
                    } catch (err) {
                        toastr.error(this.labels.saveError || 'An error occurred while saving.');
                    } finally {
                        this.loading = false;
                    }
                },
                async deleteMethod() {
                    if (!confirm(this.labels.confirmRemove || 'Are you sure you want to remove this polling method?')) {
                        return;
                    }
                    try {
                        const formData = new FormData();
                        formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');
                        formData.append('_method', 'DELETE');
                        const response = await fetch(this.destroyUrl, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                            },
                            body: formData
                        });
                        const data = await response.json();
                        if (!response.ok) {
                            toastr.error(data.message || this.labels.removeFailed || 'Failed to remove polling method');
                            return;
                        }
                        toastr.success(data.message || this.labels.removed || 'Polling method removed');
                        this.removeMethod(this.type);
                    } catch (err) {
                        toastr.error(this.labels.removeError || 'An error occurred while removing.');
                    }
                },
                init() {
                    this.setDirty(this.type, this.isDirty);
                    this.$watch('isDirty', (val) => {
                        this.setDirty(this.type, val);
                    });
                    this.$watch('enabled', (val) => {
                        if (this.methods && this.methods[this.type]) {
                            this.methods[this.type].enabled = val;
                        }
                    });
                    this.$watch('affectsAvailability', (val) => {
                        if (this.methods && this.methods[this.type]) {
                            this.methods[this.type].affectsAvailability = val;
                        }
                    });
                    this.$watch('showSharedGuard', (val) => {
                        this.updateMode = val ? 'create' : 'update';
                    });
                }
            };
        }

        function togglePasswordVisibility(inputId, btn) {
            var input = document.getElementById(inputId);
            var icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        }
    </script>
@endpush
