@extends('layouts.librenmsv1')

@section('title', __('Overview'))

@section('content')
<div class="container-fluid">
@include('alerts.modals.ack')
@include('alerts.modals.notes')
@if (!$bare)
<div class="row collapse @if(!$hide_dashboard_editor)in @endif" id="dashboard-editor">
    <div class="col-md-12">
        <div class="btn-group btn-lg">
            <button class="btn btn-default disabled" style="min-width:160px;"><span class="pull-left">Dashboards</span></button>
            <div class="btn-group">
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="min-width:160px;">
                    <span class="pull-left">{{ $dashboard->user_id != Auth::id() ? ($dashboard->user->username ?? __('Deleted User')) . ':' : null}} {{ $dashboard->dashboard_name }}</span>
                <span class="pull-right">
                <span class="caret"></span>
                <span class="sr-only">Toggle Dropdown</span>
                </span>
                </button>
                <ul class="dropdown-menu">
                    @forelse ($user_dashboards as $dash)
                        @if($dash->dashboard_id != $dashboard->dashboard_id)
                        <li>
                            <a href="{{ url("?dashboard=$dash->dashboard_id") }}">{{ $dash->dashboard_name }}</a>
                        </li>
                        @endif
                    @empty
                        <li><a>No other Dashboards</a></li>
                    @endforelse

                    @isset($shared_dashboards)
                        <li role="separator" class="divider"></li>
                        <li class="dropdown-header">Shared Dashboards</li>
                        @foreach ($shared_dashboards as $dash)
                            @if($dash->dashboard_id != $dashboard->dashboard_id)
                            <li>
                                <a href="{{ url("?dashboard=$dash->dashboard_id") }}">
                                {{ ($dash->user->username ?? __('Deleted User')) . ':' . $dash->dashboard_name . ($dash->access == 1 ? ' (Read)' : '') }}</a>
                            </li>
                            @endif
                        @endforeach
                    @endisset
                </ul>
            </div>
            <button class="btn btn-default edit-dash-btn" href="#edit_dash" onclick="dashboard_collapse($(this).attr('href'))" data-toggle="tooltip" data-container="body" data-placement="top" title="Edit Dashboard"><i class="fa fa-pencil-square-o fa-fw"></i></button>
            <button class="btn btn-danger" href="#del_dash" onclick="dashboard_collapse($(this).attr('href'))" data-toggle="tooltip" data-container="body" data-placement="top" title="Remove Dashboard"><i class="fa fa-trash fa-fw"></i></button>
            <button class="btn btn-success" href="#add_dash" onclick="dashboard_collapse($(this).attr('href'))" data-toggle="tooltip" data-container="body" data-placement="top" title="New Dashboard"><i class="fa fa-plus fa-fw"></i></button>
        </div>
        <div class="dash-collapse" id="add_dash" style="display: none;" >
            <div class="row" style="margin-top:5px;">
                <div class="col-md-6">
                    <form class="form-inline" onsubmit="dashboard_add(this); return false;" name="add_form" id="add_form">
                        @csrf
                        <div class="col-sm-3 col-sx-6">
                            <div class="input-group">
                                <span class="input-group-btn">
                                    <a class="btn btn-default disabled" type="button" style="min-width:160px;"><span class="pull-left">New Dashboard</span></a>
                                </span>
                                <input class="form-control" type="text" placeholder="Name" name="dashboard_name" id="dashboard_name" style="min-width:160px;">
                                <span class="input-group-btn">
                                    <button class="btn btn-primary" type="submit">Add</button>
                                </span>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <hr>
        </div>
        <div class="dash-collapse" id="edit_dash" style="display: none;">
            <!-- Start Dashboard-Settings -->
            <div class="row" style="margin-top:5px;">
                <div class="col-md-12">
                    <div class="col-md-12">
                        <form class="form-inline" onsubmit="dashboard_edit(this); return false;">
                            @csrf
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-btn">
                                        <a class="btn btn-default disabled" type="button" style="min-width:160px;"><span class="pull-left">Dashboard Name</span></a>
                                    </span>
                                    <input class="form-control" type="text" placeholder="Dashbord Name" name="dashboard_name" value="{{ $dashboard->dashboard_name }}" style="width:160px;">
                                    <select class="form-control" name="access" style="width:160px;">
                                    @foreach (array('Private','Shared (Read)','Shared') as $k => $v)
                                        <option value="{{ $k }}" {{ $dashboard->access == $k ? 'selected' : null }}>{{ $v }}</option>
                                    @endforeach
                                    </select>
                                    <span class="input-group-btn pull-left">
                                        <button class="btn btn-primary" type="submit">Update</button>
                                    </span>
                                </div>
                            </div>
                        </form>
                    </div>
                    @if (count($user_list) and auth()->user()->isAdmin())
                    <div class="btn-group btn-lg" style="margin-top:5px;position:absolute;right:0px;">
                        <div class="btn-group">
                        <select class="form-control" id="dashboard_copy_target" name="dashboard_copy_target" onchange="dashboard_copy_user_select()">
                            <option value="-1" selected> Copy Dashboard to </option>
                        @foreach ($user_list as $user)
                            <option value="{{ $user->user_id }}">{{ $user->username }}</option>
                        @endforeach
                        </select>
                        </div>
                        <button disabled id="do_copy_dashboard" class="btn btn-primary" onclick="dashboard_copy(this)" data-toggle="tooltip" data-container="body" data-placement="top" title="Copy Dashboard"><i class="fa fa-copy fa-fw"></i></button>
                    </div>
                    @endif
                </div>
            </div>
            <!-- End Dashboard-Settings -->
            <!-- Start Widget-Select -->
            <div class="row" style="margin-top:5px;">
                <div class="col-md-12">
                    <div class="col-md-12">
                        <div class="btn-group" role="group">
                            <a class="btn btn-default disabled" role="button" style="min-width:160px;"><span class="pull-left">Add Widgets</span></a>
                            <div class="btn-group">
                                <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="min-width:160px;"><span class="pull-left">Select Widget</span>
                                <span class="pull-right">
                                    <span class="caret"></span>
                                    <span class="sr-only">Toggle Dropdown</span>
                                </span>
                                </button>
                                <ul class="dropdown-menu">
                                    @foreach ($widgets as $widget)
                                    <li>
                                        <a href="#" onsubmit="return false;" class="place_widget" data-widget_id="{{ $widget->widget_id }}">{{ $widget->widget_title }}</a>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Widget-Select -->
            <hr>
        </div>
        <div class="dash-collapse" id="del_dash" style="display: none;">
            <div class="row" style="margin-top:5px;">
                <div class="col-md-6">
                    <div class="col-md-6">
                        <button class="btn btn-danger" type="button" id="clear_widgets" name="clear_widgets" style="min-width:160px;"><span class="pull-left">Remove</span><strong class="pull-right">Widgets</strong></button>
                    </div>
                </div>
            </div>
            <div class="row" style="margin-top:5px;">
                <div class="col-md-6">
                    <div class="col-md-6">
                        <button class="btn btn-danger" type="button" onclick="dashboard_delete(this); return false;" data-dashboard="{{ $dashboard->dashboard_id }}" style="min-width:160px;"><span class="pull-left">Delete</span><strong class="pull-right">Dashboard</strong></button>
                    </div>
                </div>
            </div>
            <hr>
        </div>
    </div>
