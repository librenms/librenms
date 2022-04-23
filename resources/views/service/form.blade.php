<form method="post" role="form" id="service" class="form-horizontal service-form" x-data="serviceFormData()">
    <?php echo csrf_field() ?>
    <input type="hidden" name="service_id" id="service_id" value="">
    <input type="hidden" name="service_template_id" id="service_template_id" value="">

    <div class="form-group">
        <div class="col-sm-12">
            <span id="ajax_response">&nbsp;</span>
        </div>
    </div>
    <div class="form-group row">
        <label for='service_name' class='col-sm-3 control-label'>Name </label>
        <div class="col-sm-9">
            <input type='text' id='service_name' name='service_name' class='form-control input-sm' placeholder=''/>
        </div>
        <div class='col-sm-9'>
        </div>
    </div>
    @isset($device_id)
        <input type="hidden" name="device_id" id="device_id" value="{{ $device_id }}" x-model.number="device_id">
    @else
        <div class="form-group row">
            <label for='device_id' class='col-sm-3 control-label'>Device </label>
            <div class="col-sm-9">
                <select id='device_id' name='device_id' class='form-control has-feedback' x-model.number="device_id">
                </select>
            </div>
            <div class='col-sm-9'>
            </div>
        </div>
    @endif
    <div class="form-group row">
        <label for='service_type' class='col-sm-3 control-label'>Check Type </label>
        <div class="col-sm-9">
            <select id='service_type' name='service_type' class='form-control has-feedback' x-model="service_type">
                @foreach(\LibreNMS\Services::list() as $check)
                    <option value="{{ $check }}">{{ $check }}</option>
                @endforeach
            </select>
        </div>
        <div class='col-sm-9'>
        </div>
    </div>
    <div class='form-group row'>
        <label for='service_desc' class='col-sm-3 control-label'>Description </label>
        <div class='col-sm-9'>
            <textarea id='service_desc' name='service_desc' class='form-control' rows='5'></textarea>
        </div>
        <div class='col-sm-9'>
        </div>
    </div>
    <div class="form-group row" x-show="hasHostname">
        <label for='service_ip' class='col-sm-3 control-label'>Remote Host </label>
        <div class="col-sm-9">
            <input type='text' id='service_ip' name='service_ip' class='form-control has-feedback' placeholder='<This Device>' x-bind:required="hostnameRequired"/>
        </div>
        <div class='col-sm-9'>
        </div>
    </div>
    <div class="form-group row">
        <label for="service_param" class="col-sm-3 control-label">Parameters </label>
        <div class="col-sm-9">
            <div class="tw-flex">
                <select id="parameters" class="form-control has-feedback tw-flex-initial" x-model="currentParam" x-ref="param" x-bind:disabled="! currentParam">
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
    </div>
    <div class="form-group row">
        <label for='service_ignore' class='col-sm-3 control-label'>Ignore alert tag </label>
        <div class="col-sm-9">
            <input type="hidden" name="service_ignore" id='service_ignore' value="0">
            <input type='checkbox' id='ignore_box' name='ignore_box' onclick="$('#service_ignore').attr('value', $('#ignore_box').prop('checked')?1:0);">
        </div>
    </div>
    <div class="form-group row">
        <label for='service_disabled' class='col-sm-3 control-label'>Disable polling and alerting </label>
        <div class="col-sm-9">
            <input type='hidden' id='service_disabled' name='service_disabled' value="0">
            <input type='checkbox' id='disabled_box' name='disabled_box' onclick="$('#service_disabled').attr('value', $('#disabled_box').prop('checked')?1:0);">
        </div>
    </div>
    <hr>
    <div class="form-group row">
        <div class="col-sm-offset-3">
            <button class="btn btn-default btn-sm" type="submit" name="service-submit" id="service-submit" value="save">Save Service</button>
        </div>
    </div>
    <div class="clearfix"></div>
</form>

<script>
    function serviceFormData() {
        return {
            device_id: null,
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




    $('#service_type').val($('#service_type option:eq(0)').val()).trigger('change');
    // on-hide
    $('#create-service').on('hide.bs.modal', function (event) {
        $('#service_type').val('');
        $("#service_type").prop("disabled", false);
        $('#service_ip').val('');
        $('#service_desc').val('');
        $('#service_param').val('');
        $('#service_ignore').val('');
        $('#ignore_box').val('');
        $('#service_disabled').val('');
        $('#disabled_box').val('');
        $('#service_template_id').val('');
        $('#service_name').val('');
        $('#service_template_name').val('');
        $("#ajax_response").html('');
    });

    // on-load
    $('#create-service').on('show.bs.modal', function (e) {
        var button = $(e.relatedTarget);
        var service_id = button.data('service_id');
        var modal = $(this)
        $('#service_id').val(service_id);
        $.ajax({
            type: "GET",
            url: "<?php echo route('services.show', ['service' => '?']) ?>".replace('?', service_id),
            dataType: "json",
            success: function (service) {
                $('#service_type').val(service.service_type);
                $("#service_type").prop("disabled", true);
                $('#service_ip').val(service.service_ip);
                $('#device_id').val(service.device_id);
                $('#service_desc').val(service.service_desc);
                $('#service_param').val(service.service_param);
                $('#service_ignore').val(service.service_ignore === true ? 1 : 0);
                $('#service_disabled').val(service.service_disabled === true ? 1 : 0);
                $('#ignore_box').prop("checked", service.service_ignore);
                $('#disabled_box').prop("checked", service.service_disabled);
                $('#service_template_id').val(service.service_template_id === 0 ? '' : service.service_template_id);
                $('#service_name').val(service.service_name);
            }
        });

    });

    // on-submit
    $('#service-submit').on("click", function (e) {
        e.preventDefault();
        var service_id = $('#service_id').val();
        $.ajax({
            type: service_id ? 'PUT' : 'POST',
            url: "<?php echo route('services.store') ?>" + (service_id ? '/' + service_id : ''),
            data: $('form.service-form').serializeArray(),
            success: function (result) {
                $('#message').html('<div class="alert alert-info">' + result.message + '</div>');
                $("#create-service").modal('hide');
                setTimeout(function () {
                    location.reload();
                }, 1500);
            },
            error: function (result) {
                var message = result.responseJSON.message;
                for (const field in result.responseJSON.errors) {
                    message += '<br />' + field + ': ' + result.responseJSON.errors[field];
                }

                $("#ajax_response").html('<div class="alert alert-danger">' + message + '</div>');
            }
        });
    });
</script>
