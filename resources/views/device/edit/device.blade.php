@extends('layouts.librenmsv1')

@section('content')
    <x-device.page :device="$device">
        <x-device.edit-tabs :device="$device" />

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row">
            <div class="col-sm-6 col-sm-offset-2 tw:justify-between tw:flex tw:flex-wrap">
                <form id="delete_host" name="delete_host" method="post" action="delhost/" role="form" class="tw:inline-block">
                    @csrf
                    <input type="hidden" name="id" value="{{ $device->device_id }}">
                    <button type="submit" class="btn btn-danger" name="Submit"><i class="fa fa-trash"></i> {{ __('device.edit.delete_device') }}</button>
                </form>

                @if(LibrenmsConfig::get('enable_clear_discovery') && ! $device->snmp_disable)
                    <button type="submit" id="rediscover" data-device_id="{{ $device->device_id }}"
                            class="btn btn-primary" name="rediscover" title="{{ __('device.edit.rediscover_title') }}">
                        <i class="fa fa-retweet"></i> {{ __('device.edit.rediscover') }}
                    </button>
                @endif
            </div>
        </div>
        <br>

        <form id="edit" name="edit" method="post" action="{{ route('device.edit.update', [$device->device_id]) }}" role="form" class="form-horizontal">
            @method('PUT')
            @csrf
            <div class="form-group" data-toggle="tooltip" data-container="body" data-placement="bottom" title="{{ __('device.edit.hostname_title') }}" >
                <label for="edit-hostname-input" class="col-sm-2 control-label" >{{ __('device.edit.hostname_ip') }}</label>
                <div class="col-sm-6">
                    <input type="text" id="edit-hostname-input" name="hostname" class="form-control" disabled value="{{ old('hostname', $device->hostname) }}" />
                </div>
                <div class="col-sm-2">
                    <button type="button" name="hostname-edit-button" id="hostname-edit-button" class="btn btn-danger" onclick="toggleHostnameEdit()"> <i class="fa fa-pencil"></i> </button>
                </div>
            </div>

            <div class="form-group" data-toggle="tooltip" data-container="body" data-placement="bottom" title="{{ __('device.edit.display_title', ['sysName' => $device->sysName]) }}" >
                <label for="edit-display-input" class="col-sm-2 control-label" >{{ __('device.edit.display_name') }}</label>
                <div class="col-sm-6">
                    <input type="text" id="edit-display-input" name="display" class="form-control" placeholder="{{ __('device.edit.system_default') }}" value="{{ old('display', $device->display) }}">
                </div>
            </div>

            <div class="form-group" data-toggle="tooltip" data-container="body" data-placement="bottom" title="{{ __('device.edit.overwrite_ip_title') }}" >
                <label for="edit-overwrite_ip-input" class="col-sm-2 control-label text-danger" >{{ __('device.edit.overwrite_ip') }}</label>
                <div class="col-sm-6">
                    <input type="text" id="edit-overwrite_ip-input" name="overwrite_ip" class="form-control" value="{{ old('overwrite_ip', $device->overwrite_ip) }}">
                </div>
            </div>

            <div class="form-group">
                <label for="descr" class="col-sm-2 control-label">{{ __('device.edit.description') }}</label>
                <div class="col-sm-6">
                    <textarea id="descr" name="purpose" class="form-control">{{ old('purpose', $device->purpose) }}</textarea>
                </div>
            </div>

            <div class="form-group">
                <label for="type" class="col-sm-2 control-label">{{ __('device.edit.type') }}</label>
                <div class="col-sm-6">
                    <select id="type" name="type" class="form-control">
                        @foreach($types as $type => $type_data)
                            <option value="{{ $type }}" {{ old('type', $device->type) == $type ? 'selected' : '' }} data-icon="{{ $type_data['icon'] }}">
                                {{ $type_data['text'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="sysLocation" class="col-sm-2 control-label">{{ __('device.edit.override_sysLocation') }}</label>
                <div class="col-sm-6">
                    <input onChange="edit.sysLocation.disabled=!edit.override_sysLocation.checked; edit.sysLocation.select()"
                           type="checkbox" name="override_sysLocation" data-size="small"
                            {{ old('override_sysLocation', $device->override_sysLocation) ? 'checked' : '' }}
                    />
                </div>
            </div>
            <div class="form-group" title="{{ __('device.edit.coordinates_title') }}">
                <div class="col-sm-2"></div>
                <div class="col-sm-6">
                    <input id="sysLocation" name="sysLocation" class="form-control"
                           {{ old('override_sysLocation', $device->override_sysLocation) ? '' : 'disabled' }}
                             value="{{ old('sysLocation', $device->location?->location) }}" />
                </div>
            </div>

            <div class="form-group">
                <label for="override_sysContact" class="col-sm-2 control-label">{{ __('device.edit.override_sysContact') }}</label>
                <div class="col-sm-6">
                    <input onChange="edit.override_sysContact_string.disabled=!edit.override_sysContact.checked"
                           type="checkbox" id="override_sysContact" name="override_sysContact" data-size="small"
                            {{ old('override_sysContact', $override_sysContact_bool) ? 'checked' : '' }}
                    />
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-2">
                </div>
                <div class="col-sm-6">
                    <input id="override_sysContact_string" class="form-control" name="override_sysContact_string" size="32"
                           {{ old('override_sysContact', $override_sysContact_bool) ? '' : 'disabled' }}
                           data-override="{{ $override_sysContact_string }}"
                           data-default="{{ $device->sysContact }}"
                           value="{{ old('override_sysContact_string', $override_sysContact_bool ? $override_sysContact_string : $device->sysContact) }}"
                    />
                </div>
            </div>

            <div class="form-group">
                <label for="parent_id" class="col-sm-2 control-label">{{ __('device.edit.depends_on') }}</label>
                <div class="col-sm-6">
                    <select multiple name="parent_id[]" id="parent_id" class="form-control" style="width: 100%">
                        <option value="0" {{ empty($parents) ? 'selected' : '' }}>{{ __('device.edit.none') }}</option>
                        @foreach ($devices as $dev)
                            <option value="{{ $dev->device_id }}" {{ $parents->contains($dev->device_id) ? 'selected' : '' }}>
                                {{ $dev->hostname }} ({{ $dev->sysName }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            @config('distributed_poller')
            <div class="form-group">
                <label for="poller_group" class="col-sm-2 control-label">{{ __('device.edit.poller_group') }}</label>
                <div class="col-sm-6">
                    <select name="poller_group" id="poller_group" class="form-control input-sm">
                        <option value="0">{{ __('device.edit.poller_group_general') }}{{$default_poller_group == 0 ? ' ' . __('device.edit.default_poller') : ''}}</option>
                        @foreach($poller_groups as $group_id => $group_name)
                            <option value="{{ $group_id }}" {{ old('poller_group', $device->poller_group) == $group_id ? 'selected' : '' }}>
                                {{ $group_name }}{{ $default_poller_group == $group_id ? ' ' . __('device.edit.default_poller') : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            @endconfig

            <div class="form-group">
                <label for="disabled" class="col-sm-2 control-label">{{ __('device.edit.disable_polling_alerting') }}</label>
                <div class="col-sm-6">
                    <input name="disabled" type="checkbox" id="disabled" value="1" data-size="small"
                       {{ old('disabled', $device->disabled) ? 'checked' : '' }}
                    />
                </div>
            </div>

            <div class="form-group">
                <label for="maintenance" class="col-sm-2 control-label"></label>
                <div class="col-sm-6">
                    <div id="app">
                        <maintenance-mode
                            :device-id="{{ $device->device_id }}"
                            device-name="{{ $device->displayName() }}"
                            :maintenance-id="{{ $exclusive_maintenance_id }}"
                            :default-maintenance-behavior="{{ $default_maintenance_behavior }}"
                            :maintenance="{{ $maintenance ? 'true' : 'false' }}"
                        ></maintenance-mode>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="disable_notify" class="col-sm-2 control-label">{{ __('device.edit.disable_alerting') }}</label>
                <div class="col-sm-6">
                    <input id="disable_notify" type="checkbox" name="disable_notify" data-size="small"
                       {{ old('disable_notify', $device->disable_notify) ? 'checked' : '' }}
                    />
                </div>
            </div>
            <div class="form-group">
                <label for="ignore" class="col-sm-2 control-label" title="{{ __('device.edit.ignore_alert_tag_title') }}">{{ __('device.edit.ignore_alert_tag') }}</label>
                <div class="col-sm-6">
                    <input name="ignore" type="checkbox" id="ignore" value="1" data-size="small"
                       {{ old('ignore', $device->ignore) ? 'checked' : '' }}
                    />
                </div>
            </div>
            <div class="form-group">
                <label for="ignore_status" class="col-sm-2 control-label" title="{{ __('device.edit.ignore_device_status_title') }}">{{ __('device.edit.ignore_device_status') }}</label>
                <div class="col-sm-6">
                    <input name="ignore_status" type="checkbox" id="ignore_status" value="1" data-size="small"
                       {{ old('ignore_status', $device->ignore_status) ? 'checked' : '' }}
                    />
                </div>
            </div>
            <div class="row">
                <div class="col-md-1 col-md-offset-2">
                    <button type="submit" name="Submit"  class="btn btn-default"><i class="fa fa-check"></i> {{ __('device.edit.save') }}</button>
                </div>
            </div>
        </form>
        <br />
        <div class="panel panel-default">
            <div class="panel-heading">
                @if($rrd_num)
                {{ __('device.edit.size_on_disk') }}: <b>{{ $rrd_size }}</b> in <b>{{ $rrd_num }}</b> {{ __('device.edit.rrd_files') }} |
                @endif
                {{ __('device.edit.last_polled') }}: <b>{{ $device->last_polled }}</b>
                @if($device->last_discovered)
                    | {{ __('device.edit.last_discovered') }}: <b>{{ $device->last_discovered }}</b>
                @endif
            </div>
        </div>
    </x-device.page>
@endsection

@push('scripts')
    <script>
        const defaultType = '{{ $default_type }}';
        function templateTypeSelection(option) {
            if (!option.id) { // placeholder
                return option.text;
            }
            const iconClass = $(option.element).data('icon');
            if (option.id && iconClass) {
                let $container = $('<span>');
                let $icon = $('<i>').addClass(`fa-solid fa-${iconClass} fa-fw fa-lg`);
                let $text = $('<span>').text(option.text);

                return $container.append($icon).append($text);
            }
            return option.text;
        }
        $('#type').select2({
            placeholder: 'Select or enter a device type',
            templateResult: templateTypeSelection,
            templateSelection: templateTypeSelection,
            tags: true,
            allowClear: true,
        }).on('select2:clearing', function(e) {
            // reset to the default value when clearing
            e.preventDefault();
            setTimeout(function() {
                $('#type').val(defaultType).trigger('change');
            }, 10);
        }).on('change select2:select initialized', function() {
            // hide the clear button when default is selected
            const currentValue = $(this).val();
            $(this).parent().find('.select2-selection__clear').toggle(currentValue !== defaultType);
        }).trigger('initialized');

        $('[type="checkbox"]').bootstrapSwitch('offColor', 'danger');
        $('#override_sysContact').on('switchChange.bootstrapSwitch', function(event, state) {
            var $input = $('#override_sysContact_string');
            var newValue = state ? $input.data('override') : $input.data('default');

            if (!state || newValue) {
                $input.val(newValue);
            }
        });
        $("#rediscover").on("click", function() {
                fetch('{{ route('device.rediscover', [$device->device_id]) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content
                    }
                })
                    .then(r => r.json())
                    .then(d => toastr[d.status === 'ok' ? 'success' : 'error'](d.message))
                    .catch(() => toastr.error('An error occurred setting this device to be rediscovered'));
        });

        function toggleHostnameEdit() {
            document.getElementById('edit-hostname-input').disabled = ! document.getElementById('edit-hostname-input').disabled;
        }
        $('#parent_id').select2({
            width: 'resolve'
        });
    </script>
    @vuei18n
@endpush
