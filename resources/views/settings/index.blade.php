@extends('layouts.librenmsv1')

@section('title', __('Settings'))

@section('content')
    <div class="panel with-nav-tabs panel-default">
        <div class="panel-heading">
            <ul class="nav nav-tabs">
                @foreach($tabs as $tab)
                    <li @if($tab == $active_tab)class="active"@endif><a href="#tab-{{ $tab }}"
                                                                        data-toggle="tab">@lang("settings.tabs.$tab")</a>
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
                                                <a data-toggle="collapse" data-parent="#accordion-{{ $group }}" href="#{{ "$group-$section" }}">
                                                    <i class="fa fa-caret-down"></i> @lang("settings.sections.$group.$section")
                                                </a>
                                            </h4>
                                        </div>
                                        <div id="{{ "$group-$section" }}" class="panel-collapse collapse @if($group == $active_tab && $section == $active_section) in @endif">
                                            <div class="panel-body">
                                                <form class="form-horizontal" role="form">
                                                @foreach($configs as $config)
                                                    @if (!$config->isHidden() && $config->getType())
                                                        @include('settings.types.' . $config->getType(), $config->toArray())
                                                    @endif
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

@section('javascript')
    <script>
        $( document ).ready(function() {
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

            // Checkbox config ajax calls
            $(".global-config-check").bootstrapSwitch('offColor', 'danger');
            $('input.global-config-check').on('switchChange.bootstrapSwitch', function (event, state) {
                event.preventDefault();
                var $this = $(this);
                var config_id = $this.data("config_id");
                $.ajax({
                    type: 'POST',
                    url: 'ajax_form.php',
                    data: {type: "update-config-item", config_id: config_id, config_value: state},
                    dataType: "json",
                    success: function (data) {
                        if (data.status == 'ok') {
                            toastr.success('Config updated');
                        } else {
                            toastr.error(data.message);
                        }
                    },
                    error: function () {
                        toastr.error(data.message);
                    }
                });
            });

            $('select').change(function (event) {
                event.preventDefault();
                var $this = $(this);
                var config_id = $this.data("config_id");
                var config_value = $this.val();
                $.ajax({
                    type: 'POST',
                    url: 'ajax_form.php',
                    data: {type: "update-config-item", config_id: config_id, config_value: config_value},
                    dataType: "json",
                    success: function (data) {
                        if (data.status == 'ok') {
                            $this.closest('.form-group').addClass('has-success');
                            $this.next().addClass('fa-check');
                            setTimeout(function () {
                                $this.closest('.form-group').removeClass('has-success');
                                $this.next().removeClass('fa-check');
                            }, 2000);
                        } else {
                            $(this).closest('.form-group').addClass('has-error');
                            $this.next().addClass('fa-times');
                            setTimeout(function () {
                                $this.closest('.form-group').removeClass('has-error');
                                $this.next().removeClass('fa-times');
                            }, 2000);
                        }
                    },
                    error: function () {
                        $("#message").html('<div class="alert alert-danger">An error occurred.</div>');
                    }
                });
            });
        });
    </script>
@endsection

@section('css')
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
@endsection
