@props([
    'device',
    'maintenance' => false,
    'maintenanceId' => 0,
    'defaultBehavior' => \LibreNMS\Enum\MaintenanceBehavior::SkipAlerts->value,
])

<div x-data="{
        deviceId: @js($device->device_id),
        deviceName: @js($device->displayName()),
        maintenanceId: @js($maintenanceId ?: null),
        inMaintenance: @js((bool) $maintenance),
        notes: '',
        duration: '0:30',
        behavior: @js((int) $defaultBehavior),
        loading: false,
        showForm: false,
        showConfirm: false,
        open() {
            if (this.inMaintenance) {
                this.showConfirm = true;
            } else {
                this.showForm = true;
            }
        },
        start() {
            this.loading = true;
            axios.post(@js(route('alert-schedule.store')), {
                title: this.deviceName,
                notes: this.notes,
                behavior: this.behavior,
                recurring: 0,
                start: new Date().toISOString(),
                duration: this.duration,
                maps: [this.deviceId],
            }).then((response) => {
                this.inMaintenance = true;
                this.maintenanceId = response.data.schedule_id;
                this.showForm = false;
                toastr.success(response.data.message);
            }).catch((error) => {
                toastr.error(error.response?.data?.message || @js(__('components.maintenance-mode.errors.enable')));
            }).finally(() => this.loading = false);
        },
        end() {
            this.loading = true;
            axios.post(route('alert-schedule.end', {alert_schedule: this.maintenanceId})).then((response) => {
                this.inMaintenance = false;
                this.maintenanceId = null;
                toastr.success(response.data.message);
            }).catch((error) => {
                toastr.error(error.response?.data?.message || @js(__('components.maintenance-mode.errors.disable')));
            }).finally(() => {
                this.loading = false;
                this.showConfirm = false;
            });
        },
    }">
    <button type="button"
            id="maintenance"
            class="btn"
            :class="inMaintenance ? 'btn-warning' : 'btn-success'"
            :disabled="inMaintenance && ! maintenanceId"
            x-on:click="open()">
        <i class="fa fa-wrench"></i>
        <span x-text="inMaintenance ? @js(__('components.maintenance-mode.button.device_under_maintenance')) : @js(__('components.maintenance-mode.button.maintenance_mode'))">{{ $maintenance ? __('components.maintenance-mode.button.device_under_maintenance') : __('components.maintenance-mode.button.maintenance_mode') }}</span>
    </button>

    <x-modal show="showConfirm" :title="__('components.maintenance-mode.titles.end_maintenance')" maxWidth="md">
        <p>{{ __('components.maintenance-mode.confirm.end_prompt') }}</p>
        <x-slot:footer>
            <button type="button" class="btn btn-default" x-on:click="showConfirm = false">{{ __('No') }}</button>
            <button type="button" class="btn btn-warning" x-on:click="end()" :disabled="loading">{{ __('Yes') }}</button>
        </x-slot:footer>
    </x-modal>

    <x-modal show="showForm" :title="__('components.maintenance-mode.titles.device_maintenance')" maxWidth="xl">
        <div class="tw:grid tw:grid-cols-5 tw:gap-x-6 tw:gap-y-5 tw:items-center">
            <label for="maintenance-notes" class="tw:col-span-1 tw:self-start tw:pt-2 tw:mb-0 tw:font-medium">{{ __('components.maintenance-mode.form.notes_label') }}</label>
            <div class="tw:col-span-4">
                <textarea id="maintenance-notes" class="form-control" rows="3" x-model="notes"
                          placeholder="{{ __('components.maintenance-mode.form.notes_placeholder') }}"></textarea>
            </div>

            <label for="maintenance-duration" class="tw:col-span-1 tw:mb-0 tw:font-medium">{{ __('components.maintenance-mode.form.duration_label') }}</label>
            <div class="tw:col-span-4">
                <select id="maintenance-duration" class="form-control" x-model="duration">
                    @foreach (range(0, 23) as $hour)
                        @foreach (['00', '30'] as $minute)
                            @continue($hour === 0 && $minute === '00')
                            <option value="{{ $hour }}:{{ $minute }}">{{ $hour }}:{{ $minute }}h</option>
                        @endforeach
                    @endforeach
                </select>
            </div>

            <label for="maintenance-behavior" class="tw:col-span-1 tw:mb-0 tw:font-medium">{{ __('components.maintenance-mode.form.behavior_label') }}</label>
            <div class="tw:col-span-4">
                <select id="maintenance-behavior" class="form-control" x-model.number="behavior">
                    @foreach (\LibreNMS\Enum\MaintenanceBehavior::cases() as $behavior)
                        <option value="{{ $behavior->value }}">{{ $behavior->descr() }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <x-slot:footer>
            <button type="button" class="btn btn-default" x-on:click="showForm = false">{{ __('Cancel') }}</button>
            <button type="button" id="maintenance-submit" class="btn btn-success" x-on:click="start()" :disabled="loading">
                {{ __('components.maintenance-mode.form.start_maintenance') }}
            </button>
        </x-slot:footer>
    </x-modal>
</div>
