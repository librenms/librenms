@extends('layouts.librenmsv1')

@section('title', __('Edit user'))

@section('content')
<div class="container" style="font-size: 16px;">
    <x-panel>
        <x-slot:heading>
            <div class="tw:flex tw:justify-between tw:items-center">
                <div class="tw:text-4xl">
                    <i class="fa fa-user-circle-o fa-fw fa-lg" aria-hidden="true"></i>
                    {{ __('permissions.permissions.user_permissons', ['name' => $user->realname ?: $user->username]) }}
                </div>
                <a href="{{ route('users.index') }}" class="tw:focus:outline-none tw:text-gray-500 tw:hover:text-red-700! tw:dark:hover:text-red-800! tw:transition-colors">
                    <span class="tw:sr-only">Close and return to user management page</span>
                    <i class="fas fa-x"></i>
                </a>
            </div>
        </x-slot:heading>
        <x-slot:slot class="tw:p-0 tw:pt-3">
        <x-tabs active="{{ $tab }}">
            <x-tab name="{{ __('permissions.permissions.device_access', ['count' => $deviceCount]) }}" value="device">
                    @if($deviceCount == 'all')
                        <h4 class="tw:m-3">{{ __('permissions.permissions.device_all') }}</h4>
                    @else
                    <div class="tw:p-5">
                    <h4>{{ __('Grant access to new device') }}</h4>
                    <form class="form-inline" role="form" method="post"
                          action="{{ route('users.permissions.device.attach', $user) }}">
                        @csrf
                        <div class="form-group">
                            <label class="sr-only" for="device_id">{{ __('Device') }}</label>
                            <select name="device_id" id="device_id" class="form-control"></select>
                        </div>
                        <button type="submit" class="btn btn-default" name="submit">{{ __('Add') }}</button>
                    </form>
                    </div>
                    <table class="table table-hover table-condensed table-striped tw:mb-0">
                        <tr>
                            <th>{{ __('Device') }}</th>
                            <th>{{ __('Action') }}</th>
                        </tr>
                        @forelse($devicePermissions as $devicePermission)
                            <tr>
                                <td><strong>{{ $devicePermission->displayName() }}</strong></td>
                                <td>
                                    <form
                                        action="{{ route('users.permissions.device.detach', [$user, $devicePermission->device_id]) }}"
                                        method="POST" class="tw:inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-link tw:p-0"
                                                aria-label="{{ __('Delete') }}">
                                            <i class="fa fa-trash fa-lg icon-theme" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2">{{ __('permissions.permissions.none_configured') }}</td>
                            </tr>
                        @endforelse
                    </table>
                    @endif
            </x-tab>

            <x-tab name="{{ __('permissions.permissions.device_group_access', ['count' => $deviceGroupCount]) }}" value="device-group">
                    @if($deviceGroupCount == 'all')
                        <h4 class="tw:m-3">{{ __('permissions.permissions.device_group_all') }}</h4>
                    @else
                    <div class="tw:p-5">
                        <h4>{{ __('Grant access to new Device Group') }}</h4>
                        <form class="form-inline" role="form" method="post"
                              action="{{ route('users.permissions.device-group.attach', $user) }}">
                            @csrf
                            <div class="form-group">
                                <label class="sr-only" for="device_group_id">{{ __('Device Group') }}</label>
                                <select name="device_group_id" id="device_group_id" class="form-control"></select>
                            </div>
                            <button type="submit" class="btn btn-default" name="submit">{{ __('Add') }}</button>
                        </form>
                    </div>
                    <table class="table table-hover table-condensed table-striped tw:mb-0">
                        <tr>
                            <th>{{ __('Device Group') }}</th>
                            <th>{{ __('Action') }}</th>
                        </tr>
                        @forelse($deviceGroupPermissions as $deviceGroupPermission)
                            <tr>
                                <td><strong>{{ $deviceGroupPermission->name }}</strong></td>
                                <td>
                                    <form
                                        action="{{ route('users.permissions.device-group.detach', [$user, $deviceGroupPermission->id]) }}"
                                        method="POST" class="tw:inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-link tw:p-0"
                                                aria-label="{{ __('Delete') }}">
                                            <i class="fa fa-trash fa-lg icon-theme" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2">{{ __('permissions.permissions.none_configured') }}</td>
                            </tr>
                        @endforelse
                    </table>
                    @endif
            </x-tab>

            <x-tab name="{{ __('permissions.permissions.port_access', ['count' => $portCount]) }}" value="port">
                    @if($portCount == 'all')
                        <h4 class="tw:m-3">{{ __('permissions.permissions.port_all') }}</h4>
                    @else
                    <div class="tw:p-5">
                        <h4>{{ __('Grant access to new interface') }}</h4>
                        <form action="{{ route('users.permissions.port.attach', $user) }}" method="post" class="form-horizontal"
                              role="form">
                            @csrf
                            <div class="form-group">
                                <label for="device" class="col-sm-2 control-label">{{ __('Device:') }}</label>
                                <div class="col-sm-10">
                                    <select id="device" class="form-control" name="device"
                                            onchange='window.port_device_id = this.value; $("#port_id").empty().trigger("change");'></select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="port_id" class="col-sm-2 control-label">{{ __('Port:') }}</label>
                                <div class="col-sm-10">
                                    <select class="form-control" id="port_id" name="port_id"></select>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <button type="submit" class="btn btn-default" name="submit"
                                            value="Add">{{ __('Add') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <table class="table table-hover table-condensed table-striped tw:mb-0">
                        <tr>
                            <th>{{ __('Port') }}</th>
                            <th>{{ __('Action') }}</th>
                        </tr>
                        @forelse($portPermissions as $portPermission)
                            <tr>
                                <td>
                                    <strong>{{ $portPermission->device?->displayName() }}
                                        - {{ $portPermission->getLabel() }}</strong>
                                    {{ $portPermission->getDescription() }}
                                </td>
                                <td>
                                    <form
                                        action="{{ route('users.permissions.port.detach', [$user, $portPermission->port_id]) }}"
                                        method="POST" class="tw:inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-link tw:p-0"
                                                aria-label="{{ __('Delete') }}">
                                            <i class="fa fa-trash fa-lg icon-theme" aria-hidden="true"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2">{{ __('permissions.permissions.none_configured') }}</td>
                            </tr>
                        @endforelse
                    </table>
                    @endif
            </x-tab>

            <x-tab name="{{ __('permissions.permissions.bill_access', ['count' => $billCount]) }}" value="bill">
                    @if($billCount == 'all')
                        <h4 class="tw:m-3">{{ __('permissions.permissions.bill_all') }}</h4>
                    @else
                        <div class="tw:p-5">
                            <h4>{{ __('Grant access to new bill') }}</h4>
                            <form method="post" action="{{ route('users.permissions.bill.attach', $user) }}"
                                  class="form-inline" role="form">
                                @csrf
                                <div class="form-group">
                                    <label class="sr-only" for="bill_id">{{ __('Bill') }}</label>
                                    <select name="bill_id" class="form-control" id="bill_id"></select>
                                </div>
                                <button type="submit" class="btn btn-default" name="submit"
                                        value="Add">{{ __('Add') }}</button>
                            </form>
                        </div>
                        <table class="table table-hover table-condensed table-striped tw:mb-0">
                            <tr>
                                <th>{{ __('Bill') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                            @forelse($billPermissions as $billPermission)
                                <tr>
                                    <td><strong>{{ $billPermission->bill_name }}</strong></td>
                                    <td>
                                        <form
                                            action="{{ route('users.permissions.bill.detach', [$user, $billPermission->bill_id]) }}"
                                            method="POST" class="tw:inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-link tw:p-0"
                                                    aria-label="{{ __('Delete') }}">
                                                <i class="fa fa-trash fa-lg icon-theme" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2">{{ __('permissions.permissions.none_configured') }}</td>
                                </tr>
                            @endforelse
                        </table>
                    @endif
            </x-tab>
        </x-tabs>
        </x-slot:slot>
    </x-panel>
</div>
@endsection

@section('javascript')
<script>
    const userId = @js($user->user_id);
    const allowDynamic = @js($allowDynamic);
    window.port_device_id = null;

    document.addEventListener("DOMContentLoaded", () => {
        init_select2('#device_id', 'device', {user: userId, access: 'inverted'}, null, 'Select Device');
        init_select2('#device', 'device', {user: userId}, null, 'Select Device');
        init_select2('#port_id', 'port', function (params) {
            return {
                term: params.term,
                page: params.page || 1,
                device: window.port_device_id
            };
        });
        init_select2('#device_group_id', 'device-group', allowDynamic ? {} : {type: 'static'}, null, 'Select Group');
        init_select2('#bill_id', 'bill', {}, null, 'Select Bill');
    });
</script>

@endsection
