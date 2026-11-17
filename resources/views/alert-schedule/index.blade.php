@extends('layouts.librenmsv1')

@section('title', __('Alert Schedule'))

@section('content')
<div class="container-fluid" x-data="alertSchedule(@js(['behavior' => $defaultBehavior]))">
    <x-panel>
        <x-slot:table>
            <div class="table-responsive">
                <table id="alert-schedule" class="table table-condensed table-hover" data-url="{{ route('table.alert-schedule') }}">
                    <thead>
                        <tr>
                            <th data-column-id="title">{{ __('Title') }}</th>
                            <th data-column-id="behavior">{{ __('Behavior') }}</th>
                            <th data-column-id="recurring">{{ __('Recurring') }}</th>
                            <th data-column-id="start" data-order="desc">{{ __('Start (no recurring)') }}</th>
                            <th data-column-id="end">{{ __('End (no recurring)') }}</th>
                            <th data-column-id="start_recurring_dt">{{ __('Start recurring dt') }}</th>
                            <th data-column-id="end_recurring_dt">{{ __('End recurring dt') }}</th>
                            <th data-column-id="start_recurring_hr">{{ __('Start recurring hr') }}</th>
                            <th data-column-id="end_recurring_hr">{{ __('End recurring hr') }}</th>
                            <th data-column-id="recurring_day" data-sortable="false" data-searchable="false">{{ __('Recurring on days') }}</th>
                            <th data-column-id="actions" data-sortable="false" data-searchable="false" data-formatter="actions">{{ __('Actions') }}</th>
                            <th data-column-id="status" data-searchable="false" data-formatter="status">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </x-slot:table>
    </x-panel>

    <template id="alert-schedule-actions">
        @can('create', \App\Models\AlertSchedule::class)
            <button type="button" class="btn btn-primary btn-sm" x-data x-on:click="$dispatch('schedule-edit', {id: null})">{{ __('Schedule maintenance') }}</button>
        @endcan
    </template>

    <x-modal show="formOpen" maxWidth="xl">
        <x-slot:heading>
            <h4 class="tw:m-0 tw:text-base tw:font-semibold" x-text="id ? @js(__('Edit Schedule')) : @js(__('Create Schedule'))"></h4>
        </x-slot:heading>

        <form id="schedule-form" class="form-horizontal" x-on:submit.prevent="save()">
            <div class="form-group">
                <label for="schedule-title" class="col-sm-4 control-label">{{ __('Title') }} *</label>
                <div class="col-sm-8">
                    <input type="text" id="schedule-title" class="form-control" x-model="form.title" placeholder="{{ __('Maintenance title') }}" required>
                </div>
            </div>
            <div class="form-group">
                <label for="schedule-notes" class="col-sm-4 control-label">{{ __('Notes') }}</label>
                <div class="col-sm-8">
                    <textarea id="schedule-notes" class="form-control" x-model="form.notes" placeholder="{{ __('Maintenance notes') }}"></textarea>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-4 col-sm-8">
                    <div class="checkbox">
                        <label><input type="checkbox" x-model="form.recurring"> {{ __('Recurring') }}</label>
                    </div>
                </div>
            </div>
            <template x-if="! form.recurring">
                <div>
                    <div class="form-group">
                        <label for="schedule-start" class="col-sm-4 control-label">{{ __('Start') }} *</label>
                        <div class="col-sm-8">
                            <input type="datetime-local" id="schedule-start" class="form-control" x-model="form.start" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="schedule-end" class="col-sm-4 control-label">{{ __('End') }} *</label>
                        <div class="col-sm-8">
                            <input type="datetime-local" id="schedule-end" class="form-control" x-model="form.end" x-bind:min="form.start" required>
                        </div>
                    </div>
                </div>
            </template>
            <template x-if="form.recurring">
                <div>
                    <div class="form-group">
                        <label for="schedule-start-dt" class="col-sm-4 control-label">{{ __('Start date') }} *</label>
                        <div class="col-sm-8">
                            <input type="date" id="schedule-start-dt" class="form-control" x-model="form.start_recurring_dt" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="schedule-end-dt" class="col-sm-4 control-label">{{ __('End date') }}</label>
                        <div class="col-sm-8">
                            <input type="date" id="schedule-end-dt" class="form-control" x-model="form.end_recurring_dt" x-bind:min="form.start_recurring_dt">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="schedule-start-hr" class="col-sm-4 control-label">{{ __('Start hour') }} *</label>
                        <div class="col-sm-8">
                            <input type="time" id="schedule-start-hr" class="form-control" x-model="form.start_recurring_hr" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="schedule-end-hr" class="col-sm-4 control-label">{{ __('End hour') }} *</label>
                        <div class="col-sm-8">
                            <input type="time" id="schedule-end-hr" class="form-control" x-model="form.end_recurring_hr" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">{{ __('Only on weekday') }}</label>
                        <div class="col-sm-8">
                            @foreach(['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su'] as $index => $day)
                                <label class="checkbox-inline"><input type="checkbox" value="{{ $index + 1 }}" x-model.number="form.recurring_day"> {{ __($day) }}</label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </template>
            <div class="form-group">
                <label for="schedule-behavior" class="col-sm-4 control-label">{{ __('Behavior') }} *</label>
                <div class="col-sm-8">
                    <select id="schedule-behavior" class="form-control" x-model.number="form.behavior">
                        @foreach(\LibreNMS\Enum\MaintenanceBehavior::cases() as $behavior)
                            <option value="{{ $behavior->value }}">{{ $behavior->descr() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="schedule-maps" class="col-sm-4 control-label">{{ __('Map To') }} *</label>
                <div class="col-sm-8">
                    <select id="schedule-maps" x-ref="maps" class="form-control" multiple style="width: 100%"></select>
                </div>
            </div>
        </form>

        <x-slot:footer>
            <button type="button" class="btn btn-default" x-on:click="formOpen = false">{{ __('Cancel') }}</button>
            <button type="submit" form="schedule-form" class="btn btn-success" x-bind:disabled="saving">{{ __('Schedule maintenance') }}</button>
        </x-slot:footer>
    </x-modal>

    <x-modal show="deleteId" :title="__('Confirm Delete')" maxWidth="md">
        <p>{{ __('Please confirm that you would like to delete this maintenance schedule.') }}</p>
        <x-slot:footer>
            <button type="button" class="btn btn-default" x-on:click="deleteId = null">{{ __('Cancel') }}</button>
            <button type="button" class="btn btn-danger" x-on:click="destroy()" x-bind:disabled="saving">{{ __('Delete') }}</button>
        </x-slot:footer>
    </x-modal>
</div>
@endsection

@push('scripts')
<script>
    const scheduleDays = {Mo: 1, Tu: 2, We: 3, Th: 4, Fr: 5, Sa: 6, Su: 7};

    document.addEventListener('alpine:init', () => {
        Alpine.data('alertSchedule', (defaults) => ({
            formOpen: false,
            saving: false,
            id: null,
            editGeneration: 0,
            deleteId: null,
            form: {},

            init() {
                window.addEventListener('schedule-edit', (event) => this.edit(event.detail.id));
                window.addEventListener('schedule-delete', (event) => this.deleteId = event.detail.id);
            },

            edit(id) {
                const generation = ++this.editGeneration;

                if (! id) {
                    this.id = null;
                    this.form = {
                        title: '',
                        notes: '',
                        recurring: false,
                        start: moment().format('YYYY-MM-DDTHH:mm'),
                        end: moment().add(1, 'hour').format('YYYY-MM-DDTHH:mm'),
                        start_recurring_dt: moment().format('YYYY-MM-DD'),
                        end_recurring_dt: '',
                        start_recurring_hr: '',
                        end_recurring_hr: '',
                        recurring_day: [],
                        behavior: defaults.behavior,
                    };
                    this.open([]);
                    return;
                }

                this.request('GET', route('alert-schedule.edit', {alert_schedule: id})).then((schedule) => {
                    if (generation !== this.editGeneration) {
                        return;
                    }

                    this.form = {
                        title: schedule.title,
                        notes: schedule.notes ?? '',
                        recurring: Boolean(schedule.recurring),
                        start: moment(schedule.start).format('YYYY-MM-DDTHH:mm'),
                        end: moment(schedule.end).format('YYYY-MM-DDTHH:mm'),
                        start_recurring_dt: schedule.start_recurring_dt,
                        end_recurring_dt: schedule.end_recurring_dt ?? '',
                        start_recurring_hr: schedule.start_recurring_hr,
                        end_recurring_hr: schedule.end_recurring_hr,
                        recurring_day: (schedule.recurring_day ?? []).map((day) => scheduleDays[day]),
                        behavior: schedule.behavior,
                    };
                    this.id = id;
                    this.open(schedule.targets);
                }).catch(() => {});
            },

            open(targets) {
                this.formOpen = true;
                this.$nextTick(() => {
                    // select2 sizes itself when created, so wait until the dialog is visible
                    const $maps = $(this.$refs.maps);
                    if (! $maps.hasClass('select2-hidden-accessible')) {
                        $maps.select2({
                            width: '100%',
                            placeholder: @js(__('Devices, Groups or Locations')),
                            dropdownParent: $(this.$el),
                            ajax: {url: @js(route('ajax.select.devices-groups-locations')), delay: 150},
                        });
                    }

                    $maps.empty();
                    targets.forEach((target) => $maps.append(new Option(target.text, target.id, true, true)));
                    $maps.trigger('change');
                });
            },

            save() {
                const data = {...this.form, maps: $(this.$refs.maps).val()};
                if (! data.recurring) {
                    // send the browser's local time with its offset
                    data.start = moment(data.start).format();
                    data.end = moment(data.end).format();
                }

                const request = this.id
                    ? this.request('PUT', route('alert-schedule.update', {alert_schedule: this.id}), data)
                    : this.request('POST', route('alert-schedule.store'), data);

                request.then((response) => {
                    this.formOpen = false;
                    toastr.success(response.message);
                    $('#alert-schedule').bootgrid('reload');
                }).catch(() => {});
            },

            destroy() {
                this.request('DELETE', route('alert-schedule.destroy', {alert_schedule: this.deleteId})).then((response) => {
                    this.deleteId = null;
                    toastr.success(response.message);
                    $('#alert-schedule').bootgrid('reload');
                }).catch(() => {});
            },

            request(method, url, data) {
                this.saving = true;

                return axios({method, url, data})
                    .then((response) => response.data)
                    .catch((error) => {
                        const errors = Object.values(error.response?.data?.errors ?? {}).flat();
                        toastr.error(errors.length ? errors.join(' ') : (error.response?.data?.message ?? @js(__('Request failed'))));
                        throw error;
                    })
                    .finally(() => this.saving = false);
            },
        }));
    });

    $('#alert-schedule').bootgrid({
        ajax: true,
        rowCount: [50, 100, 250, -1],
        formatters: {
            actions: function (column, row) {
                let response = '';
                @can('update', \App\Models\AlertSchedule::class)
                    response += '<button type="button" class="btn btn-xs btn-primary" onclick="window.dispatchEvent(new CustomEvent(\'schedule-edit\', {detail: {id: ' + row.id + '}}))" title="' + @js(__('Edit')) + '"><i class="fa fa-pencil"></i></button> ';
                @endcan
                @can('delete', \App\Models\AlertSchedule::class)
                    response += '<button type="button" class="btn btn-xs btn-danger" onclick="window.dispatchEvent(new CustomEvent(\'schedule-delete\', {detail: {id: ' + row.id + '}}))" title="' + @js(__('Delete')) + '"><i class="fa fa-trash-o"></i></button>';
                @endcan
                return response;
            },
            status: function (column, row) {
                const labels = {
                    0: '<span class="label label-warning">' + @js(__('Set')) + '</span>',
                    1: '<span class="label label-danger">' + @js(__('Lapsed')) + '</span>',
                    2: '<span class="label label-success">' + @js(__('Current')) + '</span>',
                };
                return labels[row.status] ?? '';
            }
        }
    });
    $('#alert-schedule-header .actionBar').prepend(document.getElementById('alert-schedule-actions').content.cloneNode(true));
</script>
@endpush
