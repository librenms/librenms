@extends('layouts.librenmsv1')

@section('content')
    <div class="container-fluid">
        <div class="container">
            <div id="app" v-cloak>
                <tabs>
                    <template v-slot:header>
                        <div>Banana</div>
                    </template>
                    <tab name="One" selected icon="fa-ambulance" selected>
                        <accordion :multiple="false">
                            <accordion-item name="Test" icon="fa-wrench" active>
                                Test Content
                            </accordion-item>
                            <accordion-item name="Test 2" icon="fa-archive">
                                Test 2 Content
                            </accordion-item>
                            <accordion-item name="Test 3" icon="fa-android">
                                Test 3 Content
                            </accordion-item>
                        </accordion>
                    </tab>
                    <tab name="Dave" icon="fa-bomb">
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
        [v-cloak] > * { display:none; }
        [v-cloak]::before {
            content: " ";
            display: block;
            width: 16px;
            height: 16px;
            background-image: url('data:image/gif;base64,R0lGODlhEAAQAPIAAP///wAAAMLCwkJCQgAAAGJiYoKCgpKSkiH/C05FVFNDQVBFMi4wAwEAAAAh/hpDcmVhdGVkIHdpdGggYWpheGxvYWQuaW5mbwAh+QQJCgAAACwAAAAAEAAQAAADMwi63P4wyklrE2MIOggZnAdOmGYJRbExwroUmcG2LmDEwnHQLVsYOd2mBzkYDAdKa+dIAAAh+QQJCgAAACwAAAAAEAAQAAADNAi63P5OjCEgG4QMu7DmikRxQlFUYDEZIGBMRVsaqHwctXXf7WEYB4Ag1xjihkMZsiUkKhIAIfkECQoAAAAsAAAAABAAEAAAAzYIujIjK8pByJDMlFYvBoVjHA70GU7xSUJhmKtwHPAKzLO9HMaoKwJZ7Rf8AYPDDzKpZBqfvwQAIfkECQoAAAAsAAAAABAAEAAAAzMIumIlK8oyhpHsnFZfhYumCYUhDAQxRIdhHBGqRoKw0R8DYlJd8z0fMDgsGo/IpHI5TAAAIfkECQoAAAAsAAAAABAAEAAAAzIIunInK0rnZBTwGPNMgQwmdsNgXGJUlIWEuR5oWUIpz8pAEAMe6TwfwyYsGo/IpFKSAAAh+QQJCgAAACwAAAAAEAAQAAADMwi6IMKQORfjdOe82p4wGccc4CEuQradylesojEMBgsUc2G7sDX3lQGBMLAJibufbSlKAAAh+QQJCgAAACwAAAAAEAAQAAADMgi63P7wCRHZnFVdmgHu2nFwlWCI3WGc3TSWhUFGxTAUkGCbtgENBMJAEJsxgMLWzpEAACH5BAkKAAAALAAAAAAQABAAAAMyCLrc/jDKSatlQtScKdceCAjDII7HcQ4EMTCpyrCuUBjCYRgHVtqlAiB1YhiCnlsRkAAAOwAAAAAAAAAAAA==');
        }
    </style>
@endsection

@push('scripts')
    @routes
    <script src="{{ mix('js/app.js') }}"></script>
@endpush
