@extends('layouts.librenmsv1')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            @include('auth.login-form')
        </div>
    </div>
    @if($errors->any())
        <script>toastr.error('{{ $errors->first() }}')</script>
    @endif
</div>
@endsection
