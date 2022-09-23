@extends('layouts.librenmsv1')

@section('title', __('Edit User'))

@section('content')
<div class="container">
    <div class="row">
        <form action="{{ route('users.update', $user->user_id)}}" method="POST" role="form"
              class="form-horizontal col-md-8 col-md-offset-2">
            <legend>{{ __('Edit User') }}: {{ $user->username }}</legend>
            {{ method_field('PUT') }}
            @csrf

            @include('user.form')

            @config('twofactor')
            <br/>
            <x-panel title="{{ __('Two-Factor Authentication') }}" class="col-sm-offset-3">
                @if($twofactor_enabled)
                    @if($twofactor_locked)
                        <div class="form-group" id="twofactor-unlock-form">
                            <button type="button" id="twofactor-unlock" class="btn btn-default col-sm-4 col-sm-offset-1">{{ __('Unlock') }}</button>
                            <label for="twofactor-unlock" class="col-sm-7 control-label">{{ __('User exceeded failures') }}</label>
                        </div>
                    @endif
                    <div class="form-group">
                        <button type="button" id="twofactor-disable" class="btn btn-danger col-sm-offset-1">{{ __('Disable TwoFactor') }}</button>
                    </div>
                @else
                    <p>{{ __('No TwoFactor key generated for this user, Nothing to do.') }}</p>
                @endif
            </x-panel>
            @endconfig

            <div class="form-group">
                <div class="col-sm-9 col-sm-offset-3">
                    <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                    <a type="button" class="btn btn-danger" href="{{ route('users.index') }}">{{ __('Cancel') }}</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('javascript')
    <script type="application/javascript">
        $(document).ready(function () {
            $('#twofactor-unlock').on("click", function () {
                console.log('unlock');
                $.ajax({
                    type: 'POST',
                    url: '{{ route('2fa.unlock', ['user' => $user->user_id]) }}',
                    dataType: "json",
                    success: function(data){
                        if (data.status === 'ok') {
                            $('#twofactor-unlock-form').remove();
                            toastr.success('{{ __('Unlocked Two Factor.') }}');
                        } else {
                            toastr.error('{{ __('Failed to unlock Two Factor') }}<br />' + data.message);
                        }
                    },
                    error: function(){
                        toastr.error('{{ __('Failed to unlock Two Factor') }}');
                    }
                });
            });

            $('#twofactor-disable').on("click", function () {
                $.ajax({
                    type: 'DELETE',
                    url: '{{ route('2fa.delete', ['user' => $user->user_id]) }}',
                    dataType: "json",
                    success: function(data){
                        if (data.status === 'ok') {
                            toastr.success('{{ __('Removed Two Factor.') }}');
                        } else {
                            toastr.error('{{ __('Failed to remove Two Factor') }}<br />' + data.message);
                        }
                    },
                    error: function(){
                        toastr.error('{{ __('Failed to remove Two Factor') }}');
                    }
                });
            });
        });
    </script>
@endsection

