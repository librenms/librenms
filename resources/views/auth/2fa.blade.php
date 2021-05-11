@extends('layouts.librenmsv1')

@section('javascript')
    <script src="{{ asset('js/jquery-qrcode.min.js') }}"></script>
@endsection

@section('content')
<div class="container">
<div class="row">
    <div class="col-md-6 col-md-offset-3">
        <x-panel>
            <x-slot name="title">
                <img class="logon-logo img-responsive" src="{{ asset(\LibreNMS\Config::get('title_image', 'images/librenms_logo_light.svg')) }}">
            </x-slot>

            <div class="container-fluid">
                @if(session('twofactoradd'))
                <div class="row">
                    <div id="twofactorqrcontainer" class="col-md-12">
                        <h4>Scan with your Two Factor Authenticator.</h4>
                        <div class="col-md-12 text-center" id="twofactorqr"></div>
                        <div class="col-md-12 text-center">
                            <button class="btn btn-default" onclick="$('#twofactorqrcontainer').hide(); $('#twofactorkeycontainer').show();">Manual</button>
                        </div>
                    </div>

                    <div id="twofactorkeycontainer" style="display: none" class="col-md-12">
                        <div class="col-sm-12">
                            <h4 style="user-select: none;">Secret Key:</h4>
                        </div>
                        <div class="col-sm-12 text-center" style="padding: 32px 0; font-size: medium;">
                            {{ $key }}
                        </div>
                        <div class="col-sm-12 text-center">
                            <button class="btn btn-default" onclick="$('#twofactorkeycontainer').hide(); $('#twofactorqrcontainer').show();">QR</button>
                        </div>
                    </div>
                    <script>$("#twofactorqr").qrcode({"text": "{!! $uri !!}"});</script>
                </div>
                @endif
                <div class="row">
                    <div class="col-md-12">
                        <form class="form-horizontal" role="form" action="{{ route('2fa.verify') }}" method="post" name="twofactorform">
                            {{ csrf_field() }}
                            <div class="form-group">
                                <div class="col-md-8 col-md-offset-2">
                                    <div id="twoFactorErrors" class="help-block">
                                        @foreach($errors->all() as $error)
                                            <strong>{{ $error }}</strong>
                                        @endforeach
                                    </div>
                                    @if(!$errors->has('lockout'))
                                    <input type="text"
                                           name="twofactor"
                                           id="twofactor"
                                           class="form-control"
                                           autocomplete="off"
                                           aria-describedby="twoFactorErrors"
                                           placeholder="@lang('Please enter auth token')"
                                           required autofocus>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group">
                                @if(!$errors->has('lockout'))
                                    <div class="col-md-12" style="margin:8px">
                                        <button type="submit" class="btn btn-primary btn-block" name="submit">
                                            <i class="fa fa-btn fa-sign-in"></i> @lang('Submit')
                                        </button>
                                    </div>
                                @endif
                                @if(!$errors->isEmpty())
                                    <div class="col-md-12" style="margin:8px">
                                        <button type="submit" class="btn btn-default btn-block" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                            <i class="fa fa-btn fa-sign-out" aria-hidden="true"></i>
                                            @lang('Logout')
                                        </button>
                                    </div>
                                @endif
                                @if(session('twofactoradd'))
                                    <div class="col-md-12" style="margin:8px">
                                        <button type="submit" class="btn btn-danger btn-block" onclick="event.preventDefault(); document.getElementById('cancel-form').submit();">
                                            <i class="fa fa-btn fa-sign-out" aria-hidden="true"></i>
                                            @lang('Cancel')
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    {{ csrf_field() }}
                </form>
                <form id="cancel-form" action="{{ route('2fa.cancel') }}" method="POST" style="display: none;">
                    {{ csrf_field() }}
                </form>
           </div>
        </x-panel>
</div>
</div>
@endsection
