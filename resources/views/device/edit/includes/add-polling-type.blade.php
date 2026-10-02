@if($unconfiguredMethods->isEmpty())
    <div class="tw:bg-blue-50 tw:text-blue-800 tw:p-4 tw:rounded-lg tw:border tw:border-blue-200 tw:dark:bg-transparent tw:dark:text-blue-300 tw:dark:border-dark-gray-400">
        <i class="fa fa-info-circle tw:mr-2"></i> {{ __('All available polling types are already configured for this device.') }}
    </div>
@else
    <form x-ref="addForm" method="POST" action="{{ route('device.edit.polling.store', $device) }}"
          x-data="addPollingTypeForm(@js([
              'methodType' => '',
              'secretMode' => 'existing',
              'redirectUrl' => route('device.edit.polling', ['device' => $device]),
              'labels' => [
                  'unreachable' => __('poller.reachability_check_failed'),
                  'failed' => __('Failed to add polling method'),
                  'added' => __('Polling method added'),
                  'error' => __('An error occurred while adding polling method.'),
              ],
          ]))"
          @submit.prevent="submitForm()">
        @csrf

        {{-- Step 1: Pick a polling type --}}
        <div class="tw:bg-gray-50 tw:dark:bg-dark-gray-300 tw:border tw:border-gray-200 tw:dark:border-dark-gray-400 tw:rounded-xl tw:p-6 tw:mb-6 tw:max-w-2xl" :class="(errors && errors['method_type']) ? 'has-error' : ''">
            <label class="tw:block tw:font-medium tw:mb-2 tw:text-gray-700 tw:dark:text-dark-white-200">{{ __('Polling Type') }}</label>
            <select name="method_type" x-model="methodType" class="form-control tw:rounded-lg tw:border-gray-200 tw:bg-white tw:dark:border-dark-gray-400 tw:dark:bg-dark-gray-500 tw:dark:text-white" required>
                <option value="">{{ __('Select a polling type...') }}</option>
                @foreach($unconfiguredMethods as $method)
                    <option value="{{ $method['type'] }}">{{ $method['label'] }}</option>
                @endforeach
            </select>
            <template x-if="errors && errors['method_type']">
                <p class="tw:text-red-600 tw:dark:text-red-400 tw:text-sm tw:mt-1" x-text="errors['method_type']?.[0]"></p>
            </template>
        </div>

        {{-- Step 2: Per-method configuration --}}
        @foreach($unconfiguredMethods as $method)
            <div x-show="methodType === '{{ $method['type'] }}'" x-cloak x-transition
                 x-data="{ settingsData: @js($method['settings'] ?? []) }">

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
                            <input type="hidden" name="secret_mode" :value="secretMode" :disabled="methodType !== '{{ $method['type'] }}'">
                            {{-- Credential mode picker --}}
                            <div class="tw:mb-5">
                                <label class="tw:block tw:font-medium tw:mb-3 tw:text-gray-700 tw:dark:text-dark-white-200">{{ __('Credential Mode') }}</label>
                                <div class="tw:flex tw:gap-6">
                                    <label class="tw:flex tw:items-center tw:cursor-pointer tw:group">
                                        <input type="radio" name="secret_mode_{{ $method['type'] }}" value="existing" x-model="secretMode" class="tw:w-4 tw:h-4 tw:text-[#337ab7] tw:border-gray-300 tw:focus:ring-[#337ab7] tw:mr-2">
                                        <span class="tw:group-hover:text-[#337ab7] tw:transition-colors tw:text-gray-700 tw:dark:text-dark-white-200">{{ __('Use Existing Secret') }}</span>
                                    </label>
                                    <label class="tw:flex tw:items-center tw:cursor-pointer tw:group">
                                        <input type="radio" name="secret_mode_{{ $method['type'] }}" value="new" x-model="secretMode" class="tw:w-4 tw:h-4 tw:text-[#337ab7] tw:border-gray-300 tw:focus:ring-[#337ab7] tw:mr-2">
                                        <span class="tw:group-hover:text-[#337ab7] tw:transition-colors tw:text-gray-700 tw:dark:text-dark-white-200">{{ __('Create New Secret') }}</span>
                                    </label>
                                </div>
                            </div>

                            {{-- Existing secret picker --}}
                            <div x-show="secretMode === 'existing'" x-cloak x-transition class="tw:mb-0" :class="(errors && errors['secret_id']) ? 'has-error' : ''">
                                <fieldset :disabled="methodType !== '{{ $method['type'] }}' || secretMode !== 'existing'" class="tw:border-0 tw:p-0 tw:m-0">
                                    <x-select2
                                        :id="'secret-select-add-' . $method['type']"
                                        name="secret_id"
                                        :label="__('Select Secret')"
                                        type="secret"
                                        :data="['secret_type' => $method['type']]"
                                        :placeholder="__('Select an existing secret...')"
                                        :allow-clear="false"
                                        class="tw:max-w-md"
                                    >
                                        @foreach($availableSecrets[$method['type']] ?? [] as $secret)
                                            <option value="{{ $secret->id }}">
                                                {{ $secret->description }}
                                            </option>
                                        @endforeach
                                    </x-select2>
                                </fieldset>
                                <template x-if="errors && errors['secret_id']">
                                    <p class="tw:text-red-600 tw:dark:text-red-400 tw:text-sm tw:mt-1" x-text="errors['secret_id']?.[0]"></p>
                                </template>
                                @if(($availableSecrets[$method['type']] ?? collect())->isEmpty())
                                    <p class="tw:text-sm tw:text-amber-600 tw:dark:text-amber-400 tw:mt-2">
                                        <i class="fa fa-exclamation-triangle tw:mr-1"></i>
                                        {{ __('No existing secrets found for this type.') }}
                                        <a href="#" x-on:click.prevent="secretMode = 'new'" class="tw:underline tw:font-medium">{{ __('Create one instead.') }}</a>
                                    </p>
                                @endif
                            </div>

                            {{-- New secret form --}}
                            <div x-show="secretMode === 'new'" x-cloak x-transition
                                 x-data="{ description: @js($method['default_secret_description']), formData: @js($method['schema_defaults'] ?? []) }">
                                <fieldset :disabled="methodType !== '{{ $method['type'] }}' || secretMode !== 'new'" class="tw:border-0 tw:p-0 tw:m-0">
                                    <x-device.polling.new-secret-fields
                                        :method="$method"
                                        name-prefix="secret_data"
                                        model-prefix="formData"
                                        description-name="description"
                                        description-model="description"
                                        :error-key="'description'"
                                    />
                                </fieldset>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Settings Panel --}}
                <div class="tw:max-w-2xl">
                    <fieldset :disabled="methodType !== '{{ $method['type'] }}'" class="tw:border-0 tw:p-0 tw:m-0">
                        <x-device.polling.settings
                            :method="$method"
                            name-prefix="settings"
                            model-prefix="settingsData"
                        />
                    </fieldset>
                </div>
            </div>
        @endforeach

        {{-- Submit — only shown once a type is selected --}}
        <div x-show="methodType !== ''" x-cloak class="tw:mt-6 tw:pt-6 tw:border-t tw:border-gray-200 tw:dark:border-dark-gray-400">
            <button type="submit" :disabled="loading" class="btn btn-success tw:bg-emerald-600 tw:hover:bg-emerald-700 tw:border-emerald-600">
                <template x-if="loading"><i class="fa fa-spinner fa-spin tw:mr-1"></i></template>
                <template x-if="!loading"><i class="fa fa-plus tw:mr-1"></i></template>
                {{ __('Add Polling Type') }}
            </button>
        </div>

        {{-- Reachability Failure Dialog --}}
        <x-device.polling.validation-failed-modal />
    </form>
