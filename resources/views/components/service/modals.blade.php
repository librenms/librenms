@props(['device' => null])

{{-- Add, edit and delete service dialogs, opened by the service-create, service-edit and service-delete window events --}}
<div x-data="serviceModals(@js($device?->device_id))"
     x-on:service-create.window="create()"
     x-on:service-edit.window="edit($event.detail.id)"
     x-on:service-delete.window="confirmDelete($event.detail.id)"
     x-on:service-toggle.window="toggle($event.detail.id, $event.detail.enabled)"
     x-on:hashchange.window="if (window.location.hash === '#create-service') create()">

    <x-modal show="formOpen" maxWidth="xl">
        <x-slot:heading>
            <h4 class="tw:m-0 tw:text-base tw:font-semibold" x-text="form.service_id ? @js(__('Edit Service')) : @js(__('Add Service'))"></h4>
        </x-slot:heading>

        <form class="form-horizontal" x-on:submit.prevent="save()" id="service-form">
            @unless($device)
                <div class="form-group" x-show="! form.service_id">
                    <label for="service-device" class="col-sm-3 control-label">{{ __('Device') }}</label>
                    <div class="col-sm-9">
                        <select id="service-device" x-ref="device" class="form-control" style="width: 100%"></select>
                    </div>
                </div>
            @endunless
            <div class="form-group">
                <label for="service-name" class="col-sm-3 control-label">{{ __('Name') }}</label>
                <div class="col-sm-9">
                    <input type="text" id="service-name" class="form-control input-sm" x-model="form.service_name">
                </div>
            </div>
            <div class="form-group">
                <label for="service-type" class="col-sm-3 control-label">{{ __('Check Type') }}</label>
                <div class="col-sm-9">
                    <p class="form-control-static" x-show="form.service_id" x-text="form.service_type"></p>
                    <select id="service-type" class="form-control" x-model="form.service_type" x-show="! form.service_id">
                        @foreach(\LibreNMS\Services::list() as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="service-desc" class="col-sm-3 control-label">{{ __('Description') }}</label>
                <div class="col-sm-9">
                    <textarea id="service-desc" class="form-control" rows="4" x-model="form.service_desc"></textarea>
                </div>
            </div>
            <div class="form-group">
                <label for="service-ip" class="col-sm-3 control-label">{{ __('Remote Host') }}</label>
                <div class="col-sm-9">
                    <input type="text" id="service-ip" class="form-control" placeholder="{{ __('IP Address or Hostname') }}" x-model="form.service_ip">
                </div>
            </div>
            <div class="form-group">
                <label for="service-param" class="col-sm-3 control-label">{{ __('Parameters') }}</label>
                <div class="col-sm-9">
                    <input type="text" id="service-param" class="form-control" x-model="form.service_param">
                    <span class="help-block">{{ __('Parameters may be required and will be different depending on the service check.') }}</span>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-3 col-sm-9">
                    <div class="checkbox">
                        <label><input type="checkbox" x-model="form.service_ignore"> {{ __('Ignore alert tag') }}</label>
                    </div>
                    <div class="checkbox">
                        <label><input type="checkbox" x-model="form.service_disabled"> {{ __('Disable polling and alerting') }}</label>
                    </div>
                </div>
            </div>
        </form>

        <x-slot:footer>
            <button type="button" class="btn btn-default" x-on:click="formOpen = false">{{ __('Cancel') }}</button>
            <button type="submit" form="service-form" class="btn btn-primary" x-bind:disabled="saving">{{ __('Save Service') }}</button>
        </x-slot:footer>
    </x-modal>

    <x-modal show="deleteOpen" :title="__('Confirm Delete')" maxWidth="md">
        <p>{{ __('Please confirm that you would like to delete this service.') }}</p>
        <x-slot:footer>
            <button type="button" class="btn btn-default" x-on:click="deleteOpen = false">{{ __('Cancel') }}</button>
            <button type="button" class="btn btn-danger" x-on:click="destroy()" x-bind:disabled="saving">{{ __('Delete') }}</button>
        </x-slot:footer>
    </x-modal>
</div>

@once
@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('serviceModals', (deviceId) => ({
            formOpen: false,
            deleteOpen: false,
            saving: false,
            deleteId: null,
            form: {},

            init() {
                if (this.$refs.device) {
                    init_select2(this.$refs.device, 'device', {limit: 100}, null, @js(__('Select a device')), {dropdownParent: $(this.$el)});
                }

                if (window.location.hash === '#create-service') {
                    this.create();
                }
            },

            serviceUrl(id) {
                return route('services.show', {service: id});
            },

            create() {
                this.form = {
                    service_id: null,
                    service_name: '',
                    service_type: $('#service-type option:first').val(),
                    service_desc: '',
                    service_ip: '',
                    service_param: '',
                    service_ignore: false,
                    service_disabled: false,
                };
                this.formOpen = true;
            },

            edit(id) {
                this.request('GET', this.serviceUrl(id)).then((service) => {
                    this.form = {
                        ...service,
                        service_ignore: Boolean(service.service_ignore),
                        service_disabled: Boolean(service.service_disabled),
                    };
                    this.formOpen = true;
                }).catch(() => {});
            },

            save() {
                const request = this.form.service_id
                    ? this.request('PATCH', this.serviceUrl(this.form.service_id), this.form)
                    : this.request('POST', route('services.store'), {...this.form, device_id: deviceId ?? $(this.$refs.device).val()});

                request.then((response) => this.done(response)).catch(() => {});
            },

            toggle(id, enabled) {
                this.request('PATCH', this.serviceUrl(id), {service_disabled: ! enabled})
                    .then((response) => toastr.success(response.message))
                    .catch(() => window.location.reload());
            },

            confirmDelete(id) {
                this.deleteId = id;
                this.deleteOpen = true;
            },

            destroy() {
                this.request('DELETE', this.serviceUrl(this.deleteId)).then((response) => this.done(response)).catch(() => {});
            },

            done(response) {
                this.formOpen = false;
                this.deleteOpen = false;
                toastr.success(response.message);
                window.location.reload();
            },

            request(method, url, data) {
                this.saving = true;

                return axios({method, url, data})
                    .then((response) => response.data)
                    .catch((error) => {
                        toastr.error(error.response?.data?.message ?? @js(__('Request failed')));
                        throw error;
                    })
                    .finally(() => this.saving = false);
            },
        }));
    });
</script>
@endpush
@endonce