</div>
@endif
<span class="message" id="message"></span>
<div class="gridster grid">
    <ul></ul>
</div>
</div>
@endsection

@section('javascript')
<script src="{{ asset('js/jquery.gridster.min.js?ver=05072021') }}"></script>
<script src="{{ asset('js/raphael.min.js?ver=05072021') }}"></script>
<script src="{{ asset('js/justgage.min.js?ver=05072021') }}"></script>
@endsection

@push('scripts')
<script type="text/javascript">
    var gridster;

    var serialization = {!! $dash_config !!};

    serialization = Gridster.sort_by_row_and_col_asc(serialization);
    var gridster_state = 0;


    @if ($dashboard->dashboard_id > 0)
        var dashboard_id = {{ $dashboard->dashboard_id }};
    @else
        var dashboard_id = 0;
    @endif

    $('[data-toggle="tooltip"]').tooltip();
    dashboard_collapse();
    gridster = $(".gridster ul").gridster({
        widget_base_dimensions: ['auto', 100],
        autogenerate_stylesheet: true,
        widget_margins: [5, 5],
        avoid_overlapped_widgets: true,
        min_cols: 1,
        max_cols: 20,
        max_rows: 200,
        draggable: {
            handle: 'header, span',
            stop: function(e, ui, $widget) {
                updatePos(gridster);
            },
        },
        resize: {
            enabled: true,
            stop: function(e, ui, widget) {
                updatePos(gridster);
                widget_reload(widget.attr('id'), widget.data('type'));
            }
        },
        serialize_params: function(w, wgd) {
            return {
                id: $(w).attr('id'),
                col: wgd.col,
                row: wgd.row,
                size_x: wgd.size_x,
                size_y: wgd.size_y
            };
        }
    }).data('gridster');
    $('.gridster  ul').css({'width': $(window).width()});

    gridster.remove_all_widgets();
    gridster.disable();
    gridster.disable_resize();
    $.each(serialization, function() {
        widget_dom(this);
    });
    $(document).on('click','.edit-dash-btn', function() {
        if (gridster_state == 0) {
            gridster.enable();
            gridster.enable_resize();
            gridster_state = 1;
            $('.fade-edit').fadeIn();
        }
        else {
            gridster.disable();
            gridster.disable_resize();
            gridster_state = 0;
            $('.fade-edit').fadeOut();
        }
    });

    $(document).on('click','#clear_widgets', function() {
        var widget_id = $(this).data('widget-id');
        if (dashboard_id > 0) {
            $.ajax({
                type: 'POST',
                url: 'ajax_form.php',
                data: {
                    type: "update-dashboard-config",
                    sub_type: 'remove-all',
                    dashboard_id: dashboard_id
                },
                dataType: "json",
                success: function (data) {
                    if (data.status == 'ok') {
                        gridster.remove_all_widgets();
                        toastr.success(data.message);
                    }
                    else {
                        toastr.error(data.message);
                    }
                },
                error: function (data) {
                    toastr.error(data.message);
                }
            });
        }
    });

    $('.place_widget').on('click',  function(event, state) {
        var widget_id = $(this).data('widget_id');
        event.preventDefault();
        if (dashboard_id > 0) {
            $.ajax({
                type: 'POST',
                url: 'ajax_form.php',
                data: {
                    type: "update-dashboard-config",
                    sub_type: 'add',
                    widget_id: widget_id,
                    dashboard_id: dashboard_id
                },
                dataType: "json",
                success: function (data) {
                    if (data.status == 'ok') {
                        widget_dom(data.extra);
                        updatePos(gridster);
                        toastr.success(data.message);
                    }
                    else {
                        toastr.error(data.message);
                    }
                },
                error: function (data) {
                    toastr.error(data.message);
                }
            });
        }
    });

    $(document).on( "click", ".close-widget", function() {
        var widget_id = $(this).data('widget-id');
        $.ajax({
            type: 'POST',
            url: 'ajax_form.php',
            data: {
                type: "update-dashboard-config",
                sub_type: 'remove',
                widget_id: widget_id,
                dashboard_id: dashboard_id
            },
            dataType: "json",
            success: function (data) {
                if (data.status == 'ok') {
                    gridster.remove_widget($('#'+widget_id));
                    updatePos(gridster);
                    toastr.success(data.message);
                }
                else {
                    toastr.error(data.message);
                }
            },
            error: function (data) {
                toastr.error(data.message);
            }
        });
    });

    $(document).on("click",".edit-widget",function() {
        obj = $(this).parent().parent().parent();
        if( obj.data('settings') == 1 ) {
            obj.data('settings','0');
        } else {
            obj.data('settings','1');
        }
        widget_reload(obj.attr('id'),obj.data('type'));
    });




    function updatePos(gridster) {
        var s = JSON.stringify(gridster.serialize());

        @if ($dashboard->dashboard_id > 0)
            var dashboard_id = {{ $dashboard->dashboard_id }};
        @else
            var dashboard_id = 0;
        @endif

        if (dashboard_id > 0) {
            $.ajax({
                type: 'POST',
                url: 'ajax_form.php',
                data: {
                    type: "update-dashboard-config",
                    data: s,
                    dashboard_id: dashboard_id
                },
                dataType: "json",
                success: function (data) {
                    if (data.status == 'ok') {
                        toastr.success(data.message);
                    }
                    else {
                        toastr.error(data.message);
                    }
                },
                error: function (data) {
                    toastr.error(data.message);
                }
            });
        }
    }

    function dashboard_collapse(target) {
        if (target !== undefined) {
            $('.dash-collapse:not('+target+')').each(function() {
                $(this).fadeOut(0);
            });
            $(target).fadeToggle(300);
            if (target != "#edit_dash") {
                gridster.disable();
                gridster.disable_resize();
                gridster_state = 0;
                $('.fade-edit').fadeOut();
            }
        } else {
            $('.dash-collapse').fadeOut(0);
        }
    }

    function dashboard_delete(data) {
        $.ajax({
            type: 'POST',
            url: 'ajax_form.php',
            data: {
                type: 'delete-dashboard',
                dashboard_id: $(data).data('dashboard')
            },
            dataType: "json",
            success: function (data) {
                if( data.status == "ok" ) {
                    toastr.success(data.message);
                    setTimeout(function (){
                        window.location.href = "{{ url('/') }}";
                    }, 500);

                } else {
                    toastr.error(data.message);
                }
            },
            error: function (data) {
                toastr.error(data.message);
            }
        });
    }

    function dashboard_edit(data) {
        @if ($dashboard->dashboard_id > 0)
            var dashboard_id = {{ $dashboard->dashboard_id }};
        @else
            var dashboard_id = 0;
        @endif
        datas = $(data).serializeArray();
        data = [];
        for( var field in datas ) {
            data[datas[field].name] = datas[field].value;
        }
        if (dashboard_id > 0) {
            $.ajax({
                type: 'POST',
                url: 'ajax_form.php',
                data: {
                    type: 'edit-dashboard',
                    dashboard_name: data['dashboard_name'],
                    dashboard_id: dashboard_id,
                    access: data['access']
                },
                dataType: "json",
                success: function (data) {
                    if (data.status == "ok") {
                        toastr.success(data.message);
                        setTimeout(function (){
                            window.location.href = "{{ url('/?dashboard=') }}" + dashboard_id;
                        }, 500);
                    }
                    else {
                        toastr.error(data.message);
                    }
                },
                error: function(data) {
                    toastr.error(data.message);
                }
            });
        }
    }

    function dashboard_add(data) {
        datas = $(data).serializeArray();
        data = [];
        for( var field in datas ) {
            data[datas[field].name] = datas[field].value;
        }
        $.ajax({
            type: 'POST',
            url: 'ajax_form.php',
            data: {type: 'add-dashboard', dashboard_name: data['dashboard_name']},
            dataType: "json",
            success: function (data) {
                if( data.status == "ok" ) {
                    toastr.success(data.message);
                    setTimeout(function (){
                        window.location.href = "{{ url('/?dashboard=') }}" + data.dashboard_id;
                    }, 500);
                }
                else {
                    toastr.error(data.message);
                }
            },
            error: function(data) {
                toastr.error(data.message);
            }
        });
    }

