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
            <button class="btn btn-default disabled" style="min-width:160px;"><span class="pull-left">{{ trans('dashboard.title') }}</span></button>
            <div class="btn-group">
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" data-display="static" aria-haspopup="true" aria-expanded="false" style="min-width:160px;">
                    <span class="pull-left">{{ $dashboard->user_id != Auth::id() ? ($dashboard->user->username ?? trans('dashboard.deleted_user')) . ':' : null}} {{ $dashboard->dashboard_name }}</span>
                <span class="pull-right">
                <span class="caret"></span>
                <span class="sr-only">{{ trans('dashboard.toggle_dropdown') }}</span>
                </span>
                </button>
                <ul class="dropdown-menu">
                    @forelse ($user_dashboards as $dash)
                        @if($dash->dashboard_id != $dashboard->dashboard_id)
                        <li>
                            <a href="{{ route('dashboard.show', $dash->dashboard_id) }}">{{ $dash->dashboard_name }}</a>
                        </li>
                        @endif
                    @empty
                        <li><a>{{ trans('dashboard.no_other') }}</a></li>
                    @endforelse

                    @isset($shared_dashboards)
                        <li role="separator" class="divider"></li>
                        <li class="dropdown-header">{{ trans('dashboard.shared_title') }}</li>
                        @foreach ($shared_dashboards as $dash)
                            @if($dash->dashboard_id != $dashboard->dashboard_id)
                            <li>
                                <a href="{{ route('dashboard.show', $dash->dashboard_id) }}">
                                {{ ($dash->user->username ?? trans('dashboard.deleted_user')) . ':' . $dash->dashboard_name . ($dash->access == 1 ? ' (' . trans('dashboard.read_only') . ')' : '') }}</a>
                            </li>
                            @endif
                        @endforeach
                    @endisset
                </ul>
            </div>
                        <button class="btn btn-default edit-dash-btn" href="#edit_dash" onclick="dashboard_collapse($(this).attr('href'))" data-toggle="tooltip" data-container="body" data-placement="top" title="{{ trans('dashboard.buttons.edit') }}"><i class="fa fa-pencil-square-o fa-fw"></i></button>
            <button class="btn btn-danger" href="#del_dash" onclick="dashboard_collapse($(this).attr('href'))" data-toggle="tooltip" data-container="body" data-placement="top" title="{{ trans('dashboard.buttons.remove') }}"><i class="fa fa-trash fa-fw"></i></button>
            <button class="btn btn-success" href="#add_dash" onclick="dashboard_collapse($(this).attr('href'))" data-toggle="tooltip" data-container="body" data-placement="top" title="{{ trans('dashboard.buttons.new') }}"><i class="fa fa-plus fa-fw"></i></button>
        </div>
        <div class="dash-collapse" id="add_dash" style="display: none;" >
            <div class="row" style="margin-top:5px;">
                <div class="col-md-6">
                    <form class="form-inline" onsubmit="dashboard_add(this); return false;" name="add_form" id="add_form">
                        @csrf
                        <div class="col-sm-3 col-sx-6">
                            <div class="input-group">
                                <span class="input-group-btn">
                                    <a class="btn btn-default disabled" type="button" style="min-width:160px;"><span class="pull-left">{{ trans('dashboard.fields.new_dashboard') }}</span></a>
                                </span>
                                <input class="form-control" type="text" placeholder="{{ trans('dashboard.fields.name') }}" name="dashboard_name" id="dashboard_name" style="min-width:160px;">
                                <span class="input-group-btn">
                                    <button class="btn btn-primary" type="submit">{{ trans('dashboard.buttons.add') }}</button>
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
                                        <a class="btn btn-default disabled" type="button" style="min-width:160px;"><span class="pull-left">{{ trans('dashboard.fields.dashboard_name') }}</span></a>
                                    </span>
                                    <input class="form-control" type="text" placeholder="{{ trans('dashboard.fields.dashboard_name') }}" name="dashboard_name" value="{{ $dashboard->dashboard_name }}" style="width:160px;">
                                    <select class="form-control" name="access" style="width:160px;">
                                    @php($accessLabels = [0 => trans('dashboard.access.private'), 1 => trans('dashboard.access.shared_read'), 2 => trans('dashboard.access.shared_admin'), 3 => trans('dashboard.access.shared')])
                                    @foreach ($accessLabels as $k => $v)
                                        <option value="{{ $k }}" {{ $dashboard->access == $k ? 'selected' : null }}>{{ $v }}</option>
                                    @endforeach
                                    </select>
                                    <span class="input-group-btn pull-left">
                                        <button class="btn btn-primary" type="submit">{{ trans('dashboard.buttons.update') }}</button>
                                    </span>
                                </div>
                            </div>
                        </form>
                    </div>
                    @can('copy', \App\Models\Dashboard::class)
                    @if (count($user_list))
                    <div class="btn-group btn-lg" style="margin-top:5px;position:absolute;right:0px;">
                        <div class="btn-group">
                        <select class="form-control" id="dashboard_copy_target" name="dashboard_copy_target" onchange="dashboard_copy_user_select()">
                            <option value="-1" selected> {{ trans('dashboard.buttons.copy_to') }} </option>
                        @foreach ($user_list as $user_id => $username)
                            <option value="{{ $user_id }}">{{ $username }}</option>
                        @endforeach
                        </select>
                        </div>
                        <button disabled id="do_copy_dashboard" class="btn btn-primary" onclick="dashboard_copy(this)" data-toggle="tooltip" data-container="body" data-placement="top" title="{{ trans('dashboard.buttons.copy') }}"><i class="fa fa-copy fa-fw"></i></button>
                    </div>
                    @endif
                    @endcan
                </div>
            </div>
            <!-- End Dashboard-Settings -->
            <!-- Start Widget-Select -->
            <div class="row" style="margin-top:5px;">
                <div class="col-md-12">
                    <div class="col-md-12">
                        <div class="btn-group" role="group">
                            <a class="btn btn-default disabled" role="button" style="min-width:160px;"><span class="pull-left">{{ trans('dashboard.widgets.add') }}</span></a>
                            <div class="btn-group">
                                <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" data-display="static" aria-haspopup="true" aria-expanded="false" style="min-width:160px;"><span class="pull-left">{{ trans('dashboard.widgets.select') }}</span>
                                <span class="pull-right">
                                    <span class="caret"></span>
                                    <span class="sr-only">{{ trans('dashboard.toggle_dropdown') }}</span>
                                </span>
                                </button>
                                <ul class="dropdown-menu">
                                    @foreach ($widgets as $type => $title)
                                    <li>
                                        <a href="#" onsubmit="return false;" class="place_widget" data-widget_type="{{ $type }}">{{ $title }}</a>
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
                        <button class="btn btn-danger" type="button" id="clear_widgets" name="clear_widgets" style="min-width:160px;"><span class="pull-left">{{ trans('dashboard.widgets.remove') }}</span><strong class="pull-right">{{ trans('dashboard.widgets.label') }}</strong></button>
                    </div>
                </div>
            </div>
            <div class="row" style="margin-top:5px;">
                <div class="col-md-6">
                    <div class="col-md-6">
                        <button class="btn btn-danger" type="button" onclick="dashboard_delete(this); return false;" data-dashboard="{{ $dashboard->dashboard_id }}" style="min-width:160px;"><span class="pull-left">{{ trans('dashboard.labels.delete') }}</span><strong class="pull-right">{{ trans('dashboard.labels.dashboard') }}</strong></button>
                    </div>
                </div>
            </div>
            <hr>
        </div>
    </div>
