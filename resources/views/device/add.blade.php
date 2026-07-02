@extends('layouts.librenmsv1')

@php
    $oldActiveMethods = old('active_methods', ['snmp', 'icmp']);
@endphp

@section('title', __('Add Device'))

@section('content')
    <div class="container">
        <x-panel>
            <x-slot name="title">
                <i class="fa fa-plus fa-fw fa-lg" aria-hidden="true"></i> {{ __('Add Device') }}
            </x-slot>

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="tw:list-disc tw:list-inside tw:space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('device.add.store') }}"
                  x-data="{
                      activeTab: '{{ old('active_tab', 'snmp') }}',
                      activeMethods: @js($oldActiveMethods),
                      methods: {
                          @foreach($availableMethods as $method)
                          '{{ $method['type'] }}': {
                               validate: {{ old("polling_methods.{$method['type']}.validate") !== null ? (old("polling_methods.{$method['type']}.validate") ? 'true' : 'false') : 'true' }},
                               affects_availability: {{ old("polling_methods.{$method['type']}.affects_availability") !== null ? (old("polling_methods.{$method['type']}.affects_availability") ? 'true' : 'false') : (in_array($method['type'], ['snmp', 'icmp']) ? 'true' : 'false') }},
                               credential_mode: '{{ old("polling_methods.{$method['type']}.credential_mode", 'default') }}',
                               formData: @js(old("polling_methods.{$method['type']}.secret_data", $method['schema_defaults'] ?? [])),
                               settingsData: @js(old("polling_methods.{$method['type']}.settings", []))
                          },
                          @endforeach
                      },
                      allTypes: @js(collect($availableMethods)->map(fn($m) => ['type' => $m['type'], 'label' => $m['label']])->values()),
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
                  }">
                @csrf
                <input type="hidden" name="active_tab" :value="activeTab">
                <template x-for="method in activeMethods">
                    <input type="hidden" name="active_methods[]" :value="method">
                </template>

                {{-- General Properties Section --}}
                <div class="tw:bg-gray-50 tw:dark:bg-dark-gray-300 tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:rounded-xl tw:p-6 tw:mb-6">
                    <h3 class="tw:text-lg tw:font-semibold tw:mb-4 tw:text-gray-800 tw:dark:text-dark-white-100 tw:flex tw:items-center tw:gap-2">
                        <i class="fa fa-info-circle tw:text-[#337ab7]"></i>
                        {{ __('General Properties') }}
                    </h3>
                    <div class="tw:grid tw:grid-cols-1 tw:md:grid-cols-3 tw:gap-6">
                        <div class="form-group {{ $errors->has('hostname') ? 'has-error' : '' }} tw:mb-0">
                            <label for="hostname" class="control-label">{{ __('Hostname or IP') }}</label>
                            <input type="text" id="hostname" name="hostname" class="form-control"
                                   value="{{ old('hostname') }}" placeholder="device.example.com" required autofocus>
                            @if($errors->has('hostname'))
                                @foreach($errors->get('hostname') as $error)
                                    <span class="help-block">{{ $error }}</span>
                                @endforeach
                            @endif
                        </div>

                        <div class="form-group tw:mb-0">
                            <label for="port_assoc_mode" class="control-label">{{ __('Port Association Mode') }}</label>
                            <select id="port_assoc_mode" name="port_assoc_mode" class="form-control">
                                @foreach($port_association_modes as $mode)
                                    <option value="{{ $mode }}" {{ old('port_assoc_mode', $default_port_association_mode) === $mode ? 'selected' : '' }}>{{ $mode }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group tw:mb-0">
                            <label for="poller_group" class="control-label">{{ __('Poller Group') }}</label>
                            <select id="poller_group" name="poller_group" class="form-control">
                                <option value="0">{{ __('Default poller group') }}</option>
                                @foreach($poller_groups as $id => $name)
                                    <option value="{{ $id }}" {{ old('poller_group', $default_poller_group) == $id ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Polling Methods Section --}}
                <div class="tw:bg-white tw:dark:bg-dark-gray-500 tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:rounded-xl tw:p-6">
                    <h3 class="tw:text-lg tw:font-semibold tw:mb-4 tw:text-gray-800 tw:dark:text-dark-white-100 tw:flex tw:items-center tw:gap-2">
                        <i class="fa fa-sliders tw:text-[#337ab7]"></i>
                        {{ __('Polling Methods') }}
                    </h3>

                    <div class="tw:flex tw:flex-col tw:md:flex-row tw:gap-6">

                        {{-- Left: tab list --}}
                        <div class="tw:w-full tw:md:w-1/4 tw:shrink-0">
                            <ul class="tw:flex tw:flex-col tw:space-y-2">
                                @foreach($availableMethods as $method)
                                    <li x-show="activeMethods.includes('{{ $method['type'] }}')"
                                        :class="activeTab === '{{ $method['type'] }}'
                                            ? 'tw:bg-[#337ab7] tw:border-[#337ab7] tw:dark:bg-[#22527b]'
                                            : 'tw:border-gray-200 tw:hover:bg-gray-50 tw:dark:border-dark-gray-400 tw:dark:hover:bg-dark-gray-400'"
                                        class="tw:flex tw:items-center tw:border tw:rounded-lg tw:shadow-sm tw:transition-colors tw:overflow-hidden">
                                        <button type="button"
                                                @click="activeTab = '{{ $method['type'] }}'"
                                                :class="activeTab === '{{ $method['type'] }}' ? 'tw:text-white!' : 'tw:text-gray-700 tw:dark:text-dark-white-200'"
                                                class="tw:flex-1 tw:text-left tw:px-4 tw:py-3 tw:font-medium tw:transition-colors">
                                            {{ $method['label'] }}
                                        </button>
                                        <button type="button"
                                                @click="removeMethod('{{ $method['type'] }}')"
                                                :class="activeTab === '{{ $method['type'] }}' ? 'tw:text-blue-200 tw:hover:text-white' : 'tw:text-gray-400 tw:hover:text-red-500'"
                                                class="tw:px-3 tw:py-3 tw:shrink-0 tw:transition-colors"
                                                title="{{ __('Remove') }}">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    </li>
                                @endforeach

                                {{-- Add polling type --}}
                                <li class="tw:mt-4 tw:pt-2 tw:border-t tw:border-gray-200 tw:dark:border-dark-gray-400"
                                    x-show="addableRemaining.length > 0">
                                    <div class="input-group">
                                        <select id="add-method-select" class="form-control">
                                            <option value="">{{ __('Add polling type...') }}</option>
                                            @foreach($availableMethods as $method)
                                                <option value="{{ $method['type'] }}"
                                                        x-show="!activeMethods.includes('{{ $method['type'] }}')">
                                                    {{ $method['label'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <span class="input-group-btn">
                                            <button type="button" class="btn btn-success tw:bg-[#449d44] tw:hover:bg-[#357a35] tw:border-[#449d44]"
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

                        {{-- Right: tab panels --}}
                        <div class="tw:w-full tw:md:w-3/4 tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:rounded-lg tw:shadow-sm tw:p-6 tw:grow">
                            @foreach($availableMethods as $method)
                                <div x-show="activeTab === '{{ $method['type'] }}' && activeMethods.includes('{{ $method['type'] }}')"
                                     style="display: none;"
                                     x-transition>

                                    <div class="tw:text-2xl tw:font-semibold tw:mb-6 tw:pb-3 tw:border-b tw:border-gray-200 tw:dark:border-dark-gray-400 tw:text-gray-800 tw:dark:text-dark-white-100">
                                        {{ $method['label'] }} {{ __('Settings') }}
                                    </div>

                                    {{-- Active flag (submitted for all methods, controller ignores inactive ones) --}}
                                    <template x-if="activeMethods.includes('{{ $method['type'] }}')">
                                        <input type="hidden" name="polling_methods[{{ $method['type'] }}][active]" value="1">
                                    </template>

                                    {{-- Method Options --}}
                                    <div class="tw:mb-6">
                                        <h4 class="tw:font-semibold tw:text-xs tw:uppercase tw:tracking-wider tw:mb-3 tw:text-gray-500 tw:dark:text-dark-white-300">{{ __('Method Options') }}</h4>
                                        <div class="tw:grid tw:grid-cols-1 tw:md:grid-cols-2 tw:gap-4 tw:max-w-2xl">
                                            {{-- Validate on add toggle --}}
                                            <label class="tw:flex tw:items-center tw:cursor-pointer tw:group tw:px-4 tw:py-3 tw:rounded-lg tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:w-full">
                                                <div class="tw:relative tw:shrink-0">
                                                    <input type="hidden" name="polling_methods[{{ $method['type'] }}][validate]" value="0">
                                                    <input type="checkbox" name="polling_methods[{{ $method['type'] }}][validate]"
                                                           value="1" class="tw:sr-only"
                                                           x-model="methods['{{ $method['type'] }}'].validate">
                                                    <div class="tw:block tw:w-16 tw:h-9 tw:rounded-full tw:transition-colors tw:duration-200"
                                                         :class="methods['{{ $method['type'] }}'].validate ? 'tw:bg-[#337ab7]' : 'tw:bg-gray-300 tw:dark:bg-dark-gray-400'"></div>
                                                    <div class="tw:absolute tw:left-1 tw:top-1 tw:w-7 tw:h-7 tw:rounded-full tw:transition-transform tw:duration-200 tw:bg-white tw:shadow-sm"
                                                         :class="methods['{{ $method['type'] }}'].validate ? 'tw:translate-x-7' : 'tw:translate-x-0'"></div>
                                                </div>
                                                <span class="tw:ml-4 tw:font-medium tw:text-gray-700 tw:dark:text-dark-white-200">{{ __('Validate on add') }}</span>
                                            </label>

                                            {{-- Affects Availability toggle --}}
                                            <label class="tw:flex tw:items-center tw:cursor-pointer tw:group tw:px-4 tw:py-3 tw:rounded-lg tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:w-full">
                                                <div class="tw:relative tw:shrink-0">
                                                    <input type="hidden" name="polling_methods[{{ $method['type'] }}][affects_availability]" value="0">
                                                    <input type="checkbox" name="polling_methods[{{ $method['type'] }}][affects_availability]"
                                                           value="1" class="tw:sr-only"
                                                           x-model="methods['{{ $method['type'] }}'].affects_availability">
                                                    <div class="tw:block tw:w-16 tw:h-9 tw:rounded-full tw:transition-colors tw:duration-200"
                                                         :class="methods['{{ $method['type'] }}'].affects_availability ? 'tw:bg-[#337ab7]' : 'tw:bg-gray-300 tw:dark:bg-dark-gray-400'"></div>
                                                    <div class="tw:absolute tw:left-1 tw:top-1 tw:w-7 tw:h-7 tw:rounded-full tw:transition-transform tw:duration-200 tw:bg-white tw:shadow-sm"
                                                         :class="methods['{{ $method['type'] }}'].affects_availability ? 'tw:translate-x-7' : 'tw:translate-x-0'"></div>
                                                </div>
                                                <span class="tw:ml-4 tw:font-medium tw:text-gray-700 tw:dark:text-dark-white-200">{{ __('poller.affects_availability') }}</span>
                                            </label>
                                        </div>
                                    </div>

                                    {{-- Credentials --}}
                                    @if(!empty($method['schema_fields']))
                                        <div class="tw:bg-gray-50 tw:dark:bg-dark-gray-300 tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:rounded-xl tw:p-5 tw:mb-6">
                                            <h4 class="tw:font-semibold tw:text-xs tw:uppercase tw:tracking-wider tw:mb-4 tw:text-gray-500 tw:dark:text-dark-white-300">{{ __('Credentials') }}</h4>

                                            <div class="tw:flex tw:flex-wrap tw:gap-6 tw:mb-4">
                                                <label class="radio-inline">
                                                    <input type="radio"
                                                           name="polling_methods[{{ $method['type'] }}][credential_mode]"
                                                           value="default"
                                                           x-model="methods['{{ $method['type'] }}'].credential_mode">
                                                    {{ __('Attempt Defaults') }}
                                                </label>
                                                <label class="radio-inline">
                                                    <input type="radio"
                                                           name="polling_methods[{{ $method['type'] }}][credential_mode]"
                                                           value="existing"
                                                           x-model="methods['{{ $method['type'] }}'].credential_mode">
                                                    {{ __('Use Existing Secret') }}
                                                </label>
                                                <label class="radio-inline">
                                                    <input type="radio"
                                                           name="polling_methods[{{ $method['type'] }}][credential_mode]"
                                                           value="new"
                                                           x-model="methods['{{ $method['type'] }}'].credential_mode">
                                                    {{ __('Create New Secret') }}
                                                </label>
                                            </div>

                                            {{-- Existing secret picker --}}
                                            <div x-show="methods['{{ $method['type'] }}'].credential_mode === 'existing'"
                                                 style="display: none;"
                                                 class="form-group tw:max-w-md tw:mb-0">
                                                <label class="control-label">{{ __('Select Secret') }}</label>
                                                <select name="polling_methods[{{ $method['type'] }}][secret_id]" class="form-control">
                                                    <option value="">{{ __('Select an existing secret...') }}</option>
                                                    @foreach($availableSecrets[$method['type']] ?? [] as $secret)
                                                        <option value="{{ $secret->id }}"
                                                            {{ old("polling_methods.{$method['type']}.secret_id") == $secret->id ? 'selected' : '' }}>
                                                            {{ $secret->description }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            {{-- New secret form --}}
                                            <div x-show="methods['{{ $method['type'] }}'].credential_mode === 'new'"
                                                 style="display: none;">
                                                <div class="tw:grid tw:grid-cols-1 tw:md:grid-cols-2 tw:gap-4 tw:max-w-2xl tw:mb-4">
                                                    <div class="form-group">
                                                        <label class="control-label">{{ __('Secret Description') }}</label>
                                                        <input type="text"
                                                               name="polling_methods[{{ $method['type'] }}][description]"
                                                               class="form-control"
                                                               placeholder="{{ __('Optional') }}"
                                                               value="{{ old("polling_methods.{$method['type']}.description") }}">
                                                    </div>
                                                    <div class="form-group tw:flex tw:items-end">
                                                        <div class="checkbox tw:mb-0">
                                                            <label>
                                                                <input type="hidden" name="polling_methods[{{ $method['type'] }}][default]" value="0">
                                                                <input type="checkbox"
                                                                       name="polling_methods[{{ $method['type'] }}][default]"
                                                                       value="1"
                                                                    {{ old("polling_methods.{$method['type']}.default") ? 'checked' : '' }}>
                                                                {{ __('Make Default') }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="tw:grid tw:grid-cols-1 tw:md:grid-cols-2 tw:gap-4 tw:max-w-2xl">
                                                    @foreach($method['schema_fields'] as $field)
                                                        <div class="form-group"
                                                             @if($field['visible_if_expression']) x-show="{{ $field['visible_if_expression'] }}" @endif>
                                                            <label class="control-label">{{ __($field['label']) }}</label>
                                                            @if($field['field_type'] === 'select')
                                                                <select name="polling_methods[{{ $method['type'] }}][secret_data][{{ $field['key'] }}]"
                                                                        x-model="methods['{{ $method['type'] }}'].formData['{{ $field['key'] }}']"
                                                                        class="form-control">
                                                                    @foreach($field['options'] as $optVal => $optLabel)
                                                                        <option value="{{ $optVal }}">{{ __($optLabel) }}</option>
                                                                    @endforeach
                                                                </select>
                                                            @elseif($field['field_type'] === 'password')
                                                                <input type="password"
                                                                       name="polling_methods[{{ $method['type'] }}][secret_data][{{ $field['key'] }}]"
                                                                       class="form-control"
                                                                       autocomplete="new-password">
                                                            @else
                                                                <input type="text"
                                                                       name="polling_methods[{{ $method['type'] }}][secret_data][{{ $field['key'] }}]"
                                                                       x-model="methods['{{ $method['type'] }}'].formData['{{ $field['key'] }}']"
                                                                       class="form-control">
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Settings fields --}}
                                    @if(!empty($method['settings_fields']))
                                        <div class="tw:bg-gray-50 tw:dark:bg-dark-gray-300 tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:rounded-xl tw:p-5 tw:mb-6">
                                            <h4 class="tw:font-semibold tw:text-xs tw:uppercase tw:tracking-wider tw:mb-4 tw:text-gray-500 tw:dark:text-dark-white-300">{{ __('Settings') }}</h4>
                                            <div class="tw:grid tw:grid-cols-1 tw:md:grid-cols-2 tw:gap-4 tw:max-w-2xl">
                                                @foreach($method['settings_fields'] as $setting)
                                                    <div class="form-group tw:mb-0"
                                                         @if($setting['visible_if_expression']) x-show="{{ $setting['visible_if_expression'] }}" @endif>
                                                        <label class="control-label">{{ __('poller.method_settings.' . $method['type'] . '.' . $setting['key']) }}</label>
                                                        @if(($setting['field_type'] ?? 'text') === 'select')
                                                            <select name="polling_methods[{{ $method['type'] }}][settings][{{ $setting['key'] }}]"
                                                                    x-model="methods['{{ $method['type'] }}'].settingsData['{{ $setting['key'] }}']"
                                                                    class="form-control">
                                                                @foreach($setting['options'] ?? [] as $optVal => $optLabel)
                                                                    <option value="{{ $optVal }}">{{ __($optLabel) }}</option>
                                                                @endforeach
                                                            </select>
                                                        @elseif(($setting['field_type'] ?? 'text') === 'number')
                                                            <input type="number"
                                                                   name="polling_methods[{{ $method['type'] }}][settings][{{ $setting['key'] }}]"
                                                                   x-model="methods['{{ $method['type'] }}'].settingsData['{{ $setting['key'] }}']"
                                                                   class="form-control"
                                                                   @isset($setting['min']) min="{{ $setting['min'] }}" @endisset
                                                                   @isset($setting['max']) max="{{ $setting['max'] }}" @endisset>
                                                        @else
                                                            <input type="text"
                                                                   name="polling_methods[{{ $method['type'] }}][settings][{{ $setting['key'] }}]"
                                                                   x-model="methods['{{ $method['type'] }}'].settingsData['{{ $setting['key'] }}']"
                                                                   class="form-control">
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    {{-- SNMP-off manual overrides --}}
                                    @if($method['type'] === 'snmp')
                                        <div x-show="!methods['snmp'].validate"
                                             class="tw:bg-yellow-50/50 tw:dark:bg-dark-gray-300/50 tw:border tw:border-yellow-100 tw:dark:border-dark-gray-400 tw:rounded-xl tw:p-5 tw:mt-6"
                                             style="display: none;">
                                            <h4 class="tw:font-semibold tw:text-xs tw:uppercase tw:tracking-wider tw:mb-4 tw:text-yellow-800 tw:dark:text-yellow-600">{{ __('Manual Overrides') }}</h4>
                                            <div class="tw:grid tw:grid-cols-1 tw:md:grid-cols-3 tw:gap-4 tw:max-w-2xl">
                                                <div class="form-group tw:mb-0">
                                                    <label for="sysName" class="control-label">{{ __('sysName') }} <span class="text-muted">({{ __('optional') }})</span></label>
                                                    <input type="text" id="sysName" name="sysName" class="form-control" value="{{ old('sysName') }}">
                                                </div>
                                                <div class="form-group tw:mb-0">
                                                    <label for="hardware" class="control-label">{{ __('Hardware') }} <span class="text-muted">({{ __('optional') }})</span></label>
                                                    <input type="text" id="hardware" name="hardware" class="form-control" value="{{ old('hardware') }}">
                                                </div>
                                                <div class="form-group tw:mb-0" x-init="setTimeout(() => init_select2('#os-select', 'os', {}, null, '{{ __('OS (optional)') }}'), 100)">
                                                    <label for="os-select" class="control-label">{{ __('OS') }} <span class="text-muted">({{ __('optional') }})</span></label>
                                                    <select id="os-select" name="os" class="form-control"></select>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                </div>{{-- end panel --}}
                            @endforeach
                        </div>{{-- end right --}}

                    </div>{{-- end flex row --}}
                </div>

                <div class="tw:mt-6 tw:pt-6 tw:border-t tw:border-gray-200 tw:dark:border-dark-gray-400">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-plus tw:mr-1"></i> {{ __('Add Device') }}
                    </button>
                </div>

            </form>
        </x-panel>
    </div>
@endsection