@if (auth()->user()->isAdmin())
    function dashboard_copy_user_select() {
        var button_disabled = true;
        if (document.getElementById("dashboard_copy_target").value > 0) {
            button_disabled = false;
        }
        $("#do_copy_dashboard").prop('disabled', button_disabled);
    }

    function dashboard_copy(data) {
        var target_user_id = document.getElementById("dashboard_copy_target").value;
        var dashboard_id = {{ $dashboard->dashboard_id }};
        var username = $("#dashboard_copy_target option:selected").text().trim();

        if (target_user_id == -1) {
            toastr.warning('No target selected to copy Dashboard to');
        } else {
            if (! confirm("Do you really want to copy this Dashboard to User '" + username + "'?")) {
                return;
            }

            $.ajax({
                type: 'POST',
                url: '{{ url('/ajax/form/copy-dashboard') }}',
                data: {target_user_id: target_user_id, dashboard_id: dashboard_id},
                dataType: "json",
                success: function (data) {
                    if( data.status == "ok" ) {
                        toastr.success(data.message);
                    } else {
                        toastr.error(data.message);
                    }
                },
                error: function(data) {
                    toastr.error(data.message);
                }
            });
            $("#dashboard_copy_target option:eq(-1)").prop('selected', true);
            dashboard_copy_user_select();
        }
    }
