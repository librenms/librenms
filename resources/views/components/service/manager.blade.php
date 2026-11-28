@props(['device' => null])

@php
    $canCreate = Gate::allows('create', \App\Models\Service::class);
    $types = $canCreate ? \LibreNMS\Services::list() : [];
@endphp

<div x-data="serviceManager(@js($device?->device_id), @js($canCreate), @js($types[0] ?? ''))"
     x-on:hashchange.window="openFromHash()">
    {{ $slot }}

    <x-service.modals :device="$device" :types="$types" />
</div>

@pushOnce('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('serviceManager', (deviceId, canCreate, defaultType) => ({
            formOpen: false,
            deleteOpen: false,
            busy: false,
            editUrl: null,
            deleteUrl: null,
            form: {},
            errors: [],

            init() {
                this.$nextTick(() => {
                    if (this.$refs.device) {
                        init_select2(this.$refs.device, 'device', {}, null, @js(__('Select a device')), {
                            width: '100%',
                            dropdownParent: $(this.$refs.form),
                        });
                    }
                    this.openFromHash();
                });
            },

            openFromHash() {
                if (canCreate && window.location.hash === '#create-service' && !this.busy) {
                    history.replaceState(history.state, '', window.location.pathname + window.location.search);
                    this.createService();
                }
            },

            createService() {
                this.editUrl = null;
                this.errors = [];
                this.form = {
                    service_name: '',
                    service_type: defaultType,
                    service_desc: '',
                    service_ip: '',
                    service_param: '',
                    service_ignore: false,
                    service_disabled: false,
                };
                if (this.$refs.device) {
                    $(this.$refs.device).val(null).trigger('change.select2');
                }
                this.formOpen = true;
            },

            async editService(url, updateUrl) {
                const service = await this.request('GET', url);
                if (service) {
                    this.editUrl = updateUrl;
                    this.errors = [];
                    this.form = service;
                    this.formOpen = true;
                }
            },

            saveService() {
                if (this.editUrl) {
                    this.request('PATCH', this.editUrl, this.form);
                } else {
                    this.request('POST', @js(route('services.store')), {
                        ...this.form,
                        device_id: deviceId ?? $(this.$refs.device).val(),
                    });
                }
            },

            confirmDeleteService(url) {
                this.deleteUrl = url;
                this.deleteOpen = true;
            },

            async toggleService(checkbox, url) {
                const enabled = checkbox.checked;
                if (!await this.request('PATCH', url, {service_disabled: !enabled})) {
                    checkbox.checked = !enabled;
                }
            },

            async request(method, url, data) {
                if (this.busy) {
                    return;
                }

                this.busy = true;
                this.errors = [];

                try {
                    const response = await axios({method, url, data});
                    if (method === 'GET') {
                        this.busy = false;
                    } else {
                        window.location.reload();
                    }
                    return response.data;
                } catch (error) {
                    const response = error.response?.data;
                    if (this.formOpen && response?.errors) {
                        this.errors = Object.values(response.errors).flat();
                    } else {
                        const message = response?.message ?? @js(__('Request failed'));
                        toastr.error($('<div>').text(message).html());
                    }
                    this.busy = false;
                }
            },
        }));
    });
</script>
@endPushOnce
