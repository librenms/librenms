@extends('layouts.install')

@section('content')
    <div class="row">
        <div class="col-12 text-center p-5">
            <h4>
                <i class="fa-solid fa-2x fa-circle-user align-middle"></i>
                <strong>{{ $user->username }}</strong>
            </h4>
        </div>
    </div>
@endsection