@endif

    function widget_dom(data) {
        dom = '<li id="'+data.user_widget_id+'" data-type="'+data.widget+'" data-settings="0">'+
              '<header class="widget_header"><span id="widget_title_'+data.user_widget_id+'">'+data.title+
              '</span>'+
              '<span class="fade-edit pull-right">'+

                @if (
                        ($dashboard->access == 1 && Auth::id() === $dashboard->user_id) ||
                        ($dashboard->access == 0 || $dashboard->access == 2)
                    )
                        '<i class="fa fa-pencil-square-o edit-widget" data-widget-id="'+data.user_widget_id+'" aria-label="Settings" data-toggle="tooltip" data-placement="top" title="Settings">&nbsp;</i>&nbsp;'+
                @endif
              '<i class="text-danger fa fa-times close-widget" data-widget-id="'+data.user_widget_id+'" aria-label="Close" data-toggle="tooltip" data-placement="top" title="Remove">&nbsp;</i>&nbsp;'+
              '</span>'+
              '</header>'+
              '<div class="widget_body" id="widget_body_'+data.user_widget_id+'">'+data.widget+'</div>'+
              '\<script\>var timeout'+data.user_widget_id+' = grab_data('+data.user_widget_id+',\''+data.widget+'\');\<\/script\>'+
              '</li>';

        if (data.hasOwnProperty('col') && data.hasOwnProperty('row')) {
            gridster.add_widget(dom, parseInt(data.size_x), parseInt(data.size_y), parseInt(data.col), parseInt(data.row));
        } else {
            gridster.add_widget(dom, parseInt(data.size_x), parseInt(data.size_y));
        }
        if (gridster_state == 0) {
            $('.fade-edit').fadeOut(0);
        }
        $('[data-toggle="tooltip"]').tooltip();
    }

    function widget_settings(data) {
        var widget_settings = {};
        var widget_id = 0;
        var datas = $(data).serializeArray();
        for( var field in datas ) {
            var name = datas[field].name;
            if (name.substring(name.length - 2, name.length) === '[]') {
                name = name.slice(0, -2);
                if (widget_settings[name]) {
                    widget_settings[name].push(datas[field].value);
                } else {
                    widget_settings[name] = [datas[field].value];
                }
            } else {
                widget_settings[name] = datas[field].value;
            }
        }

        $('.gridster').find('div[id^=widget_body_]').each(function() {
            if(this.contains(data)) {
                widget_id = $(this).parent().attr('id');
                widget_type = $(this).parent().data('type');
                $(this).parent().data('settings','0');
            }
        });
        if( widget_id > 0 && widget_settings != {} ) {
            $.ajax({
                type: 'PUT',
                url: '{{ url('/ajax/form/widget-settings/') }}/' + widget_id,
                data: {settings: widget_settings},
                dataType: "json",
                success: function (data) {
                    if( data.status == "ok" ) {
                        widget_reload(widget_id, widget_type);
                        toastr.success(data.message);
                    }
                    else {
                        toastr.error(data.message);
                    }
                },
                error: function (data) {
                    toastr.error(data.message);
                }
            });
        }
    return false;
    }

    function widget_reload(id, data_type) {
        $("#widget_body_"+id+" .bootgrid-table").bootgrid("destroy");
        $("#widget_body_"+id+" *").off();
        var $widget_body = $("#widget_body_"+id);
        if ($widget_body.parent().data('settings') == 1 ) {
            settings = 1;
        } else {
            settings = 0;
        }
        $.ajax({
            type: 'POST',
            url: ajax_url + '/dash/' + data_type,
            data: {
                id: id,
                dimensions: {x:$widget_body.width(), y:$widget_body.height()},
                settings:settings
            },
            dataType: "json",
            success: function (data) {
                var $widget_body = $("#widget_body_"+id);
                $widget_body.empty();
                if (data.status === 'ok') {
                    $("#widget_title_"+id).html(data.title);
                    $widget_body.html(data.html).parent().data('settings', data.show_settings);
                    $widget_body.html(data.html).parent().data('refresh', data.settings.refresh);
                } else {
                    $widget_body.html('<div class="alert alert-info">' + data.message + '</div>');
                }
            },
            error: function (data) {
                var $widget_body = $("#widget_body_"+id);
                $widget_body.empty();
                if (data.responseJSON.error) {
                    $widget_body.html('<div class="alert alert-info">' + data.responseJSON.error + '</div>');
                } else {
                    $widget_body.html('<div class="alert alert-info">{{ __('Problem with backend') }}</div>');
                }
            }
        });
    }

    function grab_data(id, data_type) {
        var parent = $("#widget_body_"+id).parent();

        if( parent.data('settings') == 0 ) {
            widget_reload(id, data_type);
        }

        setTimeout(function() {
            grab_data(id, data_type);
        }, (parent.data('refresh') > 0 ? parent.data('refresh') : 60) * 1000);
    }

    $('#new-widget').popover();

    @if (empty($dashboard->dashboard_id) && $default_dash == 0)
        $('#dashboard_name').val('Default');
        dashboard_add($('#add_form'));
    @endif
</script>
@endpush
