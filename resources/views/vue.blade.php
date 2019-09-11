@extends('layouts.librenmsv1')

@section('content')
    <div class="container-fluid">
        <div class="container">
            <div id="app" v-cloak>
                <tabs>
                    <tab name="One" selected>
                        <accordion :multiple="false">
                            <accordion-item name="Test">
                                Test Content
                            </accordion-item>
                            <accordion-item name="Test 2">
                                Test 2 Content
                            </accordion-item>
                            <accordion-item name="Test 3">
                                Test 3 Content
                            </accordion-item>
                        </accordion>
                    </tab>
                    <tab name="Dave">
                        Dave's not here man
                    </tab>
                </tabs>
            </div>
        </div>
        <hr />

    </div>
@endsection

@section('css')
    <style>
        [v-cloak] {display: none}
    </style>
@endsection

@push('scripts')
    @routes
    <script src="{{ asset('js/app.js') }}"></script>
@endpush
