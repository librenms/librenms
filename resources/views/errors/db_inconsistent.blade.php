@extends('layouts.error')

@section('title')
    @lang('exceptions.database_inconsistent.title')
@endsection

@section('content')
    <h3>@lang('exceptions.database_inconsistent.header')</h3>

    <div class="message-block">
        @foreach($results as $result)
            <p>
                <h2>{{ $result->getMessage() }}</h2>
                @if($result->hasFix())
                    <div style="margin-left: 3em; font-size: 18px">{{ $result->getFix() }}</div>
                @endif
            </p>
            @endforeach
    </div>
@endsection
