@extends('layouts.librenmsv1')

@section('title', __('Preferences'))

@section('content')
<div class="container">
    <row>
        <legend>@lang('User Preferences')</legend>
    </row>

    @if($can_change_password)
    <div class="panel panel-default panel-condensed">
        <div class="panel-heading">@lang('Change Password')</div>
        <div class="panel-body">
            <form method="post" action="preferences/" class="form-horizontal" role="form">
                <input type=hidden name="action" value="changepass">
                <div class="form-group">
                    <label for="old_pass" class="col-sm-2 control-label">@lang('Current Password')</label>
                    <div class="col-sm-4">
                        <input type="password" name="old_pass" autocomplete="off" class="form-control input-sm">
                    </div>
                </div>
                <div class="form-group">
                    <label for="new_pass" class="col-sm-2 control-label">@lang('New Password')</label>
                    <div class="col-sm-4">
                        <input type="password" name="new_pass" autocomplete="off" class="form-control input-sm">
                    </div>
                </div>
                <div class="form-group">
                    <label for="new_pass2" class="col-sm-2 control-label">@lang('New Password')</label>
                    <div class="col-sm-4">
                        <input type="password" name="new_pass2" autocomplete="off" class="form-control input-sm">
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-4 col-sm-offset-2">
                        <button type="submit" class="btn btn-default">@lang('Change Password')</button>
                    </div>
                </div>

            </form>
        </div>
    </div>
    @endif

    @config('twofactor')
    <div class="panel panel-default panel-condensed">
        <div class="panel-heading">@lang('Two-Factor Authentication')</div>
        <div class="panel-body">
        @if($twofactor)
            <div id="twofactorqrcontainer">
                <div id="twofactorqr"></div>
                <script>$("#twofactorqr").qrcode({"text": "{{ $twofactor_uri }}"});</script>
                <button class="btn btn-default" onclick="$('#twofactorkeycontainer').show(); $('#twofactorqrcontainer').hide();">@lang('Manual')</button>
            </div>
            <div id="twofactorkeycontainer">
                <form id="twofactorkey" class="form-horizontal" role="form">
                    <div class="form-group">
                        <label for="twofactorkey" class="col-sm-2 control-label">@lang('Secret Key')</label>
                        <div class="col-sm-4">
                            <input type="text" name="twofactorkey" autocomplete="off" disabled class="form-control input-sm" value="{{ $twofactor['key'] }}" />
                        </div>
                    </div>
                    @if($twofactor['counter'] !== false)
                    <div class="form-group">
                        <label for="twofactorcounter" class="col-sm-2 control-label">@lang('Counter')</label>
                        <div class="col-sm-4">
                            <input type="text" name="twofactorcounter" autocomplete="off" disabled class="form-control input-sm" value="{{ $twofactor['counter'] }}" />
                        </div>
                    </div>
                    @endif
                </form>
                <button class="btn btn-default" onclick="$('#twofactorkeycontainer').hide(); $('#twofactorqrcontainer').show();">@lang('QR')</button>
            </div>
                <br/>
                <form method="post" class="form-horizontal" role="form" action="{{ route('2fa.remove') }}">
                    <button class="btn btn-danger" type="submit">@lang('Disable TwoFactor')</button>
                </form>
        @else
            <form method="post" class="form-horizontal" role="form" action="{{ route('2fa.add') }}">
                <div class="form-group">
                    <label for="twofactortype" class="col-sm-2 control-label">@lang('TwoFactor Type')</label>
                    <div class="col-sm-4">
                        <select name="twofactortype" class="select form-control">
                            <option value="time">@lang('Time Based (TOTP)')</option>
                            <option value="counter">@lang('Counter Based (HOTP)')</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-4 col-sm-offset-2">
                        <button class="btn btn-default" type="submit" id="twofactor-generate">@lang('Generate TwoFactor Secret Key')</button>
                    </div>
                </div>
            </form>
        @endif
        </div>
    </div>
    @endconfig

    <div class="panel panel-default panel-condensed">
        <div class="panel-heading">@lang('Default Dashboard')</div>
        <div class="panel-body">
            <form method="post" action="preferences/" class="form-horizontal" role="form">
                <div class="form-group">
                    <input type=hidden name="action" value="changedash">
                    <div class="form-group">
                        <label for="dashboard" class="col-sm-2 control-label">Dashboard</label>
                        <div class="col-sm-4">
                            <select class="form-control" name="dashboard">
                                <option value="1">murrant:Default</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-4 col-sm-offset-2">
                            <button type="submit" class="btn btn-default">Update Dashboard</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="panel panel-default panel-condensed">
        <div class="panel-heading">@lang('Add schedule notes to devices notes')</div>
        <div class="panel-body">
            <form method="post" action="preferences/" class="form-horizontal" role="form">
                <div class="form-group">
                    <input type=hidden name="action" value="changenote">
                    <div class="form-group">
                        <label for="dashboard" class="col-sm-3 control-label">Add schedule notes to
                            devices notes</label>
                        <div class="col-sm-4">
                            <input id="notetodevice" type="checkbox" name="notetodevice"
                                   data-size="small">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-4 col-sm-offset-3">
                            <button type="submit" class="btn btn-default">Update preferences</button>
                        </div>
                        <div class="col-sm-6"></div>
                    </div>
                </div>
            </form>
        </div>
    </div>


    <div class="panel panel-default panel-condensed">
        <div class="panel-heading">@lang('Device Permissions')</div>
        <div class="panel-body">
            <strong class="blue">Global Administrative Access</strong>
        </div>
    </div>
</div>
@endsection

@section('javascript')
    <script src="{{ asset('js/jquery.qrcode.min.js') }}"></script>
    <script>
        $(document).ready( function() {
            $("[name='notetodevice']").bootstrapSwitch('offColor', 'danger');
        });
    </script>
@endsection

@section('css')
    <style>
        #twofactorkeycontainer { display: none; }
    </style>
@endsection