@endif

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('addPollingTypeForm', (config) => ({
                methodType: config.methodType,
                secretMode: config.secretMode,
                loading: false,
                errors: {},
                unreachableDialog: false,
                unreachableMessage: '',
                unreachableDetails: '',
                saveAnyway() {
                    this.unreachableDialog = false;
                    this.submitForm(true);
                },
                async submitForm(force = false) {
                    if (this.loading) { return; }
                    this.loading = true;
                    this.errors = {};
                    const formData = new FormData(this.$refs.addForm);
                    if (force) {
                        formData.set('force_save', '1');
                    }
                    try {
                        const { data } = await axios.post(this.$refs.addForm.action, formData);
                        toastr.success(data.message || config.labels.added);
                        window.location.href = config.redirectUrl + '?tab=' + encodeURIComponent(this.methodType);
                    } catch (error) {
                        const data = error.response?.data;
                        if (data?.status === 'unreachable') {
                            this.unreachableMessage = data.message || config.labels.unreachable;
                            this.unreachableDetails = data.error_details || '';
                            this.unreachableDialog = true;
                        } else if (data?.errors) {
                            this.errors = data.errors;
                        } else {
                            toastr.error(data?.message || (error.response ? config.labels.failed : config.labels.error));
                        }
                    } finally {
                        this.loading = false;
                    }
                },
            }));
        });
    </script>
@endpush
