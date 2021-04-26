@extends('device.index')

@section('tab')
    @if($data['smokeping']->hasGraphs())
        <x-panel class="with-nav-tabs">
            <x-slot name="heading">
                @if(\LibreNMS\Config::get('smokeping.url'))
                    <a href="{{ \LibreNMS\Config::get('smokeping.url') }}?target={{ $device->type }}.{{ str_replace('.','_',$device->hostname) }}" target="_blank"><span class="panel-title">@lang('Smokeping') <i class="glyphicon glyphicon-share-alt"></i></span></a>
                @else
                    <span class="panel-title">@lang('Smokeping')</span>
                @endif

                <ul class="nav nav-tabs" style="display: inline-block">
                    @foreach($data['smokeping_tabs'] as $tab)
                        <li @if($loop->first) class="active" @endif><a href="#{{ $tab }}" data-toggle="tab">@lang('smokeping.' . $tab)</a></li>
                    @endforeach
                </ul>
            </x-slot>

            <div class="tab-content">
                @foreach($data['smokeping_tabs'] as $direction)
                    <div class="tab-pane fade in @if($loop->first) active @endif" id="{{ $direction }}">
                        <div class="row">
                            <div class="col-md-12">
                                <h3>Average</h3>
                            </div>
                        </div>
                        <div class="row">
                            @foreach(\LibreNMS\Util\Html::graphRow(['type' => "device_smokeping_{$direction}_all_avg", 'device' => $device->device_id]) as $graph)
                                <div class='col-md-3'>{!! $graph !!}</div>
                            @endforeach
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <h3>Aggregate</h3>
                            </div>
                        </div>
                        <div class="row">
                            @foreach(\LibreNMS\Util\Html::graphRow(['type' => "device_smokeping_{$direction}_all", 'device' => $device->device_id, 'legend' => 'no']) as $graph)
                                <div class='col-md-3'>{!! $graph !!}</div>
                            @endforeach
                        </div>
                        @foreach($data['smokeping']->otherGraphs($direction) as $info)
                            <div class="row">
                                <div class="col-md-12">
                                    <h3>@deviceLink($info['device'])</h3>
                                </div>
                            </div>
                            <div class="row">
                                @foreach(\LibreNMS\Util\Html::graphRow($info['graph']) as $graph)
                                    <div class='col-md-3'>{!! $graph !!}</div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </x-panel>
    @endif
    <x-panel title="{{ __('Performance') }}">
        <x-slot name="heading">
            <span class="panel-title" style="line-height: 34px">@lang('Performance')</span>
                <span style="text-align: center">
                    <form method="post" role="form" id="map" class="form-inline">
                            @csrf
                            <div class="form-group">
                                <label for="dtpickerfrom">@lang('From')</label>
                                <input type="text" class="form-control" id="dtpickerfrom" name="dtpickerfrom" maxlength="16"
                                       value="{{ $data['dtpickerfrom'] }}" data-date-format="YYYY-MM-DD HH:mm">
                            </div>
                            <div class="form-group">
                                <label for="dtpickerto">@lang('To')</label>
                                <input type="text" class="form-control" id="dtpickerto" name="dtpickerto" maxlength=16
                                       value="{{ $data['dtpickerto'] }} " data-date-format="YYYY-MM-DD HH:mm">
                            </div>
                            <input type="submit" class="btn btn-default" id="submit" value="Update">
                    </form>
                </span>
        </x-slot>

        <div id="performance"></div>
    </x-panel>
@endsection

@section('javascript')
    <script src="{{ url('js/vis.min.js') }}"></script>
@endsection

@push('scripts')
    <script type="text/javascript">
        var container = document.getElementById('performance');
        var names = ['Loss', 'Min latency', 'Max latency', 'Avg latency'];
        var groups = new vis.DataSet();
        groups.add({
            id: 0,
            content: names[0],
            options: {
                drawPoints: {
                    style: 'circle'
                },
                shaded: {
                    orientation: 'bottom'
                }
            }
        });

        groups.add({
            id: 1,
            content: names[1],
            options: {
                drawPoints: {
                    style: 'circle'
                },
                shaded: {
                    orientation: 'bottom'
                }
            }
        });

        groups.add({
            id: 2,
            content: names[2],
            options: {
                drawPoints: {
                    style: 'circle'
                },
                shaded: {
                    orientation: 'bottom'
                }
            }
        });

        groups.add({
            id: 3,
            content: names[3],
            options: {
                drawPoints: {
                    style: 'circle'
                },
                shaded: {
                    orientation: 'bottom'
                }
            }
        });

        var items = @json($data['perfdata']);
        var dataset = new vis.DataSet(items);
        var options = {
            barChart: {width: 50, align: 'right'}, // align: left, center, right
            drawPoints: false,
            legend: {left: {position: "bottom-left"}},
            dataAxis: {
                icons: true,
                showMajorLabels: true,
                showMinorLabels: true,
            },
            zoomMin: 86400, //24hrs
            zoomMax: {{ $data['duration'] }},
            orientation: 'top'
        };
        var graph2d = new vis.Graph2d(container, dataset, groups, options);

        $(function () {
            $("#dtpickerfrom").datetimepicker({
                useCurrent: true,
                sideBySide: true,
                useStrict: false,
                icons: {
                    time: 'fa fa-clock-o',
                    date: 'fa fa-calendar',
                    up: 'fa fa-chevron-up',
                    down: 'fa fa-chevron-down',
                    previous: 'fa fa-chevron-left',
                    next: 'fa fa-chevron-right',
                    today: 'fa fa-calendar-check-o',
                    clear: 'fa fa-trash-o',
                    close: 'fa fa-close'
                }
            });
            $("#dtpickerto").datetimepicker({
                useCurrent: true,
                sideBySide: true,
                useStrict: false,
                icons: {
                    time: 'fa fa-clock-o',
                    date: 'fa fa-calendar',
                    up: 'fa fa-chevron-up',
                    down: 'fa fa-chevron-down',
                    previous: 'fa fa-chevron-left',
                    next: 'fa fa-chevron-right',
                    today: 'fa fa-calendar-check-o',
                    clear: 'fa fa-trash-o',
                    close: 'fa fa-close'
                }
            });
        });
    </script>
@endpush

@push('styles')
    <style>
        .panel.with-nav-tabs .panel-title {
            vertical-align: top; /* otherwise pushes tabs off bottom */
            margin: 10px;
            display: inline-block;
        }

        .panel.with-nav-tabs .panel-heading {
            padding: 5px 5px 0 5px;
        }

        .panel.with-nav-tabs .nav-tabs {
            border-bottom: none;
            margin-bottom: -5px;
        }

        .panel.with-nav-tabs .nav-tabs > li > a {
            padding-right: 10px;
        }

        .panel.with-nav-tabs .nav-justified {
            margin-bottom: -1px;
        }
        
        .bootstrap-datetimepicker-widget.dropdown-menu {
            inset: auto!important;
        }
    </style>
@endpush
