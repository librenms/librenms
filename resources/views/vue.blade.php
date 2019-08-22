@extends('layouts.librenmsv1')

@section('content')
    <div class="container-fluid">
        <div class="container">
            <div id="app">
                <accordion>
                    <accordion-item name="Test">
                        Test Content
                    </accordion-item>
                    <accordion-item name="Test 2">
                        Test 2 Content
                    </accordion-item>
                </accordion>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    @routes
    <script src="{{ asset('js/app.js') }}"></script>
@endpush
