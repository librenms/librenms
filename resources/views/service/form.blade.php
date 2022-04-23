<form method="post" role="form" id="service" class="form-horizontal" x-data="serviceFormData()">
    <div class="form-group row">
        <label for='service_name' class='col-sm-3 control-label'>Name </label>
        <div class="col-sm-9">
            <input type='text' id='service_name' name='service_name' class='form-control input-sm' placeholder='' x-model="service_name" x-bind:class="{'!tw-border-red-500': errors.service_name}"/>
        </div>
        <div class='col-sm-9 col-sm-offset-3 tw-text-red-500'>
            <template x-for="error in errors.service_name">
                <div x-text="error"></div>
            </template>
        </div>
    </div>
    @isset($device_id)
        <input type="hidden" name="device_id" id="device_id" value="{{ $device_id }}" x-model.number="device_id">
    @else
        <div class="form-group row">
            <label for='device_id' class='col-sm-3 control-label'>Device </label>
            <div class="col-sm-9">
                <select id='device_id' name='device_id' class='form-control' x-model.number="device_id" x-bind:class="{'!tw-border-red-500': errors.device_id}">
                </select>
            </div>
            <div class='col-sm-9 col-sm-offset-3 tw-text-red-500'>
                <template x-for="error in errors.device_id">
                    <div x-text="error"></div>
                </template>
            </div>
        </div>
    @endif
    <div class="form-group row">
        <label for='service_type' class='col-sm-3 control-label'>Check Type </label>
        <div class="col-sm-9">
            <select id='service_type' name='service_type' class='form-control has-feedback' x-model="service_type" x-bind:class="{'!tw-border-red-500': errors.service_type}">
                @foreach(\LibreNMS\Services::list() as $check)
                    <option value="{{ $check }}">{{ $check }}</option>
                @endforeach
            </select>
        </div>
        <div class='col-sm-9 col-sm-offset-3 tw-text-red-500'>
            <template x-for="error in errors.service_type">
                <div x-text="error"></div>
            </template>
        </div>
    </div>
    <div class='form-group row'>
        <label for='service_desc' class='col-sm-3 control-label'>Description </label>
        <div class='col-sm-9'>
            <textarea id='service_desc' name='service_desc' class='form-control' rows='5' x-model="service_desc" x-bind:class="{'!tw-border-red-500': errors.service_desc}"></textarea>
        </div>
        <div class='col-sm-9 col-sm-offset-3 tw-text-red-500'>
            <template x-for="error in errors.service_desc">
                <div x-text="error"></div>
            </template>
        </div>
    </div>
    <div class="form-group row" x-show="hasHostname">
        <label for='service_ip' class='col-sm-3 control-label'>Remote Host </label>
        <div class="col-sm-9">
            <input type='text' id='service_ip' name='service_ip' class='form-control has-feedback' placeholder='<This Device>' x-model="service_ip" x-bind:class="{'!tw-border-red-500': errors.device_id}"/>
        </div>
        <div class='col-sm-9 col-sm-offset-3 tw-text-red-500'>
            <template x-for="error in errors.service_ip">
                <div x-text="error"></div>
            </template>
        </div>
    </div>
    <div class="form-group row">
        <label for="service_param" class="col-sm-3 control-label">Parameters </label>
        <div class="col-sm-9">
            <div class="tw-flex">
                <select id="parameters" class="form-control has-feedback tw-flex-initial" x-model="currentParam" x-ref="param" x-bind:disabled="! currentParam" x-bind:class="{'!tw-border-red-500': errors.service_param}">
                    <template x-for="param in unusedParams()">
                        <option x-bind:value="param.param || param.short" x-text="(param.param || param.short) + (param.required ? ' *' : '') + (param.group ? ' []' : '')"></option>
                    </template>
                </select>
                <input type='text' id='service_param' name='service_param' class='form-control has-feedback  tw-ml-2 tw-flex-grow' x-model="currentValue" x-ref="value"
                       x-bind:placeholder="currentParam && getParameter(currentParam).value"
                       x-bind:title="currentParam && getParameter(currentParam).description"
                       x-bind:disabled="currentParam && ! getParameter(currentParam).value"
                       x-bind:required="currentParam && getParameter(currentParam).value">
                <button x-on:click.prevent="addTag(currentParam, currentValue)"
                        x-bind:disabled="! currentParam"
                        x-bind:class="currentParam ? 'hover:tw-bg-blue-700' : 'tw-opacity-50 !tw-cursor-not-allowed'"
                        class="tw-bg-blue-500 tw-text-white tw-font-bold tw-py-2 tw-px-4 tw-rounded tw-ml-2 tw-flex-none"><i class="fa-solid fa-plus"></i></button>
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
        <div class='col-sm-9 col-sm-offset-3 tw-text-red-500'>
            <template x-for="error in errors.service_param">
                <div x-text="error"></div>
            </template>
        </div>
    </div>
    <div class="form-group row">
        <label for='service_ignore' class='col-sm-3 control-label'>Ignore alert tag </label>
        <div class="col-sm-9">
            <input type='checkbox' id='service_ignore' name='service_ignore' x-model="service_ignore">
        </div>
        <div class='col-sm-9 col-sm-offset-3 tw-text-red-500'>
            <template x-for="error in errors.service_ignore">
                <div x-text="error"></div>
            </template>
        </div>
    </div>
    <div class="form-group row">
        <label for='service_disabled' class='col-sm-3 control-label'>Disable polling and alerting </label>
        <div class="col-sm-9">
            <input type='checkbox' id='service_disabled' name='service_disabled' x-model="service_disabled">
        </div>
        <div class='col-sm-9 col-sm-offset-3 tw-text-red-500'>
            <template x-for="error in errors.service_disabled">
                <div x-text="error"></div>
            </template>
        </div>
    </div>
    <div class="form-group row">
        <div class="col-sm-offset-3">
            <button class="btn btn-default btn-sm" type="button" value="save" x-on:click="save">Save Service</button>
        </div>
    </div>
    <div class="clearfix"></div>
