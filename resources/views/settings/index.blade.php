@extends('layouts.librenmsv1')

@section('title', __('Settings'))

@section('content')
    <div class="panel with-nav-tabs panel-default">
        <div class="panel-heading">
            <ul class="nav nav-tabs">
                @foreach($tabs as $tab)
                    <li @if($tab == $active_tab)class="active"@endif>
                        <a href="#tab-{{ $tab }}" data-toggle="tab"
                           onclick="window.history.pushState('{{ $tab }}', '', '/settings/{{ $tab }}');">@lang("settings.tabs.$tab")</a>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="panel-body">
            <div class="tab-content">
                @foreach($groups as $group => $sections)
                    <div class="tab-pane fade @if($group == $active_tab)in active @endif" id="tab-{{ $group }}">
                        <div class="panel-group" id="accordion-{{ $group }}">
                            @foreach($sections as $section => $configs)
                                @if($group == 'global')
                                    @foreach($configs as $config)
                                        <b>{{ $config->getName() }}</b>
                                        = {{ stripslashes(json_encode($config->getValue())) }} <br/>
                                    @endforeach
                                @else
                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <h4 class="panel-title">
                                                <a data-toggle="collapse" data-parent="#accordion-{{ $group }}"
                                                   href="#{{ "$group-$section" }}"
                                                   onclick="window.history.pushState('{{ $group }}/{{ $section }}', '', '/settings/{{ $group }}/{{ $section }}');">
                                                    <i class="fa fa-caret-down"></i> @lang("settings.sections.$group.$section")
                                                </a>
                                            </h4>
                                        </div>
                                        <div id="{{ "$group-$section" }}"
                                             class="panel-collapse collapse @if($group == $active_tab && $section == $active_section) in @endif">
                                            <div class="panel-body">
                                                <form class="form-horizontal section-form" role="form">
                                                    @foreach($configs as $config)
                                                        @include('settings.types.' . $config->getType(), $config->toArray())
                                                    @endforeach
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(".toolTip").tooltip();

        $('#email_backend').change(function () {
            var type = this.value;
            if (type === 'sendmail') {
                $('.smtp-form').hide();
                $('.sendmail-form').show();
            } else if (type === 'smtp') {
                $('.sendmail-form').hide();
                $('.smtp-form').show();
            } else {
                $('.smtp-form').hide();
                $('.sendmail-form').hide();
            }
        }).change(); // trigger initially

        $('#geoloc\\.engine').change(function () {
            var engine = this.value;
            if (engine === 'openstreetmap') {
                $('.geoloc_api_key').hide();
            } else {
                $('.geoloc_api_key').show();
            }
        }).change(); // trigger initially

        // Checkbox config ajax calls
        $('.section-form input[type=checkbox]').on('switchChange.bootstrapSwitch', function (event, state) {
            event.preventDefault();
            var $this = $(this);
            globalConfigUpdateValue($this, state);
        }).bootstrapSwitch('offColor', 'danger');

        // Input field config ajax calls
        $('.section-form input:not([type=checkbox])').on('blur keyup', function (event) {
            if (event.type === 'keyup' && event.keyCode !== 13) {
                return;
            }
            event.preventDefault();
            var $this = $(this);
            var value = $this.val();

            globalConfigUpdateValue($this, value);
        });

        $('.section-form select').change(function (event) {
            event.preventDefault();
            var $this = $(this);
            var value = $this.val();
            globalConfigUpdateValue($this, value);
        });

        function globalConfigUpdateValue(target, value) {
            var name = target.attr('name');
            var original = target.data('original');
            if (original != value && target[0].checkValidity()) {
                $.ajax({
                    type: 'PUT',
                    url: '{{ route('settings.update', ['']) }}/' + name,
                    data: {value: value},
                    target: target,
                    dataType: "json",
                    success: function (data) {
                        globalConfigUpdateSuccess(this.target, data);
                    },
                    error: function (data) {
                        globalConfigUpdateFailure(this.target, data.responseJSON);
                    }
                });
            }
        }

        function globalConfigUpdateSuccess(target, data) {
            target.data('original', target.val());
            target.closest('.form-group').addClass('has-success');
            target.next().addClass('fa-check');
            setTimeout(function () {
                target.closest('.form-group').removeClass('has-success');
                target.next().removeClass('fa-check');
            }, 2000);
            console.log(data.message);
            toastr.success(data.message || 'Config ' + target.attr('name') + ' updated');
        }

        function globalConfigUpdateFailure(target, data) {
            if (target.is(':checkbox')) {
                console.log(target.data('original'));
                target.bootstrapSwitch('state', target.data('original'));
            } else {
                target.val(target.data('original')).change();
            }
            target.closest('.form-group').addClass('has-error');
            target.next().addClass('fa-times');
            setTimeout(function () {
                target.closest('.form-group').removeClass('has-error');
                target.next().removeClass('fa-times');
            }, 2000);
            toastr.error(data.message || 'Error occurred updating ' + target.attr('name'));
        }
    </script>
@endpush

@push('styles')
    <style>
        /* format tabs */
        .panel.with-nav-tabs > .panel-heading {
            padding: 5px 5px 0 5px;
        }

        .panel.with-nav-tabs > .panel-heading > .nav-tabs {
            border-bottom: none;
        }

        .panel.with-nav-tabs {
            margin-bottom: -1px;
        }

        /* colorize tab links */
        .with-nav-tabs.panel-default .nav-tabs > li > a,
        .with-nav-tabs.panel-default .nav-tabs > li > a:hover,
        .with-nav-tabs.panel-default .nav-tabs > li > a:focus {
            color: #777;
        }

        .with-nav-tabs.panel-default .nav-tabs > .open > a,
        .with-nav-tabs.panel-default .nav-tabs > .open > a:hover,
        .with-nav-tabs.panel-default .nav-tabs > .open > a:focus,
        .with-nav-tabs.panel-default .nav-tabs > li > a:hover,
        .with-nav-tabs.panel-default .nav-tabs > li > a:focus {
            color: #777;
            background-color: #ddd;
            border-color: transparent;
        }

        .with-nav-tabs.panel-default .nav-tabs > li.active > a,
        .with-nav-tabs.panel-default .nav-tabs > li.active > a:hover,
        .with-nav-tabs.panel-default .nav-tabs > li.active > a:focus {
            color: #555;
            background-color: #fff;
            border-color: #ddd;
            border-bottom-color: transparent;
        }
    </style>
@endpush
