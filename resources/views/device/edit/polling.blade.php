@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device">
        <x-device.edit-tabs :device="$device" />

        @if($configuredMethods->isNotEmpty())
            <div
                x-data="pollingTabs('{{ request('tab') }}', @js($configuredMethods->pluck('type')->values()), '{{ $configuredMethods->first()["type"] }}', @js($configuredMethods->mapWithKeys(fn($m) => [$m['type'] => ['enabled' => (bool)$m['enabled'], 'affectsAvailability' => (bool)$m['affects_availability']]])))"
                class="tw:flex tw:flex-col tw:md:flex-row tw:gap-6 tw:mt-6"
            >
                <!-- Left Tabs -->
                <div class="tw:w-full tw:md:w-1/4 tw:shrink-0">
                    <ul class="tw:flex tw:flex-col tw:space-y-2">
                        @foreach($configuredMethods as $method)
                            <li>
                                <button type="button" @click="activeTab = '{{ $method["type"] }}'"
                                        :class="activeTab === '{{ $method["type"] }}' ? 'tw:bg-blue-600 tw:text-white! tw:border-blue-600 tw:dark:bg-blue-800' : 'tw:text-gray-700 tw:border-gray-200 tw:hover:bg-gray-50 tw:dark:text-dark-white-200 tw:dark:border-dark-gray-400 tw:dark:hover:bg-dark-gray-400'"
                                        class="tw:w-full tw:text-left tw:px-4 tw:py-3 tw:border tw:rounded-lg tw:flex tw:justify-between tw:items-center tw:transition-colors tw:shadow-sm">
                                    <span class="tw:font-medium">{{ $method['label'] }}</span>
                                    <div class="tw:flex tw:items-center">
                                        @if($method['last_check_successful'] === true)
                                            <i class="fa fa-fw fa-check-circle tw:text-green-500" title="{{ __('Status: Successful') }}"></i>
                                        @elseif($method['last_check_successful'] === false)
                                            <i class="fa fa-fw fa-times-circle tw:text-red-500" title="{{ __('Status: Failed') }}"></i>
                                        @else
                                            <i class="fa fa-fw fa-circle-o tw:text-gray-400 tw:dark:text-dark-white-400" title="{{ __('Status: Unknown') }}"></i>
                                        @endif
                                    </div>
                                </button>
                            </li>
                        @endforeach
                        <li class="tw:mt-4 tw:pt-2 tw:border-t tw:border-gray-200 tw:dark:border-dark-gray-400">
                            <button type="button" @click="activeTab = 'add'"
                                    :class="activeTab === 'add' ? 'tw:bg-[#32c861] tw:text-white! tw:border-[#32c861]' : 'tw:bg-[#32c861] tw:text-white tw:border-[#32c861] tw:hover:bg-[#29a34f]'"
                                    class="tw:w-full tw:text-left tw:px-4 tw:py-3 tw:border tw:rounded-lg tw:flex tw:justify-between tw:items-center tw:transition-colors tw:shadow-sm tw:dark:text-white!">
                                <span class="tw:font-medium">{{ __('Add Polling Type') }}</span>
                                <i class="fa fa-plus"></i>
                            </button>
                        </li>
                    </ul>
                </div>

                <!-- Right Content -->
                <div class="tw:w-full tw:md:w-3/4 tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:rounded-lg tw:shadow-sm tw:p-6 tw:grow">
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

                    @foreach($configuredMethods as $method)
                        <div x-data="{
                                enabled: {{ $method['enabled'] ? 'true' : 'false' }},
                                affectsAvailability: {{ $method['affects_availability'] ? 'true' : 'false' }},
                                updateMode: 'update',
                                currentSecretId: '{{ (string) ($method['secret']?->id ?? '') }}',
                                selectedSecretId: '{{ (string) ($method['secret']?->id ?? '') }}',
                                pendingSecretId: '{{ (string) ($method['secret']?->id ?? '') }}',
                                isChangingSecret: false,
                                secretSelectionConfirmed: false,
                                secretDescriptions: @js($method['secret_descriptions']),
                                secretFormDataById: @js($method['secret_form_data_by_id']),
                                formData: {{ json_encode($method['secret_form_data']) }},
                                settingsData: {{ json_encode($method['settings']) }},
                                init() {
                                    this.$watch('enabled', (val) => {
                                        this.methods['{{ $method["type"] }}'].enabled = val;
                                    });
                                    this.$watch('affectsAvailability', (val) => {
                                        this.methods['{{ $method["type"] }}'].affectsAvailability = val;
                                    });
                                }
                             }"
                             x-show="activeTab === '{{ $method["type"] }}'"
                             style="display: none;"
                             x-transition>

                            <div class="tw:text-2xl tw:font-semibold tw:mb-6 tw:text-gray-800 tw:dark:text-dark-white-100 tw:border-b tw:pb-3 tw:dark:border-dark-gray-400">{{ $method['label'] }} {{ __('Settings') }}</div>

                            <form method="POST" action="{{ route('device.edit.polling.update', ['device' => $device, 'methodType' => $method['type']]) }}">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="tab" value="{{ $method['type'] }}">

                                <div class="tw:mb-6">
                                    <label class="tw:flex tw:items-center tw:cursor-pointer tw:group tw:px-4 tw:py-3 tw:rounded-lg tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:w-full tw:max-w-md">
                                        <div class="tw:relative tw:shrink-0">
                                            <input type="hidden" name="enabled" value="0">
                                            <input type="checkbox" name="enabled" value="1" class="tw:sr-only" x-model="enabled">
                                            <div class="tw:block tw:w-12 tw:h-7 tw:rounded-full tw:transition-colors tw:duration-200" :class="enabled ? 'tw:bg-blue-600' : 'tw:bg-gray-300 tw:dark:bg-dark-gray-400'"></div>
                                            <div class="tw:absolute tw:left-0.5 tw:top-0.5 tw:w-6 tw:h-6 tw:rounded-full tw:transition-transform tw:duration-200 tw:bg-white tw:shadow-sm" :class="enabled ? 'tw:translate-x-5' : 'tw:translate-x-0'"></div>
                                        </div>
                                        <span class="tw:ml-3 tw:font-medium tw:text-gray-700 tw:dark:text-dark-white-200">{{ __('Enabled') }}</span>
                                    </label>

                                    <label class="tw:flex tw:items-center tw:cursor-pointer tw:group tw:px-4 tw:py-3 tw:rounded-lg tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:w-full tw:max-w-md tw:mt-3">
                                        <div class="tw:relative tw:shrink-0">
                                            <input type="hidden" name="affects_availability" value="0">
                                            <input type="checkbox" name="affects_availability" value="1" class="tw:sr-only" x-model="affectsAvailability">
                                            <div class="tw:block tw:w-12 tw:h-7 tw:rounded-full tw:transition-colors tw:duration-200" :class="affectsAvailability ? 'tw:bg-blue-600' : 'tw:bg-gray-300 tw:dark:bg-dark-gray-400'"></div>
                                            <div class="tw:absolute tw:left-0.5 tw:top-0.5 tw:w-6 tw:h-6 tw:rounded-full tw:transition-transform tw:duration-200 tw:bg-white tw:shadow-sm" :class="affectsAvailability ? 'tw:translate-x-5' : 'tw:translate-x-0'"></div>
                                        </div>
                                        <span class="tw:ml-3 tw:font-medium tw:text-gray-700 tw:dark:text-dark-white-200">{{ __('poller.affects_availability') }}</span>
                                    </label>

                                    @if(!empty($method['settings_fields']))
                                        <div x-show="enabled" class="tw:mt-6 tw:pt-6 tw:border-t tw:border-gray-200 tw:dark:border-dark-gray-400">
                                            <h4 class="tw:font-semibold tw:text-lg tw:mb-4 tw:text-gray-800 tw:dark:text-dark-white-100">{{ __($method['label'] . ' Configuration') }}</h4>

                                            <div class="tw:grid tw:grid-cols-1 tw:gap-4 tw:max-w-2xl">
                                                @foreach($method['settings_fields'] as $setting)
                                                    <div @if($setting['visible_if_expression']) x-show="{{ $setting['visible_if_expression'] }}" @endif>
                                                        <label class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:dark:text-dark-white-200 tw:mb-1">{{ __('poller.method_settings.' . $method['type'] . '.' . $setting['key']) }}</label>
                                                        @if(($setting['field_type'] ?? 'text') === 'select')
                                                            <select name="settings[{{ $setting['key'] }}]" x-model="settingsData['{{ $setting['key'] }}']" class="form-control">
                                                                @foreach($setting['options'] ?? [] as $optVal => $optLabel)
                                                                    <option value="{{ $optVal }}">{{ __($optLabel) }}</option>
                                                                @endforeach
                                                            </select>
                                                        @elseif(($setting['field_type'] ?? 'text') === 'number')
                                                            <input
                                                                type="number"
                                                                name="settings[{{ $setting['key'] }}]"
                                                                x-model="settingsData['{{ $setting['key'] }}']"
                                                                class="form-control"
                                                                @isset($setting['min']) min="{{ $setting['min'] }}" @endisset
                                                                @isset($setting['max']) max="{{ $setting['max'] }}" @endisset
                                                            >
                                                        @else
                                                            <input type="text" name="settings[{{ $setting['key'] }}]" x-model="settingsData['{{ $setting['key'] }}']" class="form-control">
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    @if($method['type'] === 'snmp')
                                        <!-- SNMP Disabled Overrides -->
                                        <div x-show="!enabled" class="tw:mt-6 tw:pt-6 tw:border-t tw:border-gray-200 tw:dark:border-dark-gray-400" style="display: none;">
                                            <h4 class="tw:font-semibold tw:text-lg tw:mb-4 tw:text-gray-800 tw:dark:text-dark-white-100">{{ __('Manual Overrides') }}</h4>
                                            <div class="tw:grid tw:grid-cols-1 tw:gap-4 tw:max-w-2xl">
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
                                    @endif
                                </div>

                                {{-- Credentials section — shown for any method that has a secret --}}
                                @if(!empty($method['schema_fields']))
                                    <div class="tw:mb-6" x-show="enabled">
                                        <h4 class="tw:font-semibold tw:text-lg tw:mb-3 tw:text-gray-700 tw:dark:text-dark-white-100">{{ __('Configured Secret') }}</h4>

                                        <div class="tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:p-5 tw:rounded-lg tw:text-sm">
                                            <input type="hidden" name="secret_id" :value="selectedSecretId" :disabled="!secretSelectionConfirmed">

                                            <div class="tw:mb-4">
                                                <div x-show="!isChangingSecret" class="tw:flex tw:flex-wrap tw:items-center tw:gap-2">
                                                    <div class="tw:font-medium tw:text-lg tw:text-gray-800 tw:dark:text-dark-white-100" x-text="secretDescriptions[selectedSecretId] ?? '{{ __('None') }}'"></div>
                                                    <button
                                                        type="button"
                                                        class="btn btn-default btn-sm"
                                                        @click="pendingSecretId = selectedSecretId; isChangingSecret = true"
                                                        title="{{ __('Change Secret') }}"
                                                        aria-label="{{ __('Change Secret') }}"
                                                    >
                                                        <i class="fa fa-edit"></i>
                                                    </button>
                                                </div>

                                                <div x-show="isChangingSecret" style="display: none;" class="tw:flex tw:flex-col tw:gap-2 tw:max-w-xl">
                                                    <select x-model="pendingSecretId" class="form-control">
                                                        @foreach(($availableSecrets[$method['type']] ?? collect()) as $secret)
                                                            <option value="{{ (string) $secret->id }}">{{ $secret->description }}</option>
                                                        @endforeach
                                                    </select>

                                                    <div class="tw:flex tw:items-center tw:gap-2">
                                                        <button
                                                            type="button"
                                                            class="btn btn-primary btn-sm"
                                                            @click="selectedSecretId = pendingSecretId; formData = { ...formData, ...(secretFormDataById[pendingSecretId] || {}) }; secretSelectionConfirmed = pendingSecretId !== currentSecretId; isChangingSecret = false"
                                                            title="{{ __('Confirm') }}"
                                                            aria-label="{{ __('Confirm') }}"
                                                        >
                                                            <i class="fa fa-check"></i>
                                                        </button>
                                                        <button
                                                            type="button"
                                                            class="btn btn-default btn-sm"
                                                            @click="pendingSecretId = selectedSecretId; secretSelectionConfirmed = false; isChangingSecret = false"
                                                            title="{{ __('Cancel') }}"
                                                            aria-label="{{ __('Cancel') }}"
                                                        >
                                                            <i class="fa fa-times"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            @if($method['usage_count'] > 1)
                                                <div class="tw:mb-5 tw:bg-yellow-50 tw:dark:bg-transparent tw:border tw:border-yellow-200 tw:dark:border-yellow-800 tw:p-4 tw:rounded-lg">
                                                    <div class="tw:flex tw:items-start">
                                                        <i class="fa fa-exclamation-triangle tw:text-yellow-600 tw:dark:text-yellow-500 tw:mt-1 tw:mr-3"></i>
                                                        <div>
                                                            <p class="tw:text-sm tw:font-medium tw:text-yellow-800 tw:dark:text-yellow-400 tw:mb-2">
                                                                {{ __('This secret is shared across :count devices.', ['count' => $method['usage_count']]) }}
                                                            </p>
                                                            <div class="tw:flex tw:flex-col tw:gap-2">
                                                                <label class="tw:flex tw:items-center tw:cursor-pointer">
                                                                    <input type="radio" name="secret_update_mode" value="update" x-model="updateMode" class="tw:w-4 tw:h-4 tw:text-blue-600 tw:border-gray-300 tw:focus:ring-blue-500 tw:mr-2">
                                                                    <span class="tw:text-gray-700 tw:dark:text-dark-white-200">{{ __('Update this shared secret (affects all devices)') }}</span>
                                                                </label>
                                                                <label class="tw:flex tw:items-center tw:cursor-pointer">
                                                                    <input type="radio" name="secret_update_mode" value="create" x-model="updateMode" class="tw:w-4 tw:h-4 tw:text-blue-600 tw:border-gray-300 tw:focus:ring-blue-500 tw:mr-2">
                                                                    <span class="tw:text-gray-700 tw:dark:text-dark-white-200">{{ __('Create a new secret for this device only') }}</span>
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <input type="hidden" name="secret_update_mode" value="update">
                                            @endif

                                            <div class="tw:grid tw:grid-cols-1 tw:md:grid-cols-2 tw:gap-4 tw:max-w-2xl">
                                                @foreach($method['schema_fields'] as $field)
                                                    <div class="tw:flex tw:flex-col" @if($field['visible_if_expression']) x-show='{{ $field['visible_if_expression'] }}' @endif>
                                                        <label class="tw:text-gray-500 tw:dark:text-dark-white-400 tw:uppercase tw:text-xs tw:font-bold tw:mb-1">{{ __($field['label']) }}</label>

                                                        @if($field['field_type'] === 'select')
                                                            <select name="secret_data[{{ $field['key'] }}]" x-model="formData['{{ $field['key'] }}']" class="form-control">
                                                                @foreach($field['options'] as $optVal => $optLabel)
                                                                    <option value="{{ $optVal }}">{{ __($optLabel) }}</option>
                                                                @endforeach
                                                            </select>
                                                        @elseif($field['field_type'] === 'password')
                                                            @can('unmask', \App\Models\Secret::class)
                                                                <div class="input-group tw:w-full">
                                                                    <input type="password" id="secret_{{ $method['type'] }}_{{ $field['key'] }}" name="secret_data[{{ $field['key'] }}]" x-model="formData['{{ $field['key'] }}']" class="form-control" autocomplete="new-password">
                                                                    <span class="input-group-btn">
                                                                        <button type="button" class="btn btn-default btn-toggle-password" onclick="togglePasswordVisibility('secret_{{ $method['type'] }}_{{ $field['key'] }}', this)" title="{{ __('Show/hide') }}">
                                                                            <i class="fa fa-eye-slash"></i>
                                                                        </button>
                                                                    </span>
                                                                </div>
                                                            @else
                                                                <input type="password" name="secret_data[{{ $field['key'] }}]" value="********" class="form-control" readonly>
                                                            @endcan
                                                        @else
                                                            <input type="text" name="secret_data[{{ $field['key'] }}]" x-model="formData['{{ $field['key'] }}']" class="form-control">
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="tw:flex tw:items-center tw:gap-4 tw:mt-6 tw:pt-6 tw:border-t tw:border-gray-200 tw:dark:border-dark-gray-400">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-save tw:mr-1"></i> {{ __('Save Settings') }}
                                    </button>

                                    @if($method['type'] === 'snmp')
                                        <button type="submit" name="force_save" value="1" class="btn btn-warning" x-show="enabled">
                                            <i class="fa fa-exclamation-triangle tw:mr-1"></i> {{ __('Force Save') }}
                                        </button>
                                    @endif
                                </div>
                            </form>

                            @if($method['type'] !== 'icmp')
                                <form method="POST" action="{{ route('device.edit.polling.destroy', ['device' => $device, 'methodType' => $method['type']]) }}" class="tw:mt-4">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="tab" value="{{ $method['type'] }}">
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('{{ __('Are you sure you want to remove this polling method?') }}')">
                                        <i class="fa fa-trash tw:mr-1"></i> {{ __('Remove') }} {{ $method['label'] }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endforeach

                    <!-- Add Polling Type Content -->
                    <div x-show="activeTab === 'add'" style="display: none;" x-transition>
                        @include('device.edit.includes.add-polling-type')
                    </div>
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
        function pollingTabs(requestedTab, configuredTabs, fallbackTab, initialMethods) {
            var allTabs = configuredTabs.concat(['add']);
            var urlTab = new URLSearchParams(window.location.search).get('tab');
            var initialTab = requestedTab || urlTab || fallbackTab;

            if (! allTabs.includes(initialTab)) {
                initialTab = fallbackTab;
            }

            return {
                activeTab: initialTab,
                methods: initialMethods || {},
                get noAvailabilitySources() {
                    return !Object.values(this.methods).some(m => m.enabled && m.affectsAvailability);
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
