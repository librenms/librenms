@extends('layouts.librenmsv1')


@section('title', __('Add Device'))

@section('content')
    <div class="container">
        <x-panel>
            <x-slot name="title">
                <i class="fa fa-plus fa-fw fa-lg" aria-hidden="true"></i> {{ __('Add Device') }}
            </x-slot>

            <form method="POST" action="{{ route('device.add.store') }}"
                  @submit.prevent="submitForm()"
                  x-data="{
                      hostname: @js(old('hostname', '')),
                      poller_group: @js(old('poller_group', $default_poller_group)),
                      sysName: @js(old('sysName', '')),
                      hardware: @js(old('hardware', '')),
                      os: @js(old('os', '')),
                      activeTab: '{{ old('active_tab', 'snmp') }}',
                      activeMethods: @js($oldActiveMethods),
                      methods: {
                          @foreach($availableMethods as $method)
                          '{{ $method['type'] }}': {
                               validate: {{ old("polling_methods.{$method['type']}.validate") !== null ? (old("polling_methods.{$method['type']}.validate") ? 'true' : 'false') : 'true' }},
                               affects_availability: {{ old("polling_methods.{$method['type']}.affects_availability") !== null ? (old("polling_methods.{$method['type']}.affects_availability") ? 'true' : 'false') : (in_array($method['type'], ['snmp', 'icmp']) ? 'true' : 'false') }},
                               credential_mode: '{{ old("polling_methods.{$method['type']}.credential_mode", 'default') }}',
                               secret_id: '{{ old("polling_methods.{$method['type']}.secret_id", '') }}',
                               description: '{{ old("polling_methods.{$method['type']}.description", '') }}',
                               default: {{ old("polling_methods.{$method['type']}.default") ? 'true' : 'false' }},
                               formData: @js(old("polling_methods.{$method['type']}.secret_data", $method['schema_defaults'] ?? [])),
                               settingsData: @js(old("polling_methods.{$method['type']}.settings", $method['settings_defaults'] ?? []))
                          },
                          @endforeach
                      },
                      allTypes: @js(collect($availableMethods)->map(fn($m) => ['type' => $m['type'], 'label' => $m['label']])->values()),
                      loading: false,
                      errors: [],
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
                      async submitForm() {
                          if (this.loading) return;
                          this.loading = true;
                          this.errors = [];

                          const pollingMethods = {};
                          for (const type of this.activeMethods) {
                              const m = this.methods[type] || {};
                              const methodPayload = {
                                  active: 1,
                                  validate: m.validate ? 1 : 0,
                                  affects_availability: m.affects_availability ? 1 : 0,
                                  credential_mode: m.credential_mode,
                                  settings: m.settingsData || {},
                              };

                              if (m.credential_mode === 'existing') {
                                  methodPayload.secret_id = m.secret_id;
                              } else if (m.credential_mode === 'new') {
                                  methodPayload.description = m.description;
                                  methodPayload.default = m.default ? 1 : 0;
                                  methodPayload.secret_data = m.formData || {};
                              }

                              pollingMethods[type] = methodPayload;
                          }

                          let selectedOs = this.os;
                          if (!this.activeMethods.includes('snmp')) {
                              const osSelect = document.getElementById('os-select');
                              if (osSelect && typeof $ !== 'undefined' && $(osSelect).val()) {
                                  selectedOs = $(osSelect).val();
                              }
                          }

                          const payload = {
                              _token: '{{ csrf_token() }}',
                              hostname: this.hostname,
                              poller_group: this.poller_group,
                              active_tab: this.activeTab,
                              active_methods: this.activeMethods,
                              polling_methods: pollingMethods,
                              sysName: this.sysName,
                              hardware: this.hardware,
                              os: selectedOs,
                          };

                          try {
                              const response = await fetch('{{ route('device.add.store') }}', {
                                  method: 'POST',
                                  headers: {
                                      'Content-Type': 'application/json',
                                      'Accept': 'application/json',
                                      'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                      'X-Requested-With': 'XMLHttpRequest',
                                  },
                                  body: JSON.stringify(payload),
                              });

                              const data = await response.json().catch(() => ({}));

                              if (response.ok && data.redirect) {
                                  window.location.href = data.redirect;
                                  return;
                              }

                              if (data.errors) {
                                  const flatErrors = [];
                                  for (const field in data.errors) {
                                      const fieldErrors = data.errors[field];
                                      if (Array.isArray(fieldErrors)) {
                                          flatErrors.push(...fieldErrors);
                                      } else if (typeof fieldErrors === 'string') {
                                          flatErrors.push(fieldErrors);
                                      }
                                  }
                                  this.errors = flatErrors.length > 0 ? flatErrors : [data.message || '{{ __('Failed to save device.') }}'];
                              } else if (data.message) {
                                  this.errors = [data.message];
                              } else {
                                  this.errors = ['{{ __('Failed to save device.') }}'];
                              }

                              window.scrollTo({ top: 0, behavior: 'smooth' });
                          } catch (err) {
                              console.error(err);
                              this.errors = [err.message || '{{ __('An unexpected error occurred.') }}'];
                              window.scrollTo({ top: 0, behavior: 'smooth' });
                          } finally {
                              this.loading = false;
                          }
                      },
                  }">
                @csrf
                <template x-if="errors.length > 0">
                    <div class="alert alert-danger tw:mb-6">
                        <ul class="tw:list-disc tw:list-inside tw:space-y-1">
                            <template x-for="(error, index) in errors" :key="index">
                                <li x-text="error"></li>
                            </template>
                        </ul>
                    </div>
                </template>

                <input type="hidden" name="active_tab" :value="activeTab">
                <template x-for="method in activeMethods">
                    <input type="hidden" name="active_methods[]" :value="method">
                </template>

                {{-- General Properties Section --}}
                <div class="tw:bg-gray-50 tw:dark:bg-dark-gray-300 tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:rounded-xl tw:p-6 tw:mb-6">
                    <div class="tw:text-lg tw:font-semibold tw:mb-4 tw:text-gray-800 tw:dark:text-dark-white-100 tw:flex tw:items-center tw:gap-2">
                        <i class="fa fa-info-circle tw:text-[#337ab7]"></i>
                        {{ __('General Properties') }}
                    </div>
                    <div class="tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:p-5 tw:rounded-lg tw:bg-white tw:dark:bg-dark-gray-500">
                        <div class="tw:grid tw:grid-cols-1 tw:md:grid-cols-2 tw:gap-6">
                            <div class="form-group @error('hostname') has-error @enderror tw:mb-0">
                                <label for="hostname" class="control-label">{{ __('Hostname or IP') }}</label>
                                <input type="text" id="hostname" name="hostname" class="form-control"
                                       x-model="hostname" placeholder="device.example.com" required autofocus>
                                @error('hostname')
                                    <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                            @config('distributed_poller')
                            <div class="form-group tw:mb-0">
                                <label for="poller_group" class="control-label">{{ __('Poller Group') }}</label>
                                <select id="poller_group" name="poller_group" x-model="poller_group" class="form-control">
                                    <option value="0">{{ __('Default poller group') }}</option>
                                    @foreach($poller_groups as $group)
                                        <option value="{{ $group->id }}">{{ $group->group_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endconfig
                        </div>
                    </div>
                </div>

                {{-- Polling Methods Section --}}
                <div class="tw:bg-gray-50 tw:dark:bg-dark-gray-300 tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:rounded-xl tw:p-6">
                    <div class="tw:text-lg tw:font-semibold tw:mb-4 tw:text-gray-800 tw:dark:text-dark-white-100 tw:flex tw:items-center tw:gap-2">
                        <i class="fa fa-sliders tw:text-[#337ab7]"></i>
                        {{ __('Polling Methods') }}
                    </div>

                    <div class="tw:flex tw:flex-col tw:md:flex-row tw:gap-6">

                        {{-- Left: tab list --}}
                        <div class="tw:w-full tw:md:w-1/4 tw:shrink-0">
                            <ul class="tw:flex tw:flex-col tw:space-y-2">
                                @foreach($availableMethods as $method)
                                    <li x-show="activeMethods.includes('{{ $method['type'] }}')"
                                        x-cloak
                                        :class="activeTab === '{{ $method['type'] }}'
                                            ? 'tw:bg-blue-600 tw:border-blue-600 tw:dark:bg-blue-700 tw:dark:border-blue-700'
                                            : 'tw:border-gray-200 tw:hover:bg-gray-50 tw:dark:border-dark-gray-400 tw:dark:hover:bg-dark-gray-400'"
                                        class="tw:flex tw:items-center tw:border tw:rounded-lg tw:shadow-sm tw:transition-colors tw:overflow-hidden">
                                        <button type="button"
                                                @click="activeTab = '{{ $method['type'] }}'"
                                                :class="activeTab === '{{ $method['type'] }}' ? 'tw:text-white!' : 'tw:text-gray-700 tw:dark:text-dark-white-200'"
                                                class="tw:flex-1 tw:text-left tw:px-4 tw:py-3 tw:font-medium tw:transition-colors tw:flex tw:items-center">
                                            <i class="fa fa-fw {{ $method['icon'] }} tw:mr-2"></i>
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
                                     x-show="addableRemaining.length > 0"
                                     x-cloak>
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
                                                {{-- Validate on add toggle --}}
                                                <label class="tw:flex tw:items-center tw:cursor-pointer tw:group tw:px-4 tw:py-3 tw:rounded-lg tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:bg-white tw:dark:bg-dark-gray-500 tw:w-full">
                                                    <div class="tw:relative tw:shrink-0">
                                                        <input type="hidden" name="polling_methods[{{ $method['type'] }}][validate]" value="0">
                                                        <input type="checkbox" name="polling_methods[{{ $method['type'] }}][validate]"
                                                               value="1" class="tw:sr-only"
                                                               x-model="methods['{{ $method['type'] }}'].validate">
                                                        <div class="tw:block tw:w-12 tw:h-7 tw:rounded-full tw:transition-colors tw:duration-200"
                                                             :class="methods['{{ $method['type'] }}'].validate ? 'tw:bg-blue-600' : 'tw:bg-gray-300 tw:dark:bg-dark-gray-400'"></div>
                                                        <div class="tw:absolute tw:left-0.5 tw:top-0.5 tw:w-6 tw:h-6 tw:rounded-full tw:transition-transform tw:duration-200 tw:bg-white tw:shadow-sm"
                                                             :class="methods['{{ $method['type'] }}'].validate ? 'tw:translate-x-5' : 'tw:translate-x-0'"></div>
                                                    </div>
                                                    <span class="tw:ml-3 tw:font-medium tw:text-gray-700 tw:dark:text-dark-white-200">{{ __('Validate on add') }}</span>
                                                </label>

                                                {{-- Affects Availability toggle --}}
                                                <label class="tw:flex tw:items-center tw:cursor-pointer tw:group tw:px-4 tw:py-3 tw:rounded-lg tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:bg-white tw:dark:bg-dark-gray-500 tw:w-full">
                                                    <div class="tw:relative tw:shrink-0">
                                                        <input type="hidden" name="polling_methods[{{ $method['type'] }}][affects_availability]" value="0">
                                                        <input type="checkbox" name="polling_methods[{{ $method['type'] }}][affects_availability]"
                                                               value="1" class="tw:sr-only"
                                                               x-model="methods['{{ $method['type'] }}'].affects_availability">
                                                        <div class="tw:block tw:w-12 tw:h-7 tw:rounded-full tw:transition-colors tw:duration-200"
                                                             :class="methods['{{ $method['type'] }}'].affects_availability ? 'tw:bg-blue-600' : 'tw:bg-gray-300 tw:dark:bg-dark-gray-400'"></div>
                                                        <div class="tw:absolute tw:left-0.5 tw:top-0.5 tw:w-6 tw:h-6 tw:rounded-full tw:transition-transform tw:duration-200 tw:bg-white tw:shadow-sm"
                                                             :class="methods['{{ $method['type'] }}'].affects_availability ? 'tw:translate-x-5' : 'tw:translate-x-0'"></div>
                                                    </div>
                                                    <span class="tw:ml-3 tw:font-medium tw:text-gray-700 tw:dark:text-dark-white-200">{{ __('poller.affects_availability') }}</span>
                                                </label>
                                            </div>
                                        </div>

                                        {{-- Credentials --}}
                                        @if(!empty($method['schema_fields']))
                                            <div class="tw:bg-gray-50 tw:dark:bg-dark-gray-300 tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:rounded-xl tw:p-5 tw:mb-6">
                                                <h4 class="tw:font-semibold tw:text-sm tw:uppercase tw:tracking-wider tw:mb-4 tw:text-gray-500 tw:dark:text-dark-white-300">{{ __('Credentials') }}</h4>

                                                <div class="tw:font-medium tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:p-5 tw:rounded-lg tw:bg-white tw:dark:bg-dark-gray-500">
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
                                                    <template x-if="methods['{{ $method['type'] }}'].credential_mode === 'existing'">
                                                        <div class="form-group tw:max-w-md tw:mb-0">
                                                            <label class="control-label">{{ __('Select Secret') }}</label>
                                                            <select name="polling_methods[{{ $method['type'] }}][secret_id]"
                                                                    x-model="methods['{{ $method['type'] }}'].secret_id"
                                                                    class="form-control">
                                                                <option value="">{{ __('Select an existing secret...') }}</option>
                                                                @foreach($availableSecrets[$method['type']] ?? [] as $secret)
                                                                    <option value="{{ $secret->id }}">
                                                                        {{ $secret->description }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </template>

                                                    {{-- New secret form --}}
                                                    <template x-if="methods['{{ $method['type'] }}'].credential_mode === 'new'">
                                                        <div>
                                                            <div class="tw:grid tw:grid-cols-1 tw:md:grid-cols-2 tw:gap-4 tw:max-w-2xl tw:mb-4">
                                                                <div class="form-group">
                                                                    <label class="control-label">{{ __('Secret Description') }}</label>
                                                                    <input type="text"
                                                                           name="polling_methods[{{ $method['type'] }}][description]"
                                                                           class="form-control"
                                                                           placeholder="{{ __('Optional') }}"
                                                                           x-model="methods['{{ $method['type'] }}'].description">
                                                                </div>
                                                                <div class="form-group tw:flex tw:items-end">
                                                                    <div class="checkbox tw:mb-0">
                                                                        <label>
                                                                            <input type="hidden" name="polling_methods[{{ $method['type'] }}][default]" value="0">
                                                                            <input type="checkbox"
                                                                                   name="polling_methods[{{ $method['type'] }}][default]"
                                                                                   value="1"
                                                                                   x-model="methods['{{ $method['type'] }}'].default">
                                                                            {{ __('Make Default') }}
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            @php
                                                                $secretNamePrefix = 'polling_methods[' . $method['type'] . '][secret_data]';
                                                                $secretModelPrefix = "methods['" . $method['type'] . "'].formData";
                                                            @endphp
                                                            <x-field-schema-fields
                                                                :fields="$method['schema_fields']"
                                                                :method-type="$method['type']"
                                                                :name-prefix="$secretNamePrefix"
                                                                :model-prefix="$secretModelPrefix"
                                                                :grid="true" />
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Settings fields --}}
                                        @if(!empty($method['settings_fields']))
                                            <div class="tw:bg-gray-50 tw:dark:bg-dark-gray-300 tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:rounded-xl tw:p-5 tw:mb-6">
                                                <h4 class="tw:font-semibold tw:text-sm tw:uppercase tw:tracking-wider tw:mb-4 tw:text-gray-500 tw:dark:text-dark-white-300">{{ __('Settings') }}</h4>
                                                <div class="tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:p-5 tw:rounded-lg tw:bg-white tw:dark:bg-dark-gray-500">
                                                    @php
                                                        $settingsNamePrefix = 'polling_methods[' . $method['type'] . '][settings]';
                                                        $settingsModelPrefix = "methods['" . $method['type'] . "'].settingsData";
                                                    @endphp
                                                    <x-field-schema-fields
                                                        :fields="$method['settings_fields']"
                                                        :method-type="$method['type']"
                                                        :name-prefix="$settingsNamePrefix"
                                                        :model-prefix="$settingsModelPrefix"
                                                        :grid="true" />
                                                </div>
                                            </div>
                                        @endif

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
                     style="display: none;"
                     x-transition>
                    <div class="tw:text-lg tw:font-semibold tw:mb-4 tw:text-gray-800 tw:dark:text-dark-white-100 tw:flex tw:items-center tw:gap-2">
                        <i class="fa fa-wrench tw:text-[#337ab7]"></i>
                        {{ __('Manual Overrides') }}
                    </div>
                    <div class="tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:p-5 tw:rounded-lg tw:bg-white tw:dark:bg-dark-gray-500">
                        <div class="tw:grid tw:grid-cols-1 tw:md:grid-cols-3 tw:gap-4 tw:max-w-2xl">
                            <div class="form-group tw:mb-0">
                                <label for="sysName" class="control-label">{{ __('sysName') }} <span class="text-muted">({{ __('optional') }})</span></label>
                                <input type="text" id="sysName" name="sysName" class="form-control" x-model="sysName">
                            </div>
                            <div class="form-group tw:mb-0">
                                <label for="hardware" class="control-label">{{ __('Hardware') }} <span class="text-muted">({{ __('optional') }})</span></label>
                                <input type="text" id="hardware" name="hardware" class="form-control" x-model="hardware">
                            </div>
                            <div class="form-group tw:mb-0" x-init="setTimeout(() => init_select2('#os-select', 'os', {}, null, '{{ __('OS (optional)') }}'), 100)">
                                <label for="os-select" class="control-label">{{ __('OS') }} <span class="text-muted">({{ __('optional') }})</span></label>
                                <select id="os-select" name="os" class="form-control"></select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tw:mt-6 tw:pt-6 tw:border-t tw:border-gray-200 tw:dark:border-dark-gray-400">
                    <button type="submit" :disabled="loading" class="btn btn-primary tw:bg-blue-600 tw:border-blue-600 tw:hover:bg-blue-700">
                        <template x-if="loading"><i class="fa fa-spinner fa-spin tw:mr-1"></i></template>
                        <template x-if="!loading"><i class="fa fa-plus tw:mr-1"></i></template>
                        {{ __('Add Device') }}
                    </button>
                </div>
            </form>
        </x-panel>
    </div>
@endsection

@push('scripts')
    <script>
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
