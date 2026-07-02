<h3 class="tw:text-xl tw:font-semibold tw:mb-6 tw:text-gray-800 tw:dark:text-dark-white-100 tw:border-b tw:pb-3 tw:dark:border-dark-gray-400">{{ __('Add Polling Type') }}</h3>

@if($unconfiguredMethods->isEmpty())
    <div class="tw:bg-blue-50 tw:text-blue-800 tw:p-4 tw:rounded-lg tw:border tw:border-blue-200 tw:dark:bg-transparent tw:dark:text-blue-300 tw:dark:border-dark-gray-400">
        <i class="fa fa-info-circle tw:mr-2"></i> {{ __('All available polling types are already configured for this device.') }}
    </div>
@else
    @if($errors->any())
        <div class="tw:mb-4 tw:bg-red-50 tw:dark:bg-transparent tw:border tw:border-red-300 tw:dark:border-red-800 tw:text-red-700 tw:dark:text-red-400 tw:p-4 tw:rounded-lg">
            <ul class="tw:list-disc tw:list-inside tw:space-y-1 tw:text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('device.edit.polling.store', $device) }}"
          x-data="{
              methodType: '{{ old('method_type', '') }}',
              credentialMode: '{{ old('credential_mode', 'existing') }}'
          }">
        @csrf

        {{-- Step 1: Pick a polling type --}}
        <div class="tw:bg-gray-50 tw:dark:bg-dark-gray-300 tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:rounded-xl tw:p-6 tw:mb-6 tw:max-w-2xl">
            <label class="tw:block tw:font-medium tw:mb-2 tw:text-gray-700 tw:dark:text-dark-white-200">{{ __('Polling Type') }}</label>
            <select name="method_type" x-model="methodType" class="form-control @error('method_type') tw:border-red-500 @enderror" required>
                <option value="">{{ __('Select a polling type...') }}</option>
                @foreach($unconfiguredMethods as $method)
                    <option value="{{ $method['type'] }}">{{ $method['label'] }}</option>
                @endforeach
            </select>
            @error('method_type')
            <p class="tw:text-red-600 tw:dark:text-red-400 tw:text-sm tw:mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Step 2: Per-method configuration --}}
        @foreach($unconfiguredMethods as $method)
            <div x-show="methodType === '{{ $method['type'] }}'" style="display: none;" x-transition
                 x-data="{ settingsData: @json(old('settings', $method['settings'] ?? [])) }">

                @if(empty($method['schema_fields']))
                    {{-- No secret needed (ICMP, IPMI, unix-agent, etc.) --}}
                    <div class="tw:mb-6 tw:p-4 tw:rounded-lg tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:max-w-2xl tw:bg-gray-50 tw:dark:bg-transparent">
                        <div class="tw:flex tw:items-start tw:gap-3">
                            <i class="fa fa-info-circle tw:text-[#337ab7] tw:mt-0.5 tw:shrink-0"></i>
                            <div>
                                <p class="tw:font-medium tw:text-gray-800 tw:dark:text-dark-white-100">{{ $method['label'] }}</p>
                                @if($method['type'] === 'icmp')
                                    <p class="tw:text-sm tw:text-gray-500 tw:dark:text-dark-white-400 tw:mt-1">{{ __('ICMP (ping) polling requires no credentials. It will be enabled immediately.') }}</p>
                                @elseif($method['type'] === 'unix-agent')
                                    <p class="tw:text-sm tw:text-gray-500 tw:dark:text-dark-white-400 tw:mt-1">{{ __('The Unix Agent will be configured on port 6556. You can adjust settings after adding.') }}</p>
                                @elseif($method['type'] === 'ipmi')
                                    <p class="tw:text-sm tw:text-gray-500 tw:dark:text-dark-white-400 tw:mt-1">{{ __('IPMI polling will be enabled. Configure credentials and settings after adding.') }}</p>
                                @else
                                    <p class="tw:text-sm tw:text-gray-500 tw:dark:text-dark-white-400 tw:mt-1">{{ __('This polling type requires no additional configuration.') }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    {{-- Has a secret schema (SNMP, etc.) --}}

                    {{-- Credentials Panel --}}
                    <div class="tw:bg-gray-50 tw:dark:bg-dark-gray-300 tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:rounded-xl tw:p-5 tw:mb-6 tw:max-w-2xl">
                        <h4 class="tw:font-semibold tw:text-xs tw:uppercase tw:tracking-wider tw:mb-4 tw:text-gray-500 tw:dark:text-dark-white-300">{{ __('Credentials') }}</h4>

                        {{-- Credential mode picker --}}
                        <div class="tw:mb-5">
                            <label class="tw:block tw:font-medium tw:mb-3 tw:text-gray-700 tw:dark:text-dark-white-200">{{ __('Credential Mode') }}</label>
                            <div class="tw:flex tw:gap-6">
                                <label class="tw:flex tw:items-center tw:cursor-pointer tw:group">
                                    <input type="radio" name="credential_mode" value="existing" x-model="credentialMode" class="tw:w-4 tw:h-4 tw:text-[#337ab7] tw:border-gray-300 tw:focus:ring-[#337ab7] tw:mr-2">
                                    <span class="tw:group-hover:text-[#337ab7] tw:transition-colors tw:text-gray-700 tw:dark:text-dark-white-200">{{ __('Use Existing Secret') }}</span>
                                </label>
                                <label class="tw:flex tw:items-center tw:cursor-pointer tw:group">
                                    <input type="radio" name="credential_mode" value="new" x-model="credentialMode" class="tw:w-4 tw:h-4 tw:text-[#337ab7] tw:border-gray-300 tw:focus:ring-[#337ab7] tw:mr-2">
                                    <span class="tw:group-hover:text-[#337ab7] tw:transition-colors tw:text-gray-700 tw:dark:text-dark-white-200">{{ __('Create New Secret') }}</span>
                                </label>
                            </div>
                        </div>

                        {{-- Existing secret picker --}}
                        <div x-show="credentialMode === 'existing'" style="display: none;" x-transition class="tw:mb-0">
                            <label class="tw:block tw:font-medium tw:mb-2 tw:text-gray-700 tw:dark:text-dark-white-200">{{ __('Select Secret') }}</label>
                            <select name="secret_id" class="form-control @error('secret_id') tw:border-red-500 @enderror">
                                <option value="">{{ __('Select an existing secret...') }}</option>
                                @foreach($availableSecrets[$method['type']] ?? [] as $secret)
                                    <option value="{{ $secret->id }}" {{ old('secret_id') == $secret->id ? 'selected' : '' }}>
                                        {{ $secret->description }}
                                    </option>
                                @endforeach
                            </select>
                            @error('secret_id')
                            <p class="tw:text-red-600 tw:dark:text-red-400 tw:text-sm tw:mt-1">{{ $message }}</p>
                            @enderror
                            @if(($availableSecrets[$method['type']] ?? collect())->isEmpty())
                                <p class="tw:text-sm tw:text-amber-600 tw:dark:text-amber-400 tw:mt-2">
                                    <i class="fa fa-exclamation-triangle tw:mr-1"></i>
                                    {{ __('No existing secrets found for this type.') }}
                                    <a href="#" x-on:click.prevent="credentialMode = 'new'" class="tw:underline tw:font-medium">{{ __('Create one instead.') }}</a>
                                </p>
                            @endif
                        </div>

                        {{-- New secret form --}}
                        <div x-show="credentialMode === 'new'" style="display: none;" x-transition>
                            <div class="tw:mb-4">
                                <label class="tw:block tw:font-medium tw:mb-2 tw:text-gray-700 tw:dark:text-dark-white-200">{{ __('Description') }}</label>
                                <input type="text" name="description" class="form-control" value="{{ old('description') }}">
                                <p class="tw:text-xs tw:text-gray-500 tw:dark:text-dark-white-400 tw:mt-1">{{ __('Optional. Leave blank to auto-generate.') }}</p>
                            </div>

                            <div class="tw:mb-5" x-data="{ isDefault: {{ old('default') ? 'true' : 'false' }} }">
                                <label class="tw:flex tw:items-center tw:cursor-pointer tw:group tw:px-4 tw:py-3 tw:rounded-lg tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:w-full">
                                    <span class="tw:relative tw:shrink-0">
                                        <input type="checkbox" name="default" value="1" class="tw:sr-only" x-model="isDefault">
                                        <span class="tw:block tw:w-10 tw:h-6 tw:rounded-full tw:transition-colors tw:duration-200" :class="isDefault ? 'tw:bg-[#337ab7]' : 'tw:bg-gray-300 tw:dark:bg-dark-gray-400'"></span>
                                        <span class="tw:absolute tw:left-1 tw:top-1 tw:w-4 tw:h-4 tw:rounded-full tw:bg-white tw:transition-transform tw:duration-200" :class="isDefault ? 'tw:translate-x-4' : 'tw:translate-x-0'"></span>
                                    </span>
                                    <span class="tw:ml-3 tw:font-medium tw:text-gray-700 tw:dark:text-dark-white-200">{{ __('Make Default') }}</span>
                                </label>
                            </div>

                            <div class="tw:p-5 tw:rounded-lg tw:border tw:border-gray-200 tw:dark:border-dark-gray-400"
                                 x-data='{ formData: @json($method["schema_defaults"]) }'>
                                <h5 class="tw:font-medium tw:text-lg tw:mb-4 tw:border-b tw:pb-2 tw:border-gray-200 tw:dark:border-dark-gray-400 tw:text-gray-800 tw:dark:text-dark-white-100">
                                    {{ $method['label'] }} {{ __('Details') }}
                                </h5>

                                @foreach($method['schema_fields'] as $field)
                                    <div class="tw:mb-4"
                                         @if($field['visible_if_expression']) x-show="{{ $field['visible_if_expression'] }}" @endif>
                                        <label class="tw:block tw:font-medium tw:mb-1 tw:text-gray-700 tw:dark:text-dark-white-200">
                                            {{ __($field['label']) }}
                                        </label>

                                        @if($field['field_type'] === 'select')
                                            <select name="secret_data[{{ $field['key'] }}]"
                                                    x-model="formData['{{ $field['key'] }}']"
                                                    class="form-control">
                                                @foreach($field['options'] as $optVal => $optLabel)
                                                    <option value="{{ $optVal }}">{{ __($optLabel) }}</option>
                                                @endforeach
                                            </select>
                                        @elseif($field['field_type'] === 'password')
                                            <input type="password"
                                                   name="secret_data[{{ $field['key'] }}]"
                                                   class="form-control"
                                                   autocomplete="new-password">
                                        @else
                                            <input type="text"
                                                   name="secret_data[{{ $field['key'] }}]"
                                                   x-model="formData['{{ $field['key'] }}']"
                                                   class="form-control"
                                                   value="{{ old('secret_data.' . $field['key'], '') }}">
                                        @endif

                                        @error('secret_data.' . $field['key'])
                                        <p class="tw:text-red-600 tw:dark:text-red-400 tw:text-sm tw:mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Settings Panel --}}
                @if(!empty($method['settings_fields']))
                    <div class="tw:bg-gray-50 tw:dark:bg-dark-gray-300 tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:rounded-xl tw:p-5 tw:mb-6 tw:max-w-2xl">
                        <h4 class="tw:font-semibold tw:text-xs tw:uppercase tw:tracking-wider tw:mb-4 tw:text-gray-500 tw:dark:text-dark-white-300">{{ __('Settings') }}</h4>

                        <div class="tw:grid tw:grid-cols-1 tw:gap-4">
                            @foreach($method['settings_fields'] as $setting)
                                <div @if($setting['visible_if_expression']) x-show="{{ $setting['visible_if_expression'] }}" @endif>
                                    <label class="tw:block tw:font-medium tw:mb-1 tw:text-gray-700 tw:dark:text-dark-white-200">
                                        {{ __('poller.method_settings.' . $method['type'] . '.' . $setting['key']) }}
                                    </label>

                                    @if(($setting['field_type'] ?? 'text') === 'select')
                                        <select name="settings[{{ $setting['key'] }}]" x-model="settingsData['{{ $setting['key'] }}']" class="form-control">
                                            @foreach($setting['options'] ?? [] as $optVal => $optLabel)
                                                <option value="{{ $optVal }}">{{ __($optLabel) }}</option>
                                            @endforeach
                                        </select>
                                    @elseif(($setting['field_type'] ?? 'text') === 'number')
                                        <input type="number" name="settings[{{ $setting['key'] }}]" x-model="settingsData['{{ $setting['key'] }}']" class="form-control"
                                               @if(isset($setting['min'])) min="{{ $setting['min'] }}" @endif
                                               @if(isset($setting['max'])) max="{{ $setting['max'] }}" @endif>
                                    @else
                                        <input type="text" name="settings[{{ $setting['key'] }}]" x-model="settingsData['{{ $setting['key'] }}']" class="form-control">
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endforeach

        {{-- Submit — only shown once a type is selected --}}
        <div x-show="methodType !== ''" style="display: none;" class="tw:mt-6 tw:pt-6 tw:border-t tw:border-gray-200 tw:dark:border-dark-gray-400">
            <button type="submit" class="btn btn-success tw:bg-[#449d44] tw:hover:bg-[#357a35] tw:border-[#449d44]">
                <i class="fa fa-plus tw:mr-1"></i> {{ __('Add Polling Type') }}
            </button>
        </div>
    </form>
@endif
