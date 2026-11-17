@props(['device', 'types'])

<x-modal show="formOpen" maxWidth="xl">
    <x-slot:heading>
        <h4 class="tw:m-0 tw:text-base tw:font-semibold" x-text="editUrl ? @js(__('Edit Service')) : @js(__('Add Service'))"></h4>
    </x-slot:heading>

    <form class="form-horizontal" x-ref="form" x-on:submit.prevent="saveService()" id="service-form">
        <ul class="alert alert-danger" x-cloak x-show="errors.length" role="alert">
            <template x-for="(error, index) in errors" :key="index">
                <li x-text="error"></li>
            </template>
        </ul>
        @if(! $device && $types)
            <div class="form-group" x-show="! editUrl">
                <label for="service-device" class="col-sm-3 control-label">{{ __('Device') }}</label>
                <div class="col-sm-9">
                    <select id="service-device" x-ref="device" class="form-control tw:w-full"></select>
                </div>
            </div>
        @endif
        <div class="form-group">
            <label for="service-name" class="col-sm-3 control-label">{{ __('Name') }}</label>
            <div class="col-sm-9">
                <input type="text" id="service-name" maxlength="255" class="form-control input-sm" x-model="form.service_name">
            </div>
        </div>
        <div class="form-group">
            <label for="service-type" class="col-sm-3 control-label">{{ __('Check Type') }}</label>
            <div class="col-sm-9">
                <p class="form-control-static" x-show="editUrl" x-text="form.service_type"></p>
                @if($types)
                    <select id="service-type" class="form-control" x-model="form.service_type" x-show="! editUrl" x-bind:disabled="!!editUrl" required>
                        @foreach($types as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                @else
                    <p class="form-control-static" x-show="! editUrl">{{ __('No service checks are installed.') }}</p>
                @endif
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
                <input type="text" id="service-ip" maxlength="255" class="form-control" placeholder="{{ __('IP Address or Hostname') }}" x-model="form.service_ip">
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
            <div class="col-sm-offset-3 col-sm-9 tw:flex tw:flex-col tw:items-start tw:gap-3">
                <x-toggle model="form.service_ignore" :label="__('Ignore alert tag')" />
                <x-toggle model="form.service_disabled" :label="__('Disable polling and alerting')" />
            </div>
        </div>
    </form>

    <x-slot:footer>
        <button type="button" class="btn btn-default" x-on:click="formOpen = false">{{ __('Cancel') }}</button>
        <button type="submit" form="service-form" class="btn btn-primary" x-bind:disabled="busy || (!editUrl && !form.service_type)">{{ __('Save Service') }}</button>
    </x-slot:footer>
</x-modal>

<x-modal show="deleteOpen" :title="__('Confirm Delete')" maxWidth="md">
    <p>{{ __('Please confirm that you would like to delete this service.') }}</p>
    <x-slot:footer>
        <button type="button" class="btn btn-default" x-on:click="deleteOpen = false">{{ __('Cancel') }}</button>
        <button type="button" class="btn btn-danger" x-on:click="request('DELETE', deleteUrl)" x-bind:disabled="busy">{{ __('Delete') }}</button>
    </x-slot:footer>
</x-modal>