</div>
@endif
<span class="message" id="message"></span>
<div class="grid-stack"></div>
</div>
@endsection

@section('javascript')
<script src="{{ asset('js/raphael.min.js?ver=05072021') }}"></script>
<script src="{{ asset('js/justgage.min.js?ver=05072021') }}"></script>
@endsection

@push('scripts')
@include('map.custom-js')
<script type="text/javascript">
    var serialization = @json($dash_config);
    var gridstack_state = 0;
    var grid;

    @if ($dashboard->dashboard_id > 0)
        var dashboard_id = {{ $dashboard->dashboard_id }};
    @else
        var dashboard_id = 0;
    @endif

    // Vite loads GridStack as a deferred module; ensure it's available before running dashboard code.
    window.addEventListener('DOMContentLoaded', function () {
        $('[data-toggle="tooltip"]').tooltip();
        dashboard_collapse();
        grid = GridStack.init({
            cellHeight: 100,
            margin: 10,
            minRow: 1,
            maxRow: 200,
            column: 20,
            float: false,
            draggable: {handle: 'header, span'},
            resizable: {handles: 'all'},
            disableOneColumnMode: true,
        }, '.grid-stack');

        // load existing widgets (sorted by row/col like Gridster used to)
        serialization.sort((a, b) => (a.row - b.row) || (a.col - b.col)).forEach(widget_dom);

        // default to "view mode"
        grid.enableMove(false);
        grid.enableResize(false);

        grid.on('change', function () {
            updatePos(grid);
        });

        grid.on('resizestop', function (event, element) {
            var $el = $(element);
            updatePos(grid);
            widget_reload($el.attr('id'), $el.data('type'));
        });

        @if (empty($dashboard->dashboard_id) && $default_dash == 0)
        $('#dashboard_name').val('Default');
        dashboard_add($('#add_form'));
        @endif
    });

    $('#new-widget').popover();

    $(document).on('click','.edit-dash-btn', function() {
        if (gridstack_state == 0) {
            grid.enableMove(true);
            grid.enableResize(true);
            gridstack_state = 1;
            $('.fade-edit').fadeIn();
        }
        else {
            grid.enableMove(false);
            grid.enableResize(false);
            gridstack_state = 0;
            $('.fade-edit').fadeOut();
        }
    });

    $(document).on('click','#clear_widgets', function() {
        if (dashboard_id > 0) {
            $.ajax({
                type: 'DELETE',
                url: '{{ route('dashboard.widget.clear', '?') }}'.replace('?', dashboard_id),
                dataType: "json",
                success: function (data) {
                    if (data.status == 'ok') {
                        grid.removeAll();
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
        var widget_type = $(this).data('widget_type');
        event.preventDefault();
        if (dashboard_id > 0) {
            $.ajax({
                type: 'POST',
                url: '{{ route('dashboard.widget.add', '?') }}'.replace('?', dashboard_id),
                data: {
                    widget_type: widget_type
                },
                dataType: "json",
                success: function (data) {
                    if (data.status === 'ok') {
                        widget_dom(data.extra);
                        updatePos(grid);
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
            type: 'DELETE',
            url: '{{ route('dashboard.widget.remove', '?') }}'.replace('?', widget_id),
            dataType: "json",
            success: function (data) {
                if (data.status == 'ok') {
                    var el = document.getElementById(widget_id);
                    if (el) {
                        grid.removeWidget(el);
                    }
                    updatePos(grid);
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
        const $widget = $(this).closest('.grid-stack-item');
        if ($widget.data('settings') == 1) {
            $widget.data('settings', '0');
        } else {
            $widget.data('settings', '1');
        }
        widget_reload($widget.attr('id'), $widget.data('type'), true);
    });




    function updatePos(grid) {
        @if ($dashboard->dashboard_id > 0)
            var dashboard_id = {{ $dashboard->dashboard_id }};
        @else
            var dashboard_id = 0;
        @endif

        if (dashboard_id > 0) {
            var serialized = grid.save(false).map(function(item) {
                var id = item.id || (item.el ? item.el.getAttribute('id') : undefined);
                return {
                    id: id,
                    col: (item.x ?? 0) + 1,
                    row: (item.y ?? 0) + 1,
                    size_x: item.w ?? 1,
                    size_y: item.h ?? 1,
                };
            });
            $.ajax({
                type: 'PUT',
                url: '{{ route('dashboard.widget.update', '?') }}'.replace('?', dashboard_id),
                data: {data: JSON.stringify(serialized)},
                dataType: "json",
                success: function (data) {
                    if (data.status !== 'ok') {
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
                grid.enableMove(false);
                grid.enableResize(false);
                gridstack_state = 0;
                $('.fade-edit').fadeOut();
            }
        } else {
            $('.dash-collapse').fadeOut(0);
        }
    }

    function dashboard_delete(data) {
        $.ajax({
            type: 'DELETE',
            url: '{{ route('dashboard.destroy', '?') }}'.replace('?', $(data).data('dashboard')),
            dataType: "json",
            success: function (data) {
                if( data.status == "ok" ) {
                    toastr.success(data.message);
                    setTimeout(function (){
                        window.location.href = "{{ route('home') }}";
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
                type: 'PUT',
                url: '{{ route('dashboard.update', '?') }}'.replace('?', dashboard_id),
                data: {
                    dashboard_name: data['dashboard_name'],
                    access: data['access']
                },
                dataType: "json",
                success: function (data) {
                    if (data.status == "ok") {
                        toastr.success(data.message);
                        setTimeout(function (){
                            window.location.href = '{{ route('dashboard.show', '?') }}'.replace('?', dashboard_id);
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
            url: '{{ route('dashboard.store') }}',
            data: {dashboard_name: data['dashboard_name']},
            dataType: "json",
            success: function (data) {
                if( data.status == "ok" ) {
                    toastr.success(data.message);
                    setTimeout(function (){
                        window.location.href = '{{ route('dashboard.show', '?') }}'.replace('?', data.dashboard_id);
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
                url: '{{ route('dashboard.copy', '?') }}'.replace('?', dashboard_id),
                data: {target_user_id: target_user_id},
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

    function widget_dom(data) {
        dom = '<div id="'+data.user_widget_id+'" class="grid-stack-item" data-type="'+data.widget+'" data-settings="0" gs-id="'+data.user_widget_id+'">'+
              '<div class="grid-stack-item-content">'+
              '<header class="widget_header"><span id="widget_title_'+data.user_widget_id+'">'+data.title+
              '</span><span id="widget_title_counter_'+data.user_widget_id+'"></span>'+
              '<span class="fade-edit pull-right">'+

                @if (
                        ($dashboard->access == 1 && Auth::id() === $dashboard->user_id) ||
                        ($dashboard->access == 0 || $dashboard->access >= 2)
                    )
                        '<i class="fa fa-pencil-square-o edit-widget" data-widget-id="'+data.user_widget_id+'" aria-label="Settings" data-toggle="tooltip" data-placement="top" title="Settings">&nbsp;</i>&nbsp;'+
                @endif
              '<i class="text-danger fa fa-times close-widget" data-widget-id="'+data.user_widget_id+'" aria-label="Close" data-toggle="tooltip" data-placement="top" title="Remove">&nbsp;</i>&nbsp;'+
              '</span>'+
              '</header>'+
              '<div class="widget_body" id="widget_body_'+data.user_widget_id+'">'+data.widget+'</div>'+
              '</div></div>';

        // GridStack v11+ doesn't accept HTML strings in addWidget(), so build an element.
        // (Also, relying on injected <script> tags is brittle; we start widget refresh explicitly below.)
        var wrap = document.createElement('div');
        wrap.innerHTML = dom;
        var el = wrap.firstElementChild;

        if (data.hasOwnProperty('col') && data.hasOwnProperty('row')) {
            el.setAttribute('gs-w', '' + parseInt(data.size_x));
            el.setAttribute('gs-h', '' + parseInt(data.size_y));
            el.setAttribute('gs-x', '' + (parseInt(data.col) - 1));
            el.setAttribute('gs-y', '' + (parseInt(data.row) - 1));
        } else {
            el.setAttribute('gs-w', '' + parseInt(data.size_x));
            el.setAttribute('gs-h', '' + parseInt(data.size_y));
        }

        // GridStack v11: prefer makeWidget() over addWidget(el)
        grid.el.appendChild(el);
        grid.makeWidget(el);
        grab_data(data.user_widget_id, data.widget);
        if (gridstack_state == 0) {
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

        $('.grid-stack').find('div[id^=widget_body_]').each(function() {
            if(this.contains(data)) {
                const $widget = $(this).closest('.grid-stack-item');
                widget_id = $widget.attr('id');
                widget_type = $widget.data('type');
                $widget.data('settings', '0');
            }
        });
        if(widget_id > 0 && widget_settings != {}) {
            $.ajax({
                type: 'PUT',
                url: '{{ route('dashboard.widget.settings', '?') }}/'.replace('?', widget_id),
                data: { settings: widget_settings },
                dataType: "json",
                success: function (data) {
                    if( data.status == "ok" ) {
                        widget_reload(widget_id, widget_type, true);
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

    function widget_reload(id, data_type, forceDomInject = false) {
        const $widget_body = $('#widget_body_' + id);
        const $widget = $widget_body.children().first();

        // skip html reload and sned refresh event instead
        if (!forceDomInject && $widget.data('reload') === false) {
            $widget.trigger('refresh', $widget); // send refresh event
            return; // skip html reload
        }

        $.ajax({
            type: 'POST',
            url: ajax_url + '/dash/' + data_type,
            data: {
                id: id,
                dimensions: {x: $widget_body.width(), y: $widget_body.height()},
                settings: $widget_body.closest('.grid-stack-item').data('settings') == 1 ? 1 : 0
            },
            dataType: 'json',
            success: function (data) {
                if (data.status === 'ok') {
                    $widget.trigger('destroy', $widget); // send destroy event
                    $widget_body.children().unbind().html("").remove(); // clear old contents and unbind events

                    $('#widget_title_' + id).html(data.title);
                    $widget_body.html(data.html);
                    $widget_body.closest('.grid-stack-item').data('settings', data.show_settings).data('refresh', data.settings.refresh);
                } else {
                    $widget_body.html('<div class="alert alert-info">' + data.message + '</div>');
                }
            },
            error: function (data) {
                $widget_body.html('<div class="alert alert-info">' + (data.responseJSON.error || '{{ __('Problem with backend') }}') + '</div>');
            }
        });
    }

    function grab_data(id, data_type) {
        const refresh = $('#widget_body_' + id).closest('.grid-stack-item').data('refresh');
        widget_reload(id, data_type);

        setTimeout(function () {
            grab_data(id, data_type);
        }, (refresh > 0 ? refresh : 60) * 1000);
    }

    // make sure edit mode stays disabled when the window is resized
    var resizeTrigger = null;
    addEvent(window, "resize", function(event) {
        // emit resize event, but only once every 100ms
        if (resizeTrigger === null) {
            resizeTrigger = setTimeout(() => {
                resizeTrigger = null;
                $('.widget_body').children().first().trigger('resize');
            }, 100);
        }

        setTimeout(function(){
            if(!gridstack_state) {
                grid.enableMove(false);
                grid.enableResize(false);
            }
        }, 100);
    });
</script>
@endpush