</form>

<script>
    function serviceFormData() {
        return {
            service_id: null,
            device_id: null,
            service_name: '',
            service_type: 'ping',
            service_desc: '',
            service_ip: null,
            service_param: {},
            service_ignore: false,
            service_disabled: false,
            currentParam: null,
            currentValue: null,
            hasHostname: true,
            parameters: [],
            excluded: {},
            errors: {},
            async save() {
                const response = await fetch('{{ route('services.store') }}', {
                    method: this.service_id ? 'PUT' : 'POST',
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
                console.log(result);
                this.errors = result.errors
            },
            getParameter(key) {
                const found = this.parameters.find(param => param.param === key || param.short === key);
                return found ? found : {};
            },
            unusedParams() {
                const used = Object.keys(this.service_param).concat(Object.values(this.excluded).flat());
                return this.parameters.filter(param => ! (used.includes(param.param) || used.includes(param.short)));
            },
            removeTag(param) {
                delete this.service_param[param];
                delete this.excluded[param];
                // this.selectFirstParam();
            },
            addTag(param, value) {
                let parameter = this.getParameter(param);
                if (param !== "" && ! this.service_param.hasOwnProperty(param)) {
                    if (parameter.value && ! this.currentValue) {
                        this.$refs.value.classList.add('!tw-border-red-500');
                        setTimeout(() => this.$refs.value.classList.remove('!tw-border-red-500'), 1500);
                        return;
                    }

                    if (parameter.group) {
                        this.excluded[param] = parameter.group;
                    }
                    this.service_param[param] = value;
                    this.currentValue = null;
                    // this.selectFirstParam();
                }
            },
            async fetchParams(type) {
                const response = await fetch('{{ route('services.params', ['type' => '?']) }}'.replace('?', type));
                let parameters = await response.json();
                let hasHostname = false;

                parameters = parameters.filter(param => {
                    if (param.param === '--hostname' || param.short === '-H') {
                        hasHostname = true;
                        return false;
                    }

                    return true;
                });

                this.hasHostname = hasHostname;
                this.parameters = parameters;
                this.$nextTick(() => this.currentParam = this.$refs.param.value);
            },
            selectFirstParam() {
                let first = this.unusedParams().first();
                this.currentParam = first.param || first.short;
            },
            init: function () {
                let deviceSelect = init_select2('#device_id', 'device', {}, null, null, {allowClear: false});

                deviceSelect.on("select2:select", (event) => {
                    this.device_id = event.target.value;
                });
                this.$watch("device_id", (device) => {
                    deviceSelect.val(device).trigger("change");
                });

                if (this.service_type) {
                    this.fetchParams(this.service_type);
                }
                this.$watch("service_type", service_type => this.fetchParams(service_type));
                this.$watch('service_param', () => this.currentParam = this.$refs.param.value);
            }
        };
    }
</script>
