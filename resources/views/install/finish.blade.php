@extends('layouts.install')

@section('content')
<div class="card">
  <div class="card-header">
    .env file
  </div>
  <div class="card-body">
    <h4 class="card-title">Written</h4>
      <p class="card-text"><textarea disabled>{{ $env }}</textarea></p>
    <a href="#" class="btn btn-primary">button</a>
  </div>
</div>
@endsection
