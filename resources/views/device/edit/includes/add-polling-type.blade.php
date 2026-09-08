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

    <form x-ref="addForm" method="POST" action="{{ route('device.edit.polling.store', $device) }}"
          x-data="{
              methodType: '{{ old('method_type', '') }}',
              credentialMode: '{{ old('credential_mode', 'existing') }}',
              loading: false,
              errors: {},
              unreachableDialog: false,
              unreachableMessage: '',
              unreachableDetails: '',
              saveAnyway() {
                  this.unreachableDialog = false;
                  this.submitForm(null, true);
              },
              async submitForm(e, force = false) {
                  this.loading = true;
                  this.errors = {};
                  const form = this.$refs.addForm;
                  const formData = new FormData(form);
                  if (force) {
                      formData.set('force_save', '1');
                  }
                  try {
                      const response = await fetch(form.action, {
                          method: 'POST',
                          headers: {
                              'Accept': 'application/json',
                              'X-Requested-With': 'XMLHttpRequest',
                              'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content') || form.querySelector('input[name=\"_token\"]')?.value || ''
                          },
                          body: formData
                      });
                      const data = await response.json();
                      if (!response.ok) {
                          if (data.status === 'unreachable') {
                              this.unreachableMessage = data.message || '{{ __('poller.reachability_check_failed') }}';
                              this.unreachableDetails = data.error_details || '';
                              this.unreachableDialog = true;
                          } else if (data.errors) {
                              this.errors = data.errors;
                          } else {
                              toastr.error(data.message || '{{ __('Failed to add polling method') }}');
                          }
                          return;
                        }
                      this.errors = {};
                      toastr.success(data.message || '{{ __('Polling method added') }}');
                      window.location.href = '{{ route('device.edit.polling', ['device' => $device]) }}?tab=' + encodeURIComponent(this.methodType);
                  } catch (err) {
                      toastr.error('{{ __('An error occurred while adding polling method.') }}');
                  } finally {
                      this.loading = false;
                  }
              }
          }"
          @submit.prevent="submitForm($event)">
        @csrf

        {{-- Step 1: Pick a polling type --}}
        <div class="tw:bg-gray-50 tw:dark:bg-dark-gray-300 tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:rounded-xl tw:p-6 tw:mb-6 tw:max-w-2xl" :class="(errors && errors['method_type']) ? 'has-error' : ''">
            <label class="tw:block tw:font-medium tw:mb-2 tw:text-gray-700 tw:dark:text-dark-white-200">{{ __('Polling Type') }}</label>
            <select name="method_type" x-model="methodType" class="form-control tw:rounded-lg tw:border-gray-200 tw:bg-white tw:dark:border-dark-gray-400 tw:dark:bg-dark-gray-500 tw:dark:text-white @error('method_type') tw:border-red-500 @enderror" required>
                <option value="">{{ __('Select a polling type...') }}</option>
                @foreach($unconfiguredMethods as $method)
                    <option value="{{ $method['type'] }}">{{ $method['label'] }}</option>
                @endforeach
            </select>
            @error('method_type')
            <p class="tw:text-red-600 tw:dark:text-red-400 tw:text-sm tw:mt-1">{{ $message }}</p>
            @enderror
            <template x-if="errors && errors['method_type']">
                <p class="tw:text-red-600 tw:dark:text-red-400 tw:text-sm tw:mt-1" x-text="errors['method_type']?.[0]"></p>
            </template>
        </div>

        {{-- Step 2: Per-method configuration --}}
        @foreach($unconfiguredMethods as $method)
            <div x-show="methodType === '{{ $method['type'] }}'" style="display: none;" x-transition
                 x-data="{ settingsData: @js(old('settings', array_merge($method['settings_defaults'] ?? [], $method['settings'] ?? []))) }">

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
                        <h4 class="tw:font-semibold tw:text-sm tw:uppercase tw:tracking-wider tw:mb-4 tw:text-gray-500 tw:dark:text-dark-white-300">{{ __('Credentials') }}</h4>

                        <div class="tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:p-5 tw:rounded-lg tw:text-sm tw:bg-white tw:dark:bg-dark-gray-500">
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
                            <div x-show="credentialMode === 'existing'" style="display: none;" x-transition class="tw:mb-0" :class="(errors && errors['secret_id']) ? 'has-error' : ''">
                                <x-select2
                                    :id="'secret-select-add-' . $method['type']"
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
                                @error('secret_id')
                                <p class="tw:text-red-600 tw:dark:text-red-400 tw:text-sm tw:mt-1">{{ $message }}</p>
                                @enderror
                                <template x-if="errors && errors['secret_id']">
                                    <p class="tw:text-red-600 tw:dark:text-red-400 tw:text-sm tw:mt-1" x-text="errors['secret_id']?.[0]"></p>
                                </template>
                                @if(($availableSecrets[$method['type']] ?? collect())->isEmpty())
                                    <p class="tw:text-sm tw:text-amber-600 tw:dark:text-amber-400 tw:mt-2">
                                        <i class="fa fa-exclamation-triangle tw:mr-1"></i>
                                        {{ __('No existing secrets found for this type.') }}
                                        <a href="#" x-on:click.prevent="credentialMode = 'new'" class="tw:underline tw:font-medium">{{ __('Create one instead.') }}</a>
                                    </p>
                                @endif
                            </div>

                            {{-- New secret form --}}
                            <div x-show="credentialMode === 'new'" style="display: none;" x-transition
                                 x-data="{ description: @js(old('description', strtoupper($method['type']) . ' ' . $device->hostname)) }">
                                 <div class="tw:mb-4" :class="(errors && errors['description']) ? 'has-error' : ''">
                                     <label class="tw:block tw:font-medium tw:mb-2 tw:text-gray-700 tw:dark:text-dark-white-200">{{ __('Description') }}</label>
                                     <input type="text" name="description" x-model="description" class="form-control @error('description') tw:border-red-500 @enderror">
                                     @error('description')
                                     <p class="tw:text-red-600 tw:dark:text-red-400 tw:text-sm tw:mt-1">{{ $message }}</p>
                                     @enderror
                                     <template x-if="errors && errors['description']">
                                         <p class="tw:text-red-600 tw:dark:text-red-400 tw:text-sm tw:mt-1" x-text="errors['description']?.[0]"></p>
                                     </template>
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

                                    <x-field-schema-fields
                                        :fields="$method['schema_fields']"
                                        :method-type="$method['type']"
                                        name-prefix="secret_data"
                                        model-prefix="formData"
                                        :grid="true" />
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Settings Panel --}}
                @if(!empty($method['settings_fields']))
                    <div class="tw:bg-gray-50 tw:dark:bg-dark-gray-300 tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:rounded-xl tw:p-5 tw:mb-6 tw:max-w-2xl">
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
            </div>
        @endforeach

        {{-- Submit — only shown once a type is selected --}}
        <div x-show="methodType !== ''" style="display: none;" class="tw:mt-6 tw:pt-6 tw:border-t tw:border-gray-200 tw:dark:border-dark-gray-400">
            <button type="submit" :disabled="loading" class="btn btn-success tw:bg-emerald-600 tw:hover:bg-emerald-700 tw:border-emerald-600">
                <template x-if="loading"><i class="fa fa-spinner fa-spin tw:mr-1"></i></template>
                <template x-if="!loading"><i class="fa fa-plus tw:mr-1"></i></template>
                {{ __('Add Polling Type') }}
            </button>
        </div>

        {{-- Reachability Failure Dialog --}}
        <template x-teleport="body">
            <div x-show="unreachableDialog" x-cloak style="display: none;"
                 class="tw:fixed tw:inset-0 tw:z-100 tw:flex tw:items-center tw:justify-center tw:p-4 tw:bg-black/60 tw:backdrop-blur-xs"
                 @click="unreachableDialog = false"
                 @keydown.escape.window="unreachableDialog = false">
                <div x-show="unreachableDialog"
                     x-transition:enter="tw:ease-out tw:duration-300"
                     x-transition:enter-start="tw:opacity-0 tw:scale-95"
                     x-transition:enter-end="tw:opacity-100 tw:scale-100"
                     x-transition:leave="tw:ease-in tw:duration-200"
                     x-transition:leave-start="tw:opacity-100 tw:scale-100"
                     x-transition:leave-end="tw:opacity-0 tw:scale-95"
                     @click.stop
                     class="tw:w-full tw:max-w-lg tw:bg-white tw:dark:bg-dark-gray-500 tw:border tw:border-gray-200 tw:dark:border-dark-gray-300 tw:rounded-xl tw:shadow-2xl tw:p-6"
                     role="dialog" aria-modal="true" aria-labelledby="modal-title">

                    <div class="tw:flex tw:items-start tw:gap-4">
                        <div class="tw:shrink-0 tw:flex tw:items-center tw:justify-center tw:h-12 tw:w-12 tw:rounded-full tw:bg-amber-100 tw:dark:bg-amber-900/50">
                            <i class="fa fa-exclamation-triangle tw:text-amber-600 tw:dark:text-amber-400 tw:text-xl"></i>
                        </div>
                        <div class="tw:grow">
                            <h3 class="tw:text-lg tw:font-semibold tw:text-gray-900 tw:dark:text-dark-white-100 tw:m-0" id="modal-title">
                                {{ __('poller.reachability_check_failed') }}
                            </h3>
                            <div class="tw:mt-2">
                                <p class="tw:text-sm tw:text-gray-600 tw:dark:text-dark-white-300" x-text="unreachableMessage"></p>
                                <template x-if="unreachableDetails">
                                    <div class="tw:mt-3 tw:p-3 tw:bg-gray-100 tw:dark:bg-dark-gray-600 tw:rounded tw:text-xs tw:font-mono tw:text-gray-800 tw:dark:text-dark-white-200 tw:overflow-x-auto tw:max-h-40" x-text="unreachableDetails"></div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="tw:mt-6 tw:flex tw:flex-col-reverse tw:sm:flex-row tw:justify-end tw:gap-3">
                        <button type="button" @click="unreachableDialog = false" class="btn btn-default">
                            {{ __('Edit Settings') }}
                        </button>
                        <button type="button" @click="saveAnyway()" class="btn btn-warning">
                            <i class="fa fa-save tw:mr-1"></i> {{ __('Save Anyway') }}
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </form>
@endif
