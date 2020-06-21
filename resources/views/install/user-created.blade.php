@extends('layouts.install')

@section('content')
    <div class="row">
        <div class="col-12 text-center p-5">
            <h4>
                <i class="fa fa-2x fa-user-circle align-middle"></i>
                <strong>{{ $user->username }}</strong>
            </h4>
        </div>
    </div>
@endsection
