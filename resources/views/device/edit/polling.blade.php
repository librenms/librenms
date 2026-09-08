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
                        <li class="tw:mt-4 tw:pt-2 tw:border-t tw:border-gray-200 tw:dark:border-dark-gray-400"
                            x-show="addableRemaining.length > 0">
                            <div class="input-group">
                                <select id="add-method-select" class="form-control tw:rounded-l-lg tw:rounded-r-none tw:border-gray-200 tw:bg-white tw:dark:border-dark-gray-400 tw:dark:bg-dark-gray-500 tw:dark:text-white">
                                    <option value="">{{ __('Add polling type...') }}</option>
                                    <template x-for="m in addableRemaining" :key="m.type">
                                        <option :value="m.type" x-text="m.label"></option>
                                    </template>
                                </select>
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-success tw:bg-emerald-600 tw:hover:bg-emerald-700 tw:border-emerald-600"
                                            @click="
                                                const sel = $el.closest('.input-group').querySelector('select');
                                                if (sel.value) { addMethod(sel.value); sel.value = ''; }
                                            ">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </span>
                            </div>
                        </li>
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
                        <div x-data="{
                                configured: {{ $method['configured'] ? 'true' : 'false' }},
                                enabled: {{ $method['enabled'] ? 'true' : 'false' }},
                                initialEnabled: {{ $method['enabled'] ? 'true' : 'false' }},
                                affectsAvailability: {{ $method['affects_availability'] ? 'true' : 'false' }},
                                initialAffectsAvailability: {{ $method['affects_availability'] ? 'true' : 'false' }},
                                updateMode: 'update',
                                credentialMode: 'existing',
                                currentSecretId: '{{ (string) ($method['secret']?->id ?? '') }}',
                                selectedSecretId: '{{ (string) ($method['secret']?->id ?? '') }}',
                                showSecretInfo: false,
                                isEditingSecret: false,
                                secretDescription: @js($method['secret']?->description ?? ''),
                                // secretMeta: { [id]: { description, usage_count } }, covering every secret of
                                // this type (from $method['secret_meta']). The merge below is just a defensive
                                // backstop for the assigned secret in case it ever falls outside that list.
                                secretMeta: @js((object) array_replace(
                                    $method['secret_meta'] ?? [],
                                    $method['secret']
                                        ? [(string) $method['secret']->id => [
                                            'description' => $method['secret']->description,
                                            'usage_count' => $method['usage_count'] ?? 1,
                                        ]]
                                        : []
                                )),
                                // Likewise a defensive backstop: $method['secret_form_data_by_id'] already
                                // covers every secret of this type, matching formData's initial value for
                                // whichever one is currently assigned.
                                secretFormDataById: @js((object) array_replace(
                                    $method['secret_form_data_by_id'] ?? [],
                                    $method['secret']
                                        ? [(string) $method['secret']->id => (object) ($method['secret_form_data'] ?? $method['schema_defaults'] ?? [])]
                                        : []
                                )),
                                secretFieldLabels: @js((object) collect($method['schema_fields'] ?? [])->mapWithKeys(fn (array $f) => [$f['key'] => $f['label'] ?? $f['key']])),
                                formData: @js((object) ($method['secret_form_data'] ?? $method['schema_defaults'] ?? [])),
                                settingsData: @js((object) ($method['settings'] ?? [])),
                                initialSettingsData: @js((object) ($method['settings'] ?? [])),
                                get selectedSecretMeta() {
                                    return this.secretMeta[this.selectedSecretId] || null;
                                },
                                get isSharedSecret() {
                                    return (this.selectedSecretMeta?.usage_count ?? 1) > 1;
                                },
                                get secretValuesChanged() {
                                    return JSON.stringify(this.formData) !== JSON.stringify(this.secretFormDataById[this.selectedSecretId] || {})
                                        || this.secretDescription !== (this.selectedSecretMeta?.description ?? '');
                                },
                                // The shared-secret guard now tracks editing intent (the Edit button),
                                // not whether values have changed yet — it should appear as soon as
                                // someone opens the editor on a secret other devices also use.
                                get showSharedGuard() {
                                    return this.configured && this.isEditingSecret && this.isSharedSecret;
                                },
                                get isDirty() {
                                    if (!this.configured) { return true; }
                                    return this.enabled !== this.initialEnabled
                                        || this.affectsAvailability !== this.initialAffectsAvailability
                                        || this.selectedSecretId !== this.currentSecretId
                                        || this.secretValuesChanged
                                        || JSON.stringify(this.settingsData) !== JSON.stringify(this.initialSettingsData);
                                },
                                onSecretChange() {
                                    // Switching secrets loads that secret's own stored values fresh —
                                    // it does not carry over edits made to the previously selected secret.
                                    this.formData = { ...(this.secretFormDataById[this.selectedSecretId] || {}) };
                                    this.secretDescription = this.selectedSecretMeta?.description ?? '';
                                    this.isEditingSecret = true;
                                    this.showSecretInfo = false;
                                },
                                toggleEditSecret() {
                                    this.isEditingSecret = !this.isEditingSecret;
                                    if (this.isEditingSecret) { this.showSecretInfo = false; }
                                },
                                init() {
                                    setDirty('{{ $method["type"] }}', this.isDirty);
                                    this.$watch('isDirty', (val) => {
                                        setDirty('{{ $method["type"] }}', val);
                                    });
                                    this.$watch('enabled', (val) => {
                                        methods['{{ $method["type"] }}'].enabled = val;
                                    });
                                    this.$watch('affectsAvailability', (val) => {
                                        methods['{{ $method["type"] }}'].affectsAvailability = val;
                                    });
                                    // Default to the safe choice the moment editing a shared secret's
                                    // values actually becomes a live concern.
                                    this.$watch('showSharedGuard', (val) => {
                                        if (val) { this.updateMode = 'create'; }
                                    });
                                }
                             }"
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

                            <form method="POST" action="{{ $method['configured'] ? route('device.edit.polling.update', ['device' => $device, 'methodType' => $method['type']]) : route('device.edit.polling.store', ['device' => $device]) }}">
                                @csrf
                                @if($method['configured'])
                                    @method('PUT')
                                @else
                                    <input type="hidden" name="method_type" value="{{ $method['type'] }}">
                                @endif
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

                                                {{-- Secret picker --}}
                                                <div class="tw:mb-4">
                                                    <label class="tw:block tw:text-sm tw:font-medium tw:text-gray-700 tw:dark:text-dark-white-200 tw:mb-1">{{ __('Secret') }}</label>
                                                    <div class="tw:flex tw:items-center tw:gap-2 tw:max-w-xl">
                                                        <select x-model="selectedSecretId" @change="onSecretChange()" class="form-control">
                                                            @foreach(($availableSecrets[$method['type']] ?? collect()) as $secret)
                                                                <option value="{{ (string) $secret->id }}">{{ $secret->description }}</option>
                                                            @endforeach
                                                        </select>
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

                                                    <div x-show="showSecretInfo" x-cloak style="display: none;"
                                                         class="tw:mt-3 tw:bg-gray-50 tw:dark:bg-dark-gray-400 tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:rounded-lg tw:p-3 tw:text-sm">
                                                        <div class="tw:font-semibold tw:text-gray-800 tw:dark:text-dark-white-100" x-text="selectedSecretMeta?.description ?? '{{ __('Unknown secret') }}'"></div>
                                                        <div class="tw:text-gray-500 tw:dark:text-dark-white-300 tw:mt-1">
                                                            <template x-if="isSharedSecret">
                                                                <span>{{ __('Shared — used by') }} <span x-text="(selectedSecretMeta?.usage_count ?? 1) - 1"></span> {{ __('other device(s).') }}</span>
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
                                                    <div class="form-group tw:max-w-md">
                                                        <label class="control-label">{{ __('Secret Description') }}</label>
                                                        <input type="text" name="description" x-model="secretDescription" class="form-control">
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
                                                                        <input type="radio" name="secret_update_mode" value="create" x-model="updateMode" class="tw:w-4 tw:h-4 tw:text-[#337ab7] tw:border-gray-300 tw:focus:ring-[#337ab7] tw:mr-2">
                                                                        <span class="tw:text-gray-700 tw:dark:text-dark-white-200">{{ __('Create a new secret for this device only (recommended)') }}</span>
                                                                    </label>
                                                                    <label class="tw:flex tw:items-center tw:cursor-pointer">
                                                                        <input type="radio" name="secret_update_mode" value="update" x-model="updateMode" class="tw:w-4 tw:h-4 tw:text-[#337ab7] tw:border-gray-300 tw:focus:ring-[#337ab7] tw:mr-2">
                                                                        <span class="tw:text-gray-700 tw:dark:text-dark-white-200">
                                                                            {{ __('Update the shared secret') }}
                                                                            (<span x-text="(selectedSecretMeta?.usage_count ?? 1) - 1"></span> {{ __('other device(s) affected') }})
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
                                                <div x-show="credentialMode === 'existing'" style="display: none;" class="form-group tw:max-w-md tw:mb-0">
                                                    <label class="control-label">{{ __('Select Secret') }}</label>
                                                    <select name="secret_id" class="form-control">
                                                        <option value="">{{ __('Select an existing secret...') }}</option>
                                                        @foreach($availableSecrets[$method['type']] ?? [] as $secret)
                                                            <option value="{{ $secret->id }}" {{ old('secret_id') == $secret->id ? 'selected' : '' }}>
                                                                {{ $secret->description }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                {{-- New secret form --}}
                                                <div x-show="credentialMode === 'new'" style="display: none;"
                                                     x-data="{ description: @js(old('description', strtoupper($method['type']) . ' ' . $device->hostname)) }">
                                                    <div class="tw:grid tw:grid-cols-1 tw:md:grid-cols-2 tw:gap-4 tw:max-w-2xl tw:mb-4">
                                                        <div class="form-group">
                                                            <label class="control-label">{{ __('Secret Description') }}</label>
                                                            <input type="text" name="description" x-model="description" class="form-control" value="{{ old('description') }}">
                                                        </div>
                                                        <div class="tw:flex tw:items-end">
                                                            <div class="checkbox tw:mb-0">
                                                                <label>
                                                                    <input type="hidden" name="default" value="0">
                                                                    <input type="checkbox" name="default" value="1" {{ old('default') ? 'checked' : '' }}>
                                                                    {{ __('Make Default') }}
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <x-field-schema-fields
                                                        :fields="$method['schema_fields']"
                                                        :method-type="$method['type']"
                                                        name-prefix="secret_data"
                                                        model-prefix="formData"
                                                        :grid="true" />
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endif

                                {{-- Settings Configuration --}}
                                @if(!empty($method['settings_fields']))
                                    <div x-show="enabled" class="tw:bg-gray-50 tw:dark:bg-dark-gray-300 tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:rounded-xl tw:p-5 tw:mb-6">
                                        <h4 class="tw:font-semibold tw:text-sm tw:uppercase tw:tracking-wider tw:mb-4 tw:text-gray-500 tw:dark:text-dark-white-300">{{ __('Settings') }}</h4>

                                        <div class="tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:p-5 tw:rounded-lg tw:text-sm tw:bg-white tw:dark:bg-dark-gray-500">
                                            <x-field-schema-fields
                                                :fields="$method['settings_fields']"
                                                :method-type="$method['type']"
                                                name-prefix="settings"
                                                model-prefix="settingsData"
                                                :grid="true" />
                                        </div>
                                    </div>
                                @endif

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

                                <div class="tw:flex tw:items-center tw:justify-between tw:gap-4 tw:mt-6 tw:pt-6 tw:border-t tw:border-gray-200 tw:dark:border-dark-gray-400" x-data="{ loading: false }">
                                    <div class="tw:flex tw:items-center tw:gap-2">
                                        @if($method['configured'])
                                            <button type="submit" :disabled="!isDirty || loading" class="btn btn-primary tw:bg-blue-600 tw:border-blue-600 tw:hover:bg-blue-700" :class="(!isDirty) ? 'tw:opacity-50 tw:cursor-not-allowed' : ''" @click="loading = true">
                                                <template x-if="loading"><i class="fa fa-spinner fa-spin tw:mr-1"></i></template>
                                                <template x-if="!loading"><i class="fa fa-save tw:mr-1"></i></template>
                                                {{ __('Save Settings') }}
                                            </button>

                                            @if($method['type'] === 'snmp')
                                                <button type="submit" name="force_save" value="1" class="btn btn-warning" x-show="enabled">
                                                    <i class="fa fa-exclamation-triangle tw:mr-1"></i> {{ __('Force Save') }}
                                                </button>
                                            @endif
                                        @else
                                            <button type="submit" :disabled="loading" class="btn btn-success tw:bg-emerald-600 tw:border-emerald-600 tw:hover:bg-emerald-700" @click="loading = true">
                                                <template x-if="loading"><i class="fa fa-spinner fa-spin tw:mr-1"></i></template>
                                                <template x-if="!loading"><i class="fa fa-plus tw:mr-1"></i></template>
                                                {{ __('Add Polling Type') }}
                                            </button>
                                        @endif
                                    </div>

                                    @if($method['configured'] && $method['type'] !== 'icmp')
                                        <button type="submit" form="delete-form-{{ $method['type'] }}" class="btn btn-danger" onclick="return confirm('{{ __('Are you sure you want to remove this polling method?') }}')">
                                            <i class="fa fa-trash tw:mr-1"></i> {{ __('Remove') }} {{ $method['label'] }}
                                        </button>
                                    @endif
                                </div>
                            </form>

                            @if($method['configured'] && $method['type'] !== 'icmp')
                                <form id="delete-form-{{ $method['type'] }}" method="POST" action="{{ route('device.edit.polling.destroy', ['device' => $device, 'methodType' => $method['type']]) }}" style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="tab" value="{{ $method['type'] }}">
                                </form>
                            @endif
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
