@extends('layouts.librenmsv1')

@section('content')
<div class="lnms-login">
    <div class="lnms-login__inner">
        @include('auth.login-form')
    </div>
    @if($errors->any())
        <script>toastr.error('{{ $errors->first() }}')</script>
    @endif
</div>
@endsection
