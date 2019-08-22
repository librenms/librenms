@extends('layouts.librenmsv1')

@section('title', __('Preferences'))

@section('content')
<div class="container">
    <row>
        <legend>@lang('User Preferences')</legend>
    </row>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($can_change_password)
    <div class="panel panel-default panel-condensed">
        <div class="panel-heading">@lang('Change Password')</div>
        <div class="panel-body">
            <form method="POST" action="{{ route('users.update', [$user->user_id]) }}" class="form-horizontal" role="form">
                <input type="hidden" name="_method" value="PATCH">
                @csrf
                <div class="form-group">
                    <label for="old_password" class="col-sm-4 control-label">@lang('Current Password')</label>
                    <div class="col-sm-4">
                        <input type="password" name="old_password" autocomplete="off" class="form-control input-sm">
                    </div>
                </div>
                <div class="form-group">
                    <label for="new_password" class="col-sm-4 control-label">@lang('New Password')</label>
                    <div class="col-sm-4">
                        <input type="password" name="new_password" autocomplete="off" class="form-control input-sm">
                    </div>
                </div>
                <div class="form-group">
                    <label for="new_password_confirmation" class="col-sm-4 control-label">@lang('Verify New Password')</label>
                    <div class="col-sm-4">
                        <input type="password" name="new_password_confirmation" autocomplete="off" class="form-control input-sm">
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-4 col-sm-offset-3">
                        <button type="submit" class="btn btn-default">@lang('Change Password')</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif

    <div class="panel panel-default panel-condensed">
        <div class="panel-heading">@lang('Preferences')</div>
        <div class="panel-body">
            <form class="form-horizontal" role="form">
                @csrf
                <div class="form-group">
                    <label for="dashboard" class="col-sm-4 control-label">@lang('Dashboard')</label>
                    <div class="col-sm-4">
                        <select class="form-control ajax-select" name="dashboard" data-pref="dashboard" data-previous="{{ $default_dashboard }}">
                            @foreach($dashboards as $dash)
                                <option value="{{ $dash->dashboard_id }}" @if($dash->dashboard_id == $default_dashboard) selected @endif>{{ $dash->user ? $dash->user->username : __('<deleted>') }}:{{ $dash->dashboard_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="locale" class="col-sm-4 control-label">@lang('Language')</label>
                    <div class="col-sm-4">
                        <select class="form-control ajax-select" name="locale" data-pref="locale" data-previous="{{ $locale }}">
                            @foreach($locales as $lang => $descr)
                                <option value="{{ $lang }}" @if($lang == $locale) selected @endif>{{ $descr }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-4">
                        <small>* @lang('Translation not fully supported')</small>
                    </div>
                </div>
                <div class="form-group">
                    <label for="notetodevice" class="col-sm-4 control-label">@lang('Add schedule notes to devices notes')</label>
                    <div class="col-sm-4">
                        <input id="notetodevice" type="checkbox" name="notetodevice" @if($note_to_device) checked @endif>
                    </div>
                </div>
            </form>
        </div>
    </div>

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
                    @csrf
                    <div class="form-group">
                        <label for="twofactorkey" class="col-sm-4 control-label">@lang('Secret Key')</label>
                        <div class="col-sm-4">
                            <input type="text" name="twofactorkey" autocomplete="off" disabled class="form-control input-sm" value="{{ $twofactor['key'] }}" />
                        </div>
                    </div>
                    @if($twofactor['counter'] !== false)
                    <div class="form-group">
                        <label for="twofactorcounter" class="col-sm-4 control-label">@lang('Counter')</label>
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
                    @csrf
                    <button class="btn btn-danger" type="submit">@lang('Disable TwoFactor')</button>
                </form>
        @else
            <form method="post" class="form-horizontal" role="form" action="{{ route('2fa.add') }}">
                @csrf
                <div class="form-group">
                    <label for="twofactortype" class="col-sm-4 control-label">@lang('TwoFactor Type')</label>
                    <div class="col-sm-4">
                        <select name="twofactortype" class="select form-control">
                            <option value="time">@lang('Time Based (TOTP)')</option>
                            <option value="counter">@lang('Counter Based (HOTP)')</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-sm-4 col-sm-offset-3">
                        <button class="btn btn-default" type="submit" id="twofactor-generate">@lang('Generate TwoFactor Secret Key')</button>
                    </div>
                </div>
            </form>
        @endif
        </div>
    </div>
    @endconfig

    <div class="panel panel-default panel-condensed">
        <div class="panel-heading">@lang('Device Permissions')</div>
        <div class="panel-body">
            @if(auth()->user()->hasGlobalAdmin())
                <strong class="blue">@lang('Global Administrative Access')</strong>
            @elseif(auth()->user()->hasGlobalRead())
                <strong class="green">@lang('Global Viewing Access')</strong>
            @else
                @forelse($devices as $device)
                    {!! \LibreNMS\Util\Url::deviceLink($device) !!} <br />
                @empty
                    <strong class="red">@lang('No access!')</strong>
                @endforelse
            @endif
        </div>
    </div>
</div>
@endsection

@section('javascript')
    <script src="{{ asset('js/jquery.qrcode.min.js') }}"></script>
@endsection

@section('scripts')
    <script>
        $("[name='notetodevice']")
            .bootstrapSwitch('offColor', 'danger')
            .on('switchChange.bootstrapSwitch', function (e, state) {
                var $this = $(this);
                $.ajax({
                    url: '{{ route('preferences.store') }}',
                    dataType: 'json',
                    type: 'POST',
                    data: {
                        pref: 'add_schedule_note_to_device',
                        value: state ? 1 : 0
                    },
                    success: function () {
                        $this.closest('.form-group').addClass('has-success');
                        setTimeout(function () {
                            $this.closest('.form-group').removeClass('has-success');
                        }, 2000);
                    },
                    error: function () {
                        $this.bootstrapSwitch('toggleState', true);
                        $this.closest('.form-group').addClass('has-error');
                        setTimeout(function(){
                            $this.closest('.form-group').removeClass('has-error');
                        }, 2000);
                    }
                });
            });

        $('.ajax-select').change(function () {
            var $this = $(this);
            var value = $this.val();
            var pref = $this.data('pref');
            $.ajax({
                url: '{{ route('preferences.store') }}',
                dataType: 'json',
                type: 'POST',
                data: {
                    pref: pref,
                    value: value
                },
                success: function () {
                    if (pref === 'locale') {
                        location.reload();
                    }

                    $this.data('previous', value);
                    $this.closest('.form-group').addClass('has-success');
                    setTimeout(function () {
                        $this.closest('.form-group').removeClass('has-success');
                    }, 2000);
                },
                error: function (data) {
                    $this.val($this.data('previous'));
                    $this.closest('.form-group').addClass('has-error');

                    var json = data.responseJSON;
                    var errors = [];
                    for (var attrib in json) {
                        errors.push(json[attrib]);
                    }
                    toastr.error('Error: ' + errors.join("<br />"));

                    setTimeout(function(){
                        $this.closest('.form-group').removeClass('has-error');
                    }, 2000);
                }
            });
        });
    </script>
@endsection

@section('css')
    <style>
        #twofactorkeycontainer { display: none; }
    </style>
@endsection
