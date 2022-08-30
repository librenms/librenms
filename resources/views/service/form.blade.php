<form method="post" role="form" id="service" class="form-horizontal" x-data="serviceFormData()" x-modelable="service_id">
    <div class="form-group row">
        <label for='service_name' class="col-sm-4 col-md-3 control-label">{{ __('service.fields.service_name') }}</label>
        <div class="col-sm-8 col-md-9">
            <input type='text' id='service_name' name='service_name' class='form-control input-sm' x-model="service_name" x-bind:class="{'!tw-border-red-500': errors.service_name}"/>
        </div>
        <div class='col-sm-8 col-sm-offset-4 col-md-9 col-md-offset-3 tw-text-red-500'>
            <template x-for="error in errors.service_name">
                <div x-text="error"></div>
            </template>
        </div>
    </div>
    @empty($device_id)
        <div class="form-group row">
            <label for='device_id' class="col-sm-4 col-md-3 control-label">{{ __('service.fields.device_id') }}</label>
            <div class="col-sm-8 col-md-9">
                <select id="device_id" name="device_id" class="form-control" x-model.number="device_id" x-bind:class="{'!tw-border-red-500': errors.device_id}">
                </select>
            </div>
            <div class="col-sm-8 col-sm-offset-4 col-md-9 col-md-offset-3 tw-text-red-500">
                <template x-for="error in errors.device_id">
                    <div x-text="error"></div>
                </template>
            </div>
        </div>
    @endif
    <div class="form-group row">
        <label for="service_type" class="col-sm-4 col-md-3 control-label">{{ __('service.fields.service_type') }}</label>
        <div class="col-sm-8 col-md-9">
            <select id="service_type" name="service_type" class="form-control has-feedback" x-model="service_type" x-bind:class="{'!tw-border-red-500': errors.service_type}">
                @foreach(\LibreNMS\Services::list() as $check)
                    <option value="{{ $check }}">{{ $check }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-8 col-sm-offset-4 col-md-9 col-md-offset-3 tw-text-red-500">
            <template x-for="error in errors.service_type">
                <div x-text="error"></div>
            </template>
        </div>
    </div>
    <div class='form-group row'>
        <label for='service_desc' class="col-sm-4 col-md-3 control-label">{{ __('service.fields.service_desc') }}</label>
        <div class='col-sm-8'>
            <textarea id='service_desc' name='service_desc' class='form-control' rows='5' x-model="service_desc" x-bind:class="{'!tw-border-red-500': errors.service_desc}"></textarea>
        </div>
        <div class='col-sm-8 col-sm-offset-4 col-md-9 col-md-offset-3 tw-text-red-500'>
            <template x-for="error in errors.service_desc">
                <div x-text="error"></div>
            </template>
        </div>
    </div>
    <div class="form-group row" x-show="hasHostname">
        <label for='service_ip' class="col-sm-4 col-md-3 control-label">{{ __('service.fields.service_ip') }}</label>
        <div class="col-sm-8 col-md-9">
            <input type='search' id='service_ip' name='service_ip' class='form-control has-feedback' placeholder='{{ __('service.this_device') }}' x-model="service_ip" x-bind:class="{'!tw-border-red-500': errors.service_ip}"/>
        </div>
        <div class='col-sm-8 col-sm-offset-4 col-md-9 col-md-offset-3 tw-text-red-500'>
            <template x-for="error in errors.service_ip">
                <div x-text="error"></div>
            </template>
        </div>
    </div>
    <div class="form-group row">
        <label for="service_param" class="col-sm-4 col-md-3 control-label">{{ __('service.fields.service_param') }}</label>
        <div class="col-sm-8 col-md-9">
            <div class="tw-flex">
                <select id="parameters" class="form-control has-feedback tw-flex-initial" x-model="currentParam" x-ref="param" x-bind:disabled="! currentParam" x-bind:class="{'!tw-border-red-500': Object.keys(errors).findIndex(e => e.includes('service_param')) >= 0}">
                    <template x-for="param in unusedParams()">
                        <option x-bind:value="param.param || param.short" x-text="(param.param || param.short) + (paramIsRequired(param) ? ' *' : '') + (param.exclusive_group ? ' []' : '')"></option>
                    </template>
                </select>
                <input type='text' id='service_param' name='service_param' class='form-control has-feedback  tw-ml-2 tw-flex-grow' x-model="currentValue" x-ref="value"
                       x-bind:placeholder="currentParam && getParameter(currentParam).value"
                       x-bind:disabled="currentParam && ! getParameter(currentParam).value"
                       x-bind:required="currentParam && getParameter(currentParam).value">
                <button x-on:click.prevent="addTag(currentParam, currentValue)"
                        x-bind:disabled="! currentParam"
                        x-bind:class="currentParam ? 'hover:tw-bg-blue-700' : 'tw-opacity-50 !tw-cursor-not-allowed'"
                        class="tw-bg-blue-500 tw-text-white tw-font-bold tw-py-2 tw-px-4 tw-rounded tw-ml-2 tw-flex-none"><i class="fa-solid fa-plus"></i></button>
            </div>
            <div class="tw-p-3 tw-text-gray-500" x-show="currentParam">
                <i class="fa fa-solid fa-question-circle"></i>
                <span x-text="getParameter(currentParam).description"></span>
            </div>
            <template x-for="(value, param) in service_param">
                <div class="tw-bg-indigo-100 tw-inline-flex tw-items-center tw-rounded tw-mt-2 tw-mr-1">
                    <span class="tw-ml-2 tw-mr-1 tw-leading-relaxed tw-truncate tw-max-w-xs" x-text="value ? `${param} ${value}` : param"></span>
                    <button x-on:click.prevent="removeTag(param)" class="tw-w-6 tw-h-8 tw-inline-block tw-align-middle tw-text-gray-500 hover:tw-text-gray-600 focus:tw-outline-none">
                        <svg class="tw-w-6 tw-h-6 tw-fill-current tw-mx-auto" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M15.78 14.36a1 1 0 0 1-1.42 1.42l-2.82-2.83-2.83 2.83a1 1 0 1 1-1.42-1.42l2.83-2.82L7.3 8.7a1 1 0 0 1 1.42-1.42l2.83 2.83 2.82-2.83a1 1 0 0 1 1.42 1.42l-2.83 2.83 2.83 2.82z"/></svg>
                    </button>
                </div>
            </template>
        </div>
        <div class='col-sm-8 col-sm-offset-4 col-md-9 col-md-offset-3 tw-text-red-500'>
            <template x-for="error in Object.entries(errors).reduce((carry, [key, message]) => key.includes('service_param') ? carry.concat(String(message).replace('service param.', '')) : carry, [])">
                <div x-text="error"></div>
            </template>
        </div>
    </div>
    <div class="form-group row">
        <label for='service_ignore' class="col-sm-4 col-md-3 control-label">{{ __('service.fields.service_ignore') }}</label>
        <div class="col-sm-8 col-md-9">
            <x-toggle x-model="service_ignore"></x-toggle>
        </div>
        <div class='col-sm-8 col-sm-offset-4 col-md-9 col-md-offset-3 tw-text-red-500'>
            <template x-for="error in errors.service_ignore">
                <div x-text="error"></div>
            </template>
        </div>
    </div>
    <div class="form-group row">
        <label for='service_disabled' class='col-sm-4 col-md-3 control-label'>{{ __('service.fields.service_disabled') }}</label>
        <div class="col-sm-8 col-md-9">
            <x-toggle x-model="service_disabled"></x-toggle>
        </div>
        <div class='col-sm-8 col-sm-offset-4 col-md-9 col-md-offset-3 tw-text-red-500'>
            <template x-for="error in errors.service_disabled">
                <div x-text="error"></div>
            </template>
        </div>
    </div>
    <div class="form-group row">
        <div class="col-sm-offset-4 col-md-offset-3 col-sm-8 tw-text-right">
            <button class="btn btn-primary btn-sm" type="button" value="save" x-on:click="save">{{ __('service.save') }}</button>
            <button class="btn btn-default btn-sm" type="button" value="test" x-on:click="test">{{ __('service.test') }}</button>
            <button class="btn btn-danger btn-sm" type="button" value="test" x-on:click="cancel">{{ __('service.cancel') }}</button>
        </div>
    </div>
    <x-panel x-show="testMessage"
             style="display: none;"
             title="Test Result"
             x-bind:class="{'panel-success': testResult === 0, 'panel-warning': testResult === 1, 'panel-danger': testResult === 2}">
        <pre x-text="testMessage"></pre>
    </x-panel>
</form>

<script>
    function serviceFormData() {
        return {
            service_id: null,
            device_id: {{ $device_id ?? 'null' }},
            service_name: '',
            service_type: 'icmp',
            service_desc: '',
            service_ip: null,
            service_param: {},
            service_ignore: false,
            service_disabled: false,
            currentParam: null,
            currentValue: null,
            hasHostname: true,
            wipeParams: false,
            testMessage: '',
            testResult: 1,
            parameters: [],
            excluded: {},
            errors: {},
            async save() {
                const url = this.service_id ? '{{ route('services.update', ['service' => '?']) }}'.replace('?', this.service_id) : '{{ route('services.store') }}';
                if (await this.submitCheck(url, this.service_id ? 'PUT' : 'POST') !== false) {
                    toastr.success('{{ __('service.added') }}');
                    this.$dispatch('service-saved');

                    // reset form except device and check type
                    this.service_name = '';
                    this.service_desc = '';
                    this.service_ip = '';
                    this.service_param = {};
                    this.service_ignore = false;
                    this.service_disabled = false;
                    this.testMessage = '';
                    this.testResult = 1;
                }
            },
            async submitCheck(url, method) {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        "X-CSRF-Token": document.head.querySelector("[name~=csrf-token][content]").content
                    },
                    body: JSON.stringify({
                        service_name: this.service_name,
                        device_id: this.device_id,
                        service_type: this.service_type,
                        service_desc: this.service_desc,
                        service_ip: this.service_ip,
                        service_param: this.service_param,
                        service_ignore: this.service_ignore,
                        service_disabled: this.service_disabled,
                    })
                });
                let result = await response.json();

                if (result.errors) {
                    this.errors = result.errors;
                    return false;
                } else {
                    this.errors = {};
                    return result;
                }
            },
            async test() {
                const result = await this.submitCheck('{{ route('services.test') }}', 'POST')
                if (result !== false) {
                    this.testMessage = result.message;
                    this.testResult = result.result;
                }
            },
            cancel(event) {
                this.$dispatch('service-form-cancel');
                this.service_id = null;
                this.device_id = null;
                this.service_type = 'icmp';
                this.service_name = '';
                this.service_desc = '';
                this.currentValue = '';
                this.service_ip = '';
                this.service_param = {};
                this.service_ignore = false;
                this.service_disabled = false;
                this.testMessage = '';
                this.testResult = 1;
                event.target.blur();
            },
            getParameter(key) {
                const found = this.parameters.find(param => param.param === key || param.short === key);
                return found ? found : {};
            },
            paramIsRequired(param)
            {
                if (param.required === true) {
                    return true;
                }

                if (Array.isArray(param.inclusive_group)) {
                    const group = this.parameters.filter(p => param.inclusive_group.includes(p.short ? p.short : p.param));
                    // console.log(group);

                    const l = group.length;
                    for (let i = 0; i < l; i++) {
                        if (this.service_param.hasOwnProperty(group[i].param)) {
                            return true;
                        }
                    }
                }

                return false;
            },
            unusedParams() {
                const used = Object.keys(this.service_param).concat(Object.values(this.excluded).flat());
                return this.parameters.filter(param => ! (used.includes(param.param) || used.includes(param.short)));
            },
            removeTag(param) {
                this.currentValue = this.service_param[param];
                delete this.service_param[param];
                delete this.excluded[param];
            },
            addTag(param, value) {
                let parameter = this.getParameter(param);
                if (param !== "" && ! this.service_param.hasOwnProperty(param)) {
                    if (parameter.value && ! this.currentValue) {
                        this.$refs.value.classList.add('!tw-border-red-500');
                        setTimeout(() => this.$refs.value.classList.remove('!tw-border-red-500'), 1500);
                        return;
                    }

                    if (parameter.exclusive_group) {
                        this.excluded[param] = parameter.exclusive_group;
                    }
                    this.service_param[param] = value;
                    this.currentValue = null;
                }
            },
            async fetchParams(type, wipe_params = true) {
                const response = await fetch('{{ route('services.params', ['type' => '?']) }}'.replace('?', type));
                let parameters = await response.json();
                let hasHostname = false;

                parameters = parameters.filter(param => {
                    if (param.uses_target) {
                        hasHostname = true;
                        return false;
                    }

                    return true;
                });

                if (wipe_params) {
                    this.service_param = {};
                }
                this.errors = {};
                this.hasHostname = hasHostname;
                this.parameters = parameters;
                this.$nextTick(() => this.currentParam = this.$refs.param.value);
            },
            init: function () {
                if (! this.device_id) {
                    let deviceSelect = init_select2('#device_id', 'device', {}, null, null, {allowClear: false});

                    deviceSelect.on("select2:select", (event) => {
                        this.device_id = event.target.value;
                    });

                    this.$watch("device_id", (device) => {
                        deviceSelect.val(device).trigger("change");
                    });
                }

                if (this.service_type) {
                    this.fetchParams(this.service_type, ! this.service_id);
                }
                this.$watch('service_type', service_type => this.fetchParams(service_type, this.wipeParams));
                this.$watch('service_param', () => this.currentParam = this.$refs.param.value);
                if (typeof this.show !== 'undefined') {
                    this.$watch('show' , show => this.service_id = show);
                }
                this.$watch('service_id', service_id => {
                    if (service_id) {
                        fetch('{{ route('services.show', ['service' => '?']) }}'.replace('?', service_id))
                            .then(response => response.json())
                            .then(result => {
                                this.wipeParams = false; // prevent param wipe when loading
                                this.service_type = result.service_type;
                                this.service_name = result.service_name;
                                this.device_id = result.device_id;
                                this.service_desc = result.service_desc;
                                this.service_ip = result.service_ip;
                                this.service_param = result.service_param;
                                this.service_ignore = result.service_ignore;
                                this.service_disabled = result.service_disabled;
                                this.$nextTick(() => this.wipeParams = true);
                            });
                    }
                });
            }
        };
    }
</script>
