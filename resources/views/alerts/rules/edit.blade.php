@extends('layouts.librenmsv1')

@section('title', __('Edit Alert Rule'))

@section('content')
<div class="row">
    <div class="col-sm-12 col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">
                    {{ __('Edit Alert Rule') }}
                    <a target="_blank" href="https://docs.librenms.org/Alerting/" class="pull-right">
                        <i class="fa fa-book"></i> {{ __('Documentation') }}
                    </a>
                </h3>
            </div>
            <div class="panel-body">
                <ul class="nav nav-tabs" role="tablist">
                    <li role="presentation" class="active"><a href="#main" aria-controls="main" role="tab" data-toggle="tab">{{ __('Main') }}&nbsp;</a></li>
                    <li role="presentation"><a href="#advanced" aria-controls="advanced" role="tab" data-toggle="tab">{{ __('Advanced') }}&nbsp;</a></li>
                </ul>
                <form method="post" role="form" id="rules" class="form-horizontal alerts-form">
                    @csrf
                    <input type="hidden" name="type" id="type" value="alert-rules">
                    <input type="hidden" name="builder_json" id="builder_json" value="">

                    @include('alerts.rules._form', [
                        'mode' => 'edit',
                        'saveUrl' => route('alert-rule.update', $alertRule),
                        'saveMethod' => 'PUT',
                        'loadUrl' => route('alert-rule.show', $alertRule),
                    ])
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- Modals for importing rules (data provided by controller) --}}
<div class="modal fade" id="search_rule_modal" tabindex="-1" role="dialog" aria-labelledby="search_rule" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h5 class="modal-title" id="search_rule">Alert rule collection</h5>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="rule_collection" class="table table-condensed table-hover">
                        <thead>
                        <tr>
                            <th data-column-id="name" data-width="200px">Name</th>
                            <th data-column-id="rule">Rule</th>
                            <td data-column-id="action" data-formatter="action"></td>
                        </tr>
                        </thead>
                        @foreach($collectionRules as $cRule)
                            <tr>
                                <td>{{ $cRule['name'] }}</td>
                                <td>{!! $cRule['sql'] !!}</td>
                                <td>{{ $cRule['id'] }}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="search_alert_rule_modal" tabindex="-1" role="dialog" aria-labelledby="search_alert_rule_list" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h5 class="modal-title" id="search_alert_rule_list">Running Alert rules</h5>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="alert_rule_list" class="table table-condensed table-hover">
                        <thead>
                        <tr>
                            <th data-column-id="alert_name" data-width="200px">Name</th>
                            <th data-column-id="alert_rule">Rule</th>
                            <th data-column-id="alert_severity">Severity</th>
                            <td data-column-id="alert_action" data-formatter="alert_action"></td>
                        </tr>
                        </thead>
                        @foreach($dbRules as $r)
                            <tr>
                                <td>{{ $r['name'] }}</td>
                                <td><i>{{ $r['display'] }}</i></td>
                                <td>{{ $r['severity'] }}</td>
                                <td>{{ $r['id'] }}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

